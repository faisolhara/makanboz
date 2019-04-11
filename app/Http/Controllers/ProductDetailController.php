<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class ProductDetailController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_product_id'     => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_product_id    = $request->get('p_product_id');
            $p_to_address_id = !empty($request->get('p_to_address_id')) ? $request->get('p_to_address_id') : 'null';
            $p_latitude      = !empty($request->get('p_latitude')) ? $request->get('p_latitude') : 'null';
            $p_longitude     = !empty($request->get('p_longitude')) ? $request->get('p_longitude') : 'null';

            $query = "select  product_id, product_name, seller_id, shop_name, 
                                shop_address_id, address, 
                                get_distance_km_fc(shop_address_id, null, null, ".$p_to_address_id.", ".$p_latitude.", ".$p_longitude.") as distance_km,
                                min_price, max_price,
                                delivery_start_time, delivery_finish_time, free_delivery_km, 
                                favorite_count, review_avg, sold_count,
                                profile_file, user_name, last_seen, seller_type,  product_count,
                                category_id, category_name,
                                available_type, available_sun, available_mon, 
                                available_tue, available_wed, available_thu,
                                available_fri, available_sat, available_start_date, 
                                available_finish_date, available_type_label,
                                preorder_day, preorder_time, delivery_time, 
                                product_weight_gr, product_description
                        from    product_v
                        where   product_id = ".$p_product_id;

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
