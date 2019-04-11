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

class SimilarProductController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        $validation = Validator::make($request->all(),[ 
            'p_product_name'    => 'required',
        ]);

        $conn = new Connection();
        $conn->setParams(array('host' => env('SPHINX_HOST'), 'port' => env('SPHINX_PORT')));
        $sphinx = new SphinxQL($conn);
 
        $p_product_name     = $request->get('p_product_name');
        $p_offset           = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
        $p_limit            = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

        $sql = "select  id as product_id, original_file, product_name, shop_name, min_price, max_price, stock_type,
                        favorite_count, review_avg, sold_count, available_type_name, delivery_time
                from    products_sph ";

        $where = "where is_published = 'Y' ";

        if(!empty($p_product_name)){
            $where .= "and  match('$p_product_name') ";
        }

        $order = "order by weight() desc limit ".$p_offset.",". $p_limit;
        
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
