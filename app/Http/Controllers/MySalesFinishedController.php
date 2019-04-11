<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class MySalesFinishedController extends Controller
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

            $p_user_id     = $request->get('p_user_id');
            $p_offset      = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
            $p_limit       = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;


            $query = "select  seller_id, delivery_date, day_name, transaction_id, 
                                product_id, thumbnail_file, product_name, plu_code, plu_name,
                                buyer_id, profile_file, user_name, quantity
                        from    my_sales_finished_v
                        where   seller_id = ".$p_user_id."
                        order by delivery_date
                        limit ".$p_offset." , ".$p_limit;

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
