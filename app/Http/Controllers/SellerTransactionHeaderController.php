<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class SellerTransactionHeaderController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_transaction_id'       => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_transaction_id    = $request->get('p_transaction_id');

            $query = "select  transaction_id, transaction_number, transaction_date, receiver_name, receiver_phone_number,
                            deliver_to_address, subtotal_product, subtotal_delivery, 
                            is_paid, payment_method, payment_date,
                            buyer_id, buyer_user_name, buyer_profile_file
                    from    seller_transaction_header_v
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
