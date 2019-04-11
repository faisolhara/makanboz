<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class MyTimeLineController extends Controller
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
            $p_user_id     = $request->get('p_user_id');
            $p_offset      = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
            $p_limit       = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

            $query = "select  user_id, following_user_id, following_user_name, following_profile_file, activity_type,
                                activity_date, product_id, thumbnail_file, shop_name, min_price, max_price, stock_type,
                                favorite_count, review_avg, sold_count, available_type_name, delivery_time,
                                fol_following_user_id, fol_following_user_name, fol_following_profile_file
                        from    timeline_v
                        where   user_id = ".$p_user_id."
                        order by activity_date desc
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
