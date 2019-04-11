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

class GetSphinxController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        $validation = Validator::make($request->all(),[ 
            'p_seller_id'    => 'int',
        ]);

        $conn = new Connection();
        $conn->setParams(array('host' => '192.168.107.9', 'port' => 9301));
        $sphinx = new SphinxQL($conn);
 
        $p_is_published    = $request->get('p_is_published');
        $p_product_name    = $request->get('p_product_name');
        $p_day_flag        = $request->get('p_day_flag');
        $p_night_flag      = $request->get('p_night_flag');
        $p_seller_id       = $request->get('p_seller_id');
        $p_city_id         = $request->get('p_city_id');
        $p_province_id     = $request->get('p_province_id');
        $p_min_price       = $request->get('p_min_price');
        $p_max_price       = $request->get('p_max_price');
        $p_category_id     = $request->get('p_category_id');
        $p_top_category_id = $request->get('p_top_category_id');
        $p_review_avg      = $request->get('p_review_avg');
        $p_delivery_date   = !empty($request->get('p_delivery_date')) ? new \DateTime($request->get('p_delivery_date')) : '';
        $p_delivery_date   = !empty($p_delivery_date) ? $p_delivery_date->format('Y-m-d') : '';

        $sql = "select id as product_id, shop_name, product_name, weight() from products_sph ";

        $where = "where is_active = 'Y' ";

        if(!empty($p_is_published)){
            $where .= "and is_published = '$p_is_published' ";
        }

        if(!empty($p_product_name)){
            $where .= "and match('@(product_name,hashtag) $p_product_name') ";
        }

        if(!empty($p_day_flag)){
            $where .= "and day_flag = '$p_day_flag' ";
        }

        if(!empty($p_night_flag)){
            $where .= "and night_flag = '$p_night_flag' ";
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

        if(!empty($p_min_price) && !empty($p_max_price)){
            $where .= "and min_price between $p_min_price and $p_max_price ";
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

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        \DB::beginTransaction();
        try {
            $query = $sphinx->query($sql.$where)
                    ->execute();

            \DB::commit();

            $result = $query->fetchAllAssoc();

            // dd($result);
            $products = [];
            foreach ($result as $data) {
                $product_id = $data['product_id'];
                $queries = \DB::select(\DB::raw("select check_date_valid_fc($product_id,'$p_delivery_date') as o_output"));
                if($queries[0]->o_output == 'Y'){
                    $products [] = $data;
                }
            }
            // dd(count($products));
            return response()->json($products, 200);

        } catch (Exception $e) {
            \DB::rollback();
            return response()->json([
                'o_status'  => -1,
                'o_message' => $e->getMessage(),
            ], 200);
        }
    }
}
