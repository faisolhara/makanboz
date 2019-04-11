<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class BuyerTransactionLineController extends Controller
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
            $p_transaction_id = $request->get('p_transaction_id');

            $query = "select  transaction_id, transaction_line_id, seller_id, profile_file, user_name,
                            product_id, thumbnail_file, product_name,
                            plu_code, plu_name, quantity, unit_price, delivery_date, delivery_time, buyer_note
                    from    buyer_transaction_line_v
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
