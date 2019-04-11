<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class ProductFilterCategoryController extends Controller
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
            $p_user_id  = $request->get('p_user_id');
            $p_limit    = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

            $query = "select  filter_date, seq_no, category_id, category_name
                        from    product_filter_category_v
                        where   ifnull(user_id, ".$p_user_id.") = ".$p_user_id." 
                        order by 2, 3
                        limit ".$p_limit;

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
