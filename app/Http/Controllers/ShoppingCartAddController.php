<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;
use Intervention\Image\Facades\Image;


class ShoppingCartAddController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_access_token'  => 'required',
            'p_user_id'       => 'required',
            'p_product_id'    => 'required',
            'p_delivery_date' => 'required',
            'p_quantity'      => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_access_token  = $request->get('p_access_token');
            $p_user_id       = intval($request->get('p_user_id'));
            $p_product_id    = intval($request->get('p_product_id'));
            $p_plu_code      = $request->get('p_plu_code');
            $p_plu_name      = $request->get('p_plu_name');
            $p_delivery_date = !empty($request->get('p_delivery_date')) ? new \DateTime($request->get('p_delivery_date')) : '';
            $p_delivery_date = !empty($p_delivery_date) ? $p_delivery_date->format('Y-m-d') : '';
            $p_quantity      = intval($request->get('p_quantity'));

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL shopping_cart_add_pc(@o_status, @o_message, :p_access_token, :p_user_id, :p_product_id, :p_plu_code, :p_plu_name, :p_delivery_date, :p_quantity)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_product_id', $p_product_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_plu_code', $p_plu_code, PDO::PARAM_STR);
            $stmt->bindParam(':p_plu_name', $p_plu_name, PDO::PARAM_STR);
            $stmt->bindParam(':p_delivery_date', $p_delivery_date, PDO::PARAM_STR);
            $stmt->bindParam(':p_quantity', $p_quantity, PDO::PARAM_INT);

            // execute the stored procedure
            $stmt->execute();

            // return $p_product_name;

            $stmt->closeCursor();

            // execute the second query to get output

            // $row = $pdo->query("set @p_product_name = :p_product_name")->fetch(PDO::FETCH_ASSOC);
            // var_dump($row);exit();   
            $row = $pdo->query("SELECT @o_status as o_status, @o_message as o_message")->fetch(PDO::FETCH_ASSOC);   
            \DB::commit();

            return response()->json($row, 200);

        } catch (Exception $e) {
            \DB::rollback();
            return response()->json([
                'o_status'  => -1,
                'o_message' => $e->getMessage(),
            ], 200);
        }
    }
}
