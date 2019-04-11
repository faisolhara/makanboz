<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class MySalesConfirmationListController extends Controller
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

            $query = "select  buyer_id, profile_file, user_name,
                                transaction_id, transaction_number, transaction_date,
                                total_product,
                                thumbnail_file, product_id, product_name, plu_code, plu_name,
                                delivery_time, buyer_note, quantity, unit_price, subtotal_product
                        from    my_sales_confirmation_v
                        where   seller_id = ".$p_user_id."
                        order by transaction_id";

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
