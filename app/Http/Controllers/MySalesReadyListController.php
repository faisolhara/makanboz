<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class MySalesReadyListController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'         => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {

            $p_user_id      = $request->get('p_user_id');

            $query = "select  seller_id, transaction_id, delivery_date, day_name, 
                                product_id, thumbnail_file, product_name, plu_code, plu_name, delivery_time, 
                                buyer_id, profile_file, user_name, 
                                deliver_to_address_id, receiver_name, phone_number, 
                                address_detail,distance_km, buyer_note, quantity
                        from    my_sales_ready_v
                        where   seller_id = ".$p_user_id."
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
