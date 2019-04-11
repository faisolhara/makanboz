<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;
use Intervention\Image\Facades\Image;
use sngrl\SphinxSearch\SphinxSearch;
use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Drivers\Mysqli\Connection;

class SearchPorductController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        $validation = Validator::make($request->all(),[ 
            'p_latitude'    => 'required',
            'p_longitude'   => 'required',
            'p_date'        => 'required',
        ]);

        $conn = new Connection();
        $conn->setParams(array('host' => env('SPHINX_HOST'), 'port' => env('SPHINX_PORT')));
        $sphinx = new SphinxQL($conn);
 
        $p_latitude         = $request->get('p_latitude');
        $p_longitude        = $request->get('p_longitude');
        $p_date             = $request->get('p_date');
        $p_keyword          = $request->get('p_keyword');
        $p_max_delivery_km  = $request->get('p_max_delivery_km');
        $p_day_flag         = $request->get('p_day_flag');
        $p_night_flag       = $request->get('p_night_flag');
        $p_seller_id        = $request->get('p_seller_id');
        $p_city_id          = $request->get('p_city_id');
        $p_province_id      = $request->get('p_province_id');
        $p_start_price      = $request->get('p_start_price');
        $p_end_price        = $request->get('p_end_price');
        $p_category_id      = $request->get('p_category_id');
        $p_top_category_id  = $request->get('p_top_category_id');
        $p_review_avg       = $request->get('p_review_avg');
        $p_order_by         = $request->get('p_order_by');
        $p_offset           = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
        $p_limit            = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

        $sql = "select  id as product_id, original_file, product_name, shop_name,
                        min_price, max_price, stock_type,
                        favorite_count, review_avg, sold_count,
                        available_type_name, delivery_time, 
                        geodist($p_latitude, $p_longitude, latitude, longitude)/1000 as distance_km,
                        ((available_type = 'everyday') or 
                         (available_type = 'period' and $p_date>=available_start_date  and $p_date<=available_finish_date) or
                         (available_type = 'daily' and (available_sun='1' or available_mon='1' or available_tue='1' 
                              or available_wed='1' or available_thu='1' or available_fri='1'
                              or available_sat='1'))
                        ) as flag,
                        day_flag, night_flag, city_id, seller_id, province_id, category_id, top_category_id
                from    products_sph ";

        $where = "where   is_active = 'Y' 
                    and is_published = 'Y' 
                    and flag = 1 ";

        if(!empty($p_keyword)){
            $where .= "and match('@(product_name,hashtag) $p_keyword') ";
        }

        if(!empty($p_max_delivery_km)){
            $where .= "and (geodist($p_latitude, $p_longitude, latitude, longitude)/1000) <= $p_max_delivery_km ";
        }
        
        if(!empty($p_day_flag)){
            $where .= "and day_flag = 'Y' ";
        }

        if(!empty($p_night_flag)){
            $where .= "and night_flag = 'Y' ";
        }

        if(!empty($p_seller_id)){
            $where .= "and seller_id = $p_seller_id ";
        }

        if(!empty($p_city_id)){
            $where .= "and city_id = '$p_city_id' ";
        }

        if(!empty($p_province_id)){
            $where .= "and province_id = '$p_province_id' ";
        }

        if(!empty($p_start_price) && !empty($p_end_price)){
            $where .= "and min_price between $p_start_price and $p_end_price ";
        }

        if(!empty($p_category_id)){
            $where .= "and category_id = $p_category_id ";
        }

        if(!empty($p_top_category_id)){
            $where .= "and top_category_id = $p_top_category_id ";
        }

        if(!empty($p_review_avg)){
            $where .= "and review_avg >= $p_review_avg ";
        }

        if($p_order_by == 'terfavorit'){
            $order = "order by favorite_count desc ";
        }elseif($p_order_by == 'terbaru'){
            $order = "order by id desc ";
        }elseif($p_order_by == 'terbanyak'){
            $order = "order by sold_count desc ";
        }elseif($p_order_by == 'terkecil'){
            $order = "order by min_price asc ";
        }elseif($p_order_by == 'terbesar'){
            $order = "order by min_price desc ";
        }elseif($p_order_by == 'terdekat'){
            $order = "order by distance asc ";
        }else{
            $order = "order by weight() desc ";
        }

        $order = $order."limit ".$p_offset.",". $p_limit;
        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        \DB::beginTransaction();
        try {
            $query = $sphinx->query($sql.$where.$order)
                    ->execute();

            \DB::commit();

            $result = $query->fetchAllAssoc();

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
