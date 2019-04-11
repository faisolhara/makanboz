<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;


class ChangePasswordController extends Controller
{

    public function index(Request $request)
    {
        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_access_token'    => 'required',
            'p_user_id'         => 'required',
            'p_old_password'    => 'required',
            'p_new_password'    => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_access_token = $request->get('p_access_token');
            $p_user_id      = intval($request->get('p_user_id'));
            $p_old_password = $request->get('p_old_password');
            $p_new_password = $request->get('p_new_password');

            $pdo = \DB::connection()->getPdo();
             
            // calling stored procedure command
            $sql = 'CALL change_password_pc(@o_status, @o_message, :p_access_token, :p_user_id, :p_old_password, :p_new_password)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_old_password', $p_old_password, PDO::PARAM_STR);
            $stmt->bindParam(':p_new_password', $p_new_password, PDO::PARAM_STR);

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
