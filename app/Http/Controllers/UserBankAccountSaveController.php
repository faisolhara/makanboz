<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class UserBankAccountSaveController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_bank_account_id'     => 'required',
            'p_access_token'        => 'required',
            'p_user_id'             => 'required',
            'p_bank_id'             => 'required',
            'p_bank_account_number' => 'required',
            'p_bank_account_name'   => 'required',
            'p_branch_name'         => 'required',
            'p_city_name'           => 'required',
            'p_set_default'         => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_bank_account_id     = intval($request->get('p_bank_account_id'));
            $p_access_token        = $request->get('p_access_token');
            $p_user_id             = intval($request->get('p_user_id'));
            $p_bank_id             = intval($request->get('p_bank_id'));
            $p_bank_account_number = $request->get('p_bank_account_number');
            $p_bank_account_name   = $request->get('p_bank_account_name');
            $p_branch_name         = $request->get('p_branch_name');
            $p_city_name           = $request->get('p_city_name');
            $p_set_default         = $request->get('p_set_default');

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL user_bank_account_save_pc(@o_status, @o_message, @o_bank_account_id, :p_bank_account_id, :p_access_token, :p_user_id, :p_bank_id, :p_bank_account_number, :p_bank_account_name, :p_branch_name, :p_city_name, :p_set_default)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_bank_account_id', $p_bank_account_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_bank_id', $p_bank_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_bank_account_number', $p_bank_account_number, PDO::PARAM_STR);
            $stmt->bindParam(':p_bank_account_name', $p_bank_account_name, PDO::PARAM_STR);
            $stmt->bindParam(':p_branch_name', $p_branch_name, PDO::PARAM_STR);
            $stmt->bindParam(':p_city_name', $p_city_name, PDO::PARAM_STR);
            $stmt->bindParam(':p_set_default', $p_set_default, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();

            // return $p_product_name;

            $stmt->closeCursor();

            // execute the second query to get output

            $row = $pdo->query("SELECT @o_status as o_status, @o_message as o_message, @o_bank_account_id as o_bank_account_id")->fetch(PDO::FETCH_ASSOC);   
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
