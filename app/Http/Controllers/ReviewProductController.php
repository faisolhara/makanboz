<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class ReviewProductController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_product_id'   => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_product_id   = $request->get('p_product_id');
            $p_offset       = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
            $p_limit        = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

            $query = "select  transaction_line_id, user_id, profile_file, user_name, review_date, review_value, review_note
                        from    review_product_v
                        where   product_id = ".$p_product_id."
                        order by review_date desc
                        limit ".$p_offset.", ".$p_limit;

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
