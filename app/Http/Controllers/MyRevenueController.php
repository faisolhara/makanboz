<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class MyRevenueController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'         => 'required',
            'p_interval'        => 'required',
            'p_type'            => 'required',
            'p_offset'          => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {

            $p_user_id  = $request->get('p_user_id');
            $p_interval = $request->get('p_interval');
            $p_type     = $request->get('p_type');
            $p_offset   = $request->get('p_offset');

            $query = "select get_revenue_fc (".$p_user_id.", ".$p_interval.", '".$p_type."', ".$p_offset.") as total";

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
