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

class SearchShopListController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        $validation = Validator::make($request->all(),[ 
            // 'p_keyword'    => 'required',
        ]);
        
        $conn = new Connection();
        $conn->setParams(array('host' => env('SPHINX_HOST'), 'port' => env('SPHINX_PORT')));
        $sphinx = new SphinxQL($conn);
 
        $p_keyword   = $request->get('p_keyword');
        $p_offset    = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
        $p_limit     = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

        $sql = "select  id as user_id, user_name, shop_name, profile_file 
                from    users_sph ";

        $where = "where is_active = 'Y' ";

        if(!empty($p_keyword)){
            $where .= "and match('*$p_keyword*') ";
        }
        
        $order = "order by weight() desc, shop_name asc 
                limit ".$p_offset.",". $p_limit;
        

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
