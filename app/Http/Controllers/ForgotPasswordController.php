<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;


class ForgotPasswordController extends Controller
{

    public function index(Request $request)
    {
        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_field'    => 'required',
            'p_otp_code'        => 'required|min:6|max:6',
            'p_user_password'    => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_user_field       = $request->get('p_user_field');
            $p_otp_code         = $request->get('p_otp_code');
            $p_user_password    = $request->get('p_user_password');

            $pdo = \DB::connection()->getPdo();
             
            // calling stored procedure command
            $sql = 'CALL forgot_password_pc(@o_status, @o_message, :p_user_field, :p_otp_code, :p_user_password)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_user_field', $p_user_field, PDO::PARAM_STR);
            $stmt->bindParam(':p_otp_code', $p_otp_code, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_password', $p_user_password, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();

            $stmt->closeCursor();

            // execute the second query to get output
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
