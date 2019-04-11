<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class ShopMainController extends Controller
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
            $p_user_id          = $request->get('p_user_id');

            $query = "select  user_id, profile_file, user_name, seller_type, follower_count, following_count,
                                total_bozpay, total_point, product_count, register_date, review_avg, 
                                sold_count, succeed_persen, last_seen, shop_name, free_delivery_km
                        from    user_main_v
                        where   user_id = ".$p_user_id;

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
