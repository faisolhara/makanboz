<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class ProductFilterReviewController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

        //  $validation = Validator::make($request->all(),[ 
        //     'p_user_id'       => 'required',
        // ]);

        
        // if($validation->fails()){
        //     $errors = $validation->errors();
        //     return $errors->toJson();
        // }


        try {
            $p_limit    = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

            $query = "select review_star, review_label
                        from    product_filter_review_v
                        order by 1 desc
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
