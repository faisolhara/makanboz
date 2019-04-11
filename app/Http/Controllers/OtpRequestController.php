<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class OtpRequestController extends Controller
{

    public function index(Request $request)
    {
        \DB::beginTransaction();

        $validation = Validator::make($request->all(),[ 
            'p_phone_number' => 'required|min:6',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }
        try {

            $p_phone_number       = $request->get('p_phone_number');

            $pdo = \DB::connection()->getPdo();
             
            // calling stored procedure command
            $sql = 'CALL otp_request_pc(:p_phone_number)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_phone_number', $p_phone_number, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();

            $stmt->closeCursor();

            // execute the second query to get output
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

        return response()->json([
            'o_status'  => 1,
            'o_message' => null,
        ], 200);

    }
}
