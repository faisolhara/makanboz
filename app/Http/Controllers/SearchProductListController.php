<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class SearchProductListController extends Controller
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
            $p_user_id      = $request->get('p_user_id');
            $p_keyword      = $request->get('p_keyword');
            $p_offset       = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
            $p_limit        = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

            $query = "select  keyword
                    from    (
                              select  keyword, search_date
                              from    user_search
                              where   user_id = ".$p_user_id." 
                                      and keyword like concat('".$p_keyword."','%')
                              union
                              select  keyword, null
                              from    product_search_list
                              where   keyword like concat('".$p_keyword."','%')
                            ) a
                    order by a.search_date desc, keyword
                    limit ".$p_offset.",". $p_limit;

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
