<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class MyReviewListController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'         => 'required',
            'p_interval'        => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {

            $p_user_id      = $request->get('p_user_id');
            $p_interval     = $request->get('p_interval');
            $p_offset       = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
            $p_limit        = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

            $query = "select  tl.transaction_line_id, u.user_id, u.profile_file, u.user_name, tl.review_value, tl.review_date,
                        tl.review_note,
                        p.product_id, p.product_name, pp.thumbnail_file        
                from    transaction_line tl
                        join  transaction_header th
                          on tl.transaction_id = th.transaction_id
                        join users u
                          on th.buyer_id = u.user_id
                        join products p
                          on tl.product_id = p.product_id
                        join product_photo pp
                          on p.product_id = pp.product_id
                             and pp.seq_no = 1
                where   tl.is_reviewed = 'Y'
                        and tl.seller_id = ".$p_user_id."
                        and date_format(tl.review_date,'%Y%m%d') >= date_add(now(), interval -".$p_interval." day)
                order by tl.review_date desc
                limit ".$p_offset.",".$p_limit;

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
