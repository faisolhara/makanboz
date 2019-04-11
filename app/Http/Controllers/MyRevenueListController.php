<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class MyRevenueListController extends Controller
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

            $p_user_id  = $request->get('p_user_id');
            $p_interval = $request->get('p_interval');
            $p_offset   = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
            $p_limit    = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

            $query = "select  th.buyer_id, u.profile_file, u.user_name, th.transaction_id, th.transaction_number, th.transaction_date,
                                ts.subtotal_product, ts.subtotal_delivery, ts.subtotal_product + ts.subtotal_delivery as subtotal
                        from    transaction_header th
                                join transaction_seller ts
                                  on th.transaction_id = ts.transaction_id
                                join users u
                                  on th.buyer_id = u.user_id
                        where   th.is_paid = 'Y'
                                and ts.seller_id = ".$p_user_id."
                                and th.transaction_date >= date_add(str_to_date(date_format(now(),'%Y%m%d'),'%Y%m%d'), interval (-1 * ".$p_interval.") day)
                        order by th.transaction_date desc
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
