<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class MyLastSeenController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'       => 'required',
            'p_keyword'       => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_user_id     = $request->get('p_user_id');
            $p_keyword     = $request->get('p_keyword');
            $p_offset      = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
            $p_limit       = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

            $query = "select  user_id, last_seen_date, product_id, original_file, product_name, hashtag,
                                seller_id, shop_name, min_price, max_price, stock_type, favorite_count, 
                                review_avg, sold_count, available_type_name, delivery_time
                        from    my_last_seen_v
                        where   user_id = ".$p_user_id."
                                and product_name like '%".$p_keyword."%'
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
