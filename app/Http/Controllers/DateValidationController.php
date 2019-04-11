<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class DateValidationController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_product_id'       => 'required',
            'p_delivery_date'    => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_product_id   = $request->get('p_product_id');
            $p_delivery_date   = !empty($request->get('p_delivery_date')) ? new \DateTime($request->get('p_delivery_date')) : '';
            $p_delivery_date   = !empty($p_delivery_date) ? $p_delivery_date->format('Y-m-d') : '';

            $query = "select check_date_valid_fc($p_product_id,'$p_delivery_date') as o_output";

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
