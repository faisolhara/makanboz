<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class PaymentMethodController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'         => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {

            $p_user_id = $request->get('p_user_id');

            $query = "select  pm.payment_method_id, pm.group_type, pm.method_name,
                                pm.remark1, pm.remark2,
                                if(bozpay_flag = 'Y', (select (total_bozpay - total_request) from user_bozpay where user_id = ".$p_user_id."), null) as saldo
                        from    payment_method pm force index (payment_method_idx1)
                        where   pm.is_active = 'Y'
                        order by pm.seq_no";

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
