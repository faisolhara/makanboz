<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class MyPurchaseReadyListController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'       => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_user_id = $request->get('p_user_id');

            $query = "select  buyer_id, transaction_id, transaction_line_id, delivery_date, day_name, 
                                seller_id, shop_name, product_id, thumbnail_file, product_name, 
                                plu_code, plu_name, delivery_time, buyer_note, quantity
                        from    my_purchase_ready_v
                        where   buyer_id = ".$p_user_id."
                        order by delivery_date, product_id";

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
