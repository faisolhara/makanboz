<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;


class NewRegisterController extends Controller
{

    public function index(Request $request)
    {
        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_phone_number'    => 'required',
            'p_otp_code'        => 'required',
            'p_user_name'       => 'required',
            'p_user_password'   => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_phone_number     = $request->get('p_phone_number');
            $p_otp_code         = $request->get('p_otp_code');
            $p_user_name        = $request->get('p_user_name');
            $p_user_password    = $request->get('p_user_password');

            $pdo = \DB::connection()->getPdo();
             
            // calling stored procedure command
            $sql = 'CALL new_register_pc(@o_status, @o_message, @o_user_id, @o_access_token, :p_phone_number, :p_otp_code, :p_user_name, :p_user_password)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_phone_number', $p_phone_number, PDO::PARAM_STR);
            $stmt->bindParam(':p_otp_code', $p_otp_code, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_name', $p_user_name, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_password', $p_user_password, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();

            $stmt->closeCursor();

            // execute the second query to get output
            $row = $pdo->query("SELECT @o_status as o_status, @o_message as o_message, @o_user_id as o_user_id, @o_access_token as o_access_token")->fetch(PDO::FETCH_ASSOC);
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
