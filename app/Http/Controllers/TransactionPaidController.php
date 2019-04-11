<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class TransactionPaidController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_transaction_id'          => 'required',
            'p_payment_method_id'       => 'required',
            'p_transfer_reference'      => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_transaction_id          = intval($request->get('p_transaction_id'));
            $p_payment_method_id       = intval($request->get('p_payment_method_id'));
            $p_transfer_reference      = $request->get('p_transfer_reference');

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL change_pin_pc(@o_status, @o_message, :p_transaction_id, :p_payment_method_id, :p_transfer_reference';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_transaction_id', $p_transaction_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_payment_method_id', $p_payment_method_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_transfer_reference', $p_transfer_reference, PDO::PARAM_STR);

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
