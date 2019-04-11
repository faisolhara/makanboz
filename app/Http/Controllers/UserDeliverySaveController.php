<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class UserDeliverySaveController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_access_token'           => 'required',
            'p_user_id'              => 'required',
            'p_delivery_id'              => 'required',
            'p_cost_type'              => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_access_token           = $request->get('p_access_token');
            $p_user_id                = intval($request->get('p_user_id'));
            $p_delivery_id            = intval($request->get('p_delivery_id'));
            $p_cost_type              = $request->get('p_cost_type');
            $p_delivery_id            = intval($request->get('p_delivery_id'));
            $p_max_km                 = intval($request->get('p_max_km'));
            $p_cost_amount            = floatval($request->get('p_cost_amount'));
            $p_is_active              = $request->get('p_is_active');

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL user_delivery_save_pc(@o_status, @o_message, :p_access_token, :p_user_id, :p_delivery_id, :p_cost_type, :p_max_km, :p_cost_amount, :p_is_active)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_delivery_id', $p_delivery_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_cost_type', $p_cost_type, PDO::PARAM_STR);
            $stmt->bindParam(':p_max_km', $p_max_km, PDO::PARAM_INT);
            $stmt->bindParam(':p_cost_amount', $p_cost_amount, PDO::PARAM_STR);
            $stmt->bindParam(':p_is_active', $p_is_active, PDO::PARAM_STR);

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
