<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class ProductAvailableController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_product_id'         => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_product_id      = $request->get('p_product_id');

            $query = "select  p.available_type, 
                                p.available_start_date, p.available_finish_date, 
                                p.available_sun, p.available_mon, p.available_tue,
                                p.available_wed, p.available_thu, p.available_fri, p.available_sat
                        from    products p
                        where   p.product_id = ".$p_product_id;

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
