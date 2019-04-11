<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class MyReviewCountStarController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'         => 'required',
            'p_review_value'    => 'required',
            'p_interval'        => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {

            $p_user_id      = $request->get('p_user_id');
            $p_review_value = $request->get('p_review_value');
            $p_interval     = $request->get('p_interval');

            $query = "select  count(transaction_line_id) as review_count
                        from    transaction_line tl
                        where   tl.is_reviewed = 'Y'
                                and tl.seller_id = ".$p_user_id."
                                and tl.review_value = ".$p_review_value."
                                and date_format(tl.review_date,'%Y%m%d') >= date_add(now(), interval -".$p_interval." day)";

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
