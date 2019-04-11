<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class ProductRecomendationController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_city_id'       => 'required',
        ]);
        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_city_id      = $request->get('p_city_id');
            $p_offset      = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
            $p_limit       = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

            $query = "select   product_id, original_file, product_name, hashtag,
                                seller_id, shop_name, min_price, max_price, stock_type, favorite_count,
                                review_avg, sold_count, available_type_name, delivery_time, city_id
                        from    product_recommendation_v
                        where   city_id = '".$p_city_id."' 
                        limit ".$p_offset." , ".$p_limit;

            $result = \DB::select(\DB::raw($query));
            foreach ($result as $model) {
                $model->original_file = 'images/product/original/'.$model->original_file;
            }

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
