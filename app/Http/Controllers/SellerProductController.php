<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class SellerProductController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_seller_id'         => 'required',
            'p_product_id'         => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_seller_id    = $request->get('p_seller_id');
            $p_product_id   = $request->get('p_product_id');
            $p_offset       = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
            $p_limit        = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

            $query = "select  seller_id, shop_name, product_id, original_file, product_name, min_price, max_price, stock_type,
                                favorite_count, review_avg, sold_count, available_type_name, delivery_time
                        from    seller_other_product_v
                        where   seller_id = ".$p_seller_id."
                                and product_id <> ".$p_product_id."
                        order by product_id desc
                        limit ".$p_offset.", ".$p_limit;

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
