<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class BozpayWithdrawRequestController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_access_token'    => 'required',
            'p_user_id'         => 'required',
            'p_amount'          => 'required',
            'p_bank_account_id' => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_access_token           = $request->get('p_access_token');
            $p_user_id                = intval($request->get('p_user_id'));
            $p_amount                 = $request->get('p_amount');
            $p_bank_account_id        = intval($request->get('p_bank_account_id'));

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL bozpay_withdraw_request_pc(@o_status, @o_message, @o_withdraw_id, @o_withdraw_number, :p_access_token, :p_user_id, :p_amount, :p_bank_account_id)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_amount', $p_amount, PDO::PARAM_STR);
            $stmt->bindParam(':p_bank_account_id', $p_bank_account_id, PDO::PARAM_INT);

            // execute the stored procedure
            $stmt->execute();

            $stmt->closeCursor();

            // execute the second query to get output
            $row = $pdo->query("SELECT @o_status as o_status, @o_message as o_message, @o_withdraw_id as o_withdraw_id, @o_withdraw_number as o_withdraw_number")->fetch(PDO::FETCH_ASSOC);   
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
