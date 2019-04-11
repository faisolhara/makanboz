<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;


class ChangePhoneNumberController extends Controller
{

    public function index(Request $request)
    {
        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_phone_number_old'    => 'required',
            'p_phone_number_new'    => 'required',
            'p_otp_code_new'        => 'required|min:6|max:6',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_phone_number_old  = $request->get('p_phone_number_old');
            $p_phone_number_new  = $request->get('p_phone_number_new');
            $p_otp_code_new      = $request->get('p_otp_code_new');

            $pdo = \DB::connection()->getPdo();
             
            // calling stored procedure command
            $sql = 'CALL change_phone_number_pc(@o_status, @o_message, :p_phone_number_old, :p_phone_number_new, :p_otp_code_new)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_phone_number_old', $p_phone_number_old, PDO::PARAM_STR);
            $stmt->bindParam(':p_phone_number_new', $p_phone_number_new, PDO::PARAM_STR);
            $stmt->bindParam(':p_otp_code_new', $p_otp_code_new, PDO::PARAM_STR);

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
