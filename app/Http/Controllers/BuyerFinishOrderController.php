<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;


class BuyerFinishOrderController extends Controller
{

    public function index(Request $request)
    {
        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_access_token'         => 'required',
            'p_transaction_line_id'  => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_access_token         = $request->get('p_access_token');
            $p_transaction_line_id  = intval($request->get('p_transaction_line_id'));

            $pdo = \DB::connection()->getPdo();
             
            // calling stored procedure command
            $sql = 'CALL buyer_finish_pc(@o_status, @o_message, :p_access_token, :p_transaction_line_id)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_transaction_line_id', $p_transaction_line_id, PDO::PARAM_INT);

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
