<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class TransactionStatusController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'              => 'required',
            'p_transaction_id'       => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_user_id        = $request->get('p_user_id');
            $p_transaction_id = $request->get('p_transaction_id');

            $query = "select  transaction_id, transaction_date, transaction_status,
                                if(buyer_id = ".$p_user_id.", buyer_status_note, seller_status_note) as status_note
                        from    transaction_status_v
                        where   transaction_id = ".$p_transaction_id;

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
