<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class WithdrawDetailController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_withdraw_id'       => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_withdraw_id = $request->get('p_withdraw_id');

            $query = "select  withdraw_id, amount, transfer_status, 
                                bank_account_id, bank_id, bank_account_name, bank_account_number, account_number_label,
                                withdraw_number, request_date, transfer_date
                        from    withdraw_detail_v
                        where   withdraw_id = ".$p_withdraw_id;

            $result = \DB::select(\DB::raw($query));

            return response()->json($result, 200);

        } catch (Exception $e) {
            \DB::rollback();
            return response()->json([
                'o_status'  => -1,
                'o_message' => $e->getMessage(),
            ], 200);
        }
    }
}
