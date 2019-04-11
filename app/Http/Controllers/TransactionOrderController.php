<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;
use Intervention\Image\Facades\Image;


class TransactionOrderController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

        // $validation = Validator::make($request->all(),[ 
        //     'p_access_token'           => 'required',
        //     'p_user_id'                => 'required',
        //     'p_delivery_to_address_id' => 'required',
        //     ]);

        // if($validation->fails()){
        //     $errors = $validation->errors();
        //     return $errors->toJson();
        // }

        try {
            $transactionHeaderSave = $this->transactionHeaderSave($request);
            if($transactionHeaderSave['o_status'] == -1){
                \DB::rollback();
                return response()->json([
                    'o_status'  => -1,
                    'o_message' => $transactionHeaderSave,
                    ], 200);
            }

            $transactionLineSaves   = $this->transactionLineSave($request, $transactionHeaderSave);
            foreach($transactionLineSaves as $transactionLineSave){
                if($transactionLineSave['o_status'] == -1){
                    \DB::rollback();
                    return response()->json([
                        'o_status'  => -1,
                        'o_message' => $transactionLineSave,
                        ], 200);
                }
            }

            $transactionDeliverySaves   = $this->transactionDeliverySave($request, $transactionHeaderSave);
            foreach($transactionDeliverySaves as $transactionDeliverySave){
                if($transactionDeliverySave['o_status'] == -1){
                    \DB::rollback();
                    return response()->json([
                        'o_status'  => -1,
                        'o_message' => $transactionDeliverySave,
                        ], 200);
                }
            }

            $unpaidNotificationSave   = $this->unpaidNotificationSave($transactionHeaderSave);

            \DB::commit();
            return response()->json([
                    'o_transaction_header'     => $transactionHeaderSave,
                    'o_transaction_line'       => $transactionLineSave,
                    'o_transaction_delivery'   => $transactionDeliverySave,
                ],200);

        } catch (Exception $e) {
            \DB::rollback();
            return response()->json([
                'o_status'  => -1,
                'o_message' => $e->getMessage(),
                ], 200);
        }
    }

    protected function transactionHeaderSave(Request $request){
        $header = json_decode($request->get('p_header'));
        $p_access_token           = $header->p_access_token;
        $p_user_id                = intval($header->p_user_id);
        $p_delivery_to_address_id = intval($header->p_delivery_to_address_id);
        $p_total_product          = intval($header->p_total_product);
        $p_total_quantity         = intval($header->p_total_quantity);
        $p_subtotal_product       = floatval($header->p_subtotal_product);
        $p_subtotal_delivery      = floatval($header->p_subtotal_delivery);
        $p_voucher_id             = intval($header->p_voucher_id);
        $p_discount_delivery      = floatval($header->p_discount_delivery);
        $p_redeem_point           = floatval($header->p_redeem_point);
        $p_unique_code            = intval($header->p_unique_code);
        $p_payment_method_id      = intval($header->p_payment_method_id);
        $p_cashback_point         = floatval($header->p_cashback_point);

        $pdo = \DB::connection()->getPdo();
        // calling stored procedure command
        $sql = 'CALL transaction_header_save_pc(@o_status, @o_message, @o_transaction_id, @o_transaction_number, :p_access_token, :p_user_id, :p_delivery_to_address_id, :p_total_product, :p_total_quantity, :p_subtotal_product, :p_subtotal_delivery, :p_voucher_id, :p_discount_delivery, :p_redeem_point, :p_unique_code, :p_payment_method_id, :p_cashback_point)';

        // prepare for execution of the stored procedure
        $stmt = $pdo->prepare($sql);

        // pass value to the command
        $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
        $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
        $stmt->bindParam(':p_delivery_to_address_id', $p_delivery_to_address_id, PDO::PARAM_INT);
        $stmt->bindParam(':p_total_product', $p_total_product, PDO::PARAM_INT);
        $stmt->bindParam(':p_total_quantity', $p_total_quantity, PDO::PARAM_INT);
        $stmt->bindParam(':p_subtotal_product', $p_subtotal_product, PDO::PARAM_STR);
        $stmt->bindParam(':p_subtotal_delivery', $p_subtotal_delivery, PDO::PARAM_STR);
        $stmt->bindParam(':p_discount_delivery', $p_discount_delivery, PDO::PARAM_STR);
        $stmt->bindParam(':p_voucher_id', $p_voucher_id, PDO::PARAM_INT);
        $stmt->bindParam(':p_discount_delivery', $p_discount_delivery, PDO::PARAM_STR);
        $stmt->bindParam(':p_redeem_point', $p_redeem_point, PDO::PARAM_STR);
        $stmt->bindParam(':p_unique_code', $p_unique_code, PDO::PARAM_INT);
        $stmt->bindParam(':p_payment_method_id', $p_payment_method_id, PDO::PARAM_INT);
        $stmt->bindParam(':p_cashback_point', $p_cashback_point, PDO::PARAM_STR);

        // execute the stored procedure
        $stmt->execute();
        $stmt->closeCursor();

        // execute the second query to get output
        return $pdo->query("SELECT @o_status as o_status, @o_message as o_message, @o_transaction_id as o_transaction_id, @o_transaction_number as o_transaction_number")->fetch(PDO::FETCH_ASSOC);   
    }

    protected function transactionLineSave(Request $request, $outputHeader){
        $header = json_decode($request->get('p_header'));
        $lines  = json_decode($request->get('p_lines'));
        $output = [];
        foreach ($lines as $line) {
            $p_access_token           = $header->p_access_token;
            $p_transaction_id         = $outputHeader['o_transaction_id'];
            $p_seq_no                 = $line->p_seq_no;
            $p_seller_id              = $line->p_seller_id;
            $p_product_id             = $line->p_product_id;
            $p_plu_code               = $line->p_plu_code;
            $p_plu_name               = $line->p_plu_name;
            $p_quantity               = $line->p_quantity;
            $p_unit_price             = $line->p_unit_price;
            $p_subtotal               = $line->p_subtotal;
            $p_delivery_date          = !empty($line->p_delivery_date) ? new \DateTime($line->p_delivery_date) : '';
            $p_delivery_date          = !empty($p_delivery_date) ? $p_delivery_date->format('Y-m-d') : '';
            $p_delivery_start_time    = !empty($line->p_delivery_start_time) ? new \DateTime($line->p_delivery_start_time) : '';
            $p_delivery_start_time    = !empty($p_delivery_start_time) ? $p_delivery_start_time->format('Y-m-d H:i') : '';
            $p_delivery_finish_time   = !empty($line->p_delivery_finish_time) ? new \DateTime($line->p_delivery_finish_time) : '';
            $p_delivery_finish_time   = !empty($p_delivery_finish_time) ? $p_delivery_finish_time->format('Y-m-d H:i') : '';
            $p_buyer_note             = $line->p_buyer_note;

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL transaction_line_save_pc(@o_status, @o_message, :p_access_token, :p_transaction_id, :p_seq_no, :p_seller_id, :p_product_id, :p_plu_code, :p_plu_name, :p_quantity, :p_unit_price, :p_subtotal, :p_delivery_date, :p_delivery_start_time, :p_delivery_finish_time, :p_buyer_note)'; 

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_transaction_id', $p_transaction_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_seq_no', $p_seq_no, PDO::PARAM_INT);
            $stmt->bindParam(':p_seller_id', $p_seller_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_product_id', $p_product_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_plu_code', $p_plu_code, PDO::PARAM_STR);
            $stmt->bindParam(':p_plu_name', $p_plu_name, PDO::PARAM_STR);
            $stmt->bindParam(':p_quantity', $p_quantity, PDO::PARAM_INT);
            $stmt->bindParam(':p_unit_price', $p_unit_price, PDO::PARAM_STR);
            $stmt->bindParam(':p_subtotal', $p_subtotal, PDO::PARAM_STR);
            $stmt->bindParam(':p_delivery_date', $p_delivery_date, PDO::PARAM_STR);
            $stmt->bindParam(':p_delivery_start_time', $p_delivery_start_time, PDO::PARAM_STR);
            $stmt->bindParam(':p_delivery_finish_time', $p_delivery_finish_time, PDO::PARAM_STR);
            $stmt->bindParam(':p_buyer_note', $p_buyer_note, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();
            $stmt->closeCursor();

            $output [] = $pdo->query("SELECT @o_status as o_status, @o_message as o_message")->fetch(PDO::FETCH_ASSOC);;
        }

        return $output; 
    }

    protected function transactionDeliverySave(Request $request, $outputHeader){
        $header    = json_decode($request->get('p_header'));
        $deliverys = json_decode($request->get('p_deliveries'));
        $output    = [];

        // dd($delivery);
        foreach ($deliverys as $delivery) {
            $p_access_token           = $header->p_access_token;
            $p_transaction_id         = $outputHeader['o_transaction_id'];
            $p_seller_id              = intval($delivery->p_seller_id);
            $p_delivery_date          = !empty($delivery->p_delivery_date) ? new \DateTime($delivery->p_delivery_date) : '';
            $p_delivery_date          = !empty($p_delivery_date) ? $p_delivery_date->format('Y-m-d') : '';
            $p_delivery_start_time    = !empty($delivery->p_delivery_start_time) ? new \DateTime($delivery->p_delivery_start_time) : '';
            $p_delivery_start_time    = !empty($p_delivery_start_time) ? $p_delivery_start_time->format('Y-m-d H:i') : '';
            $p_delivery_finish_time   = !empty($delivery->p_delivery_finish_time) ? new \DateTime($delivery->p_delivery_finish_time) : '';
            $p_delivery_finish_time   = !empty($p_delivery_finish_time) ? $p_delivery_finish_time->format('Y-m-d H:i') : '';
            $p_quantity               = intval($delivery->p_quantity);
            $p_delivery_id            = intval($delivery->p_delivery_id);
            $p_distance_km            = floatval($delivery->p_distance_km);
            $p_cost_per_km            = floatval($delivery->p_cost_per_km);
            $p_total_cost             = floatval($delivery->p_total_cost);

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL transaction_delivery_save_pc(@o_status, @o_message, @o_delivery_number, :p_access_token, :p_transaction_id, :p_seller_id, :p_delivery_date, :p_delivery_start_time, :p_delivery_finish_time, :p_quantity, :p_delivery_id, :p_distance_km, :p_cost_per_km, :p_total_cost)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_transaction_id', $p_transaction_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_seller_id', $p_seller_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_delivery_date', $p_delivery_date, PDO::PARAM_STR);
            $stmt->bindParam(':p_delivery_start_time', $p_delivery_start_time, PDO::PARAM_STR);
            $stmt->bindParam(':p_delivery_finish_time', $p_delivery_finish_time, PDO::PARAM_STR);
            $stmt->bindParam(':p_quantity', $p_quantity, PDO::PARAM_INT);
            $stmt->bindParam(':p_delivery_id', $p_delivery_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_distance_km', $p_distance_km, PDO::PARAM_STR);
            $stmt->bindParam(':p_cost_per_km', $p_cost_per_km, PDO::PARAM_STR);
            $stmt->bindParam(':p_total_cost', $p_total_cost, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();
            $stmt->closeCursor();

            // execute the second query to get output
            $output [] = $pdo->query("SELECT @o_status as o_status, @o_message as o_message, @o_delivery_number as o_delivery_number")->fetch(PDO::FETCH_ASSOC);
        }

        return $output;
    }

    protected function unpaidNotificationSave($outputHeader){
        $p_transaction_id  = $outputHeader['o_transaction_id'];
        $pdo = \DB::connection()->getPdo();

        // calling stored procedure command
        $sql = 'CALL unpaid_notification_pc(:p_transaction_id)';

        // prepare for execution of the stored procedure
        $stmt = $pdo->prepare($sql);

        // pass value to the command
        $stmt->bindParam(':p_transaction_id', $p_transaction_id, PDO::PARAM_INT);

        // execute the stored procedure
        $stmt->execute();
        $stmt->closeCursor();

        return response()->json([
            'o_status'  => 1,
            'o_message' => '',
        ], 200);
    }
}
