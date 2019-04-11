<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;


class UserLoginController extends Controller
{

    public function index(Request $request)
    {
        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_field'      => 'required',
            'p_user_password'   => 'required',
            'p_ipv4'            => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_user_field       = $request->get('p_user_field');
            $p_user_password    = $request->get('p_user_password');
            $p_ipv4             = $request->get('p_ipv4');

            $pdo = \DB::connection()->getPdo();
             
            // calling stored procedure command
            $sql = 'CALL user_login_pc(@o_status, @o_message, @o_access_token, @o_user_id, @o_first_flag, :p_user_field, :p_user_password, :p_ipv4)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_user_field', $p_user_field, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_password', $p_user_password, PDO::PARAM_STR);
            $stmt->bindParam(':p_ipv4', $p_ipv4, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();

            $stmt->closeCursor();

            // execute the second query to get output
            $row = $pdo->query("SELECT @o_status as o_status, @o_message as o_message, @o_access_token as o_access_token, @o_user_id as o_user_id, @o_first_flag as o_first_flag")->fetch(PDO::FETCH_ASSOC);
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
