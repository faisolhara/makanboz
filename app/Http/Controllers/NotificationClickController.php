<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class NotificationClickController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'    => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_user_id           = $request->get('p_user_id');

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL user_notification_click_pc(:p_user_id)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);

            // execute the stored procedure
            $stmt->execute();

            // return $p_product_name;

            $stmt->closeCursor();

            // execute the second query to get output

            // $row = $pdo->query("set @p_product_name = :p_product_name")->fetch(PDO::FETCH_ASSOC);
            // var_dump($row);exit();   
            \DB::commit();

            return response()->json([
                'o_status'  => 1,
                'o_message' => 'Success',
            ], 200);

        } catch (Exception $e) {
            \DB::rollback();
            return response()->json([
                'o_status'  => -1,
                'o_message' => $e->getMessage(),
            ], 200);
        }
    }
}
