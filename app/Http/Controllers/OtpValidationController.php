<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;


class OtpValidationController extends Controller
{

    public function index(Request $request)
    {
        \DB::beginTransaction();

        $validation = Validator::make($request->all(),[ 
            'p_phone_number' => 'required|min:6',
            'p_otp_code'     => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }
        try {
            $p_phone_number          = $request->get('p_phone_number');
            $p_otp_code              = $request->get('p_otp_code');


            $queries  = \DB::select(\DB::raw("select otp_validation_fc('$p_phone_number','$p_otp_code') as o_output"));
            return response()->json($queries, 200);

        } catch (Exception $e) {
            \DB::rollback();
            return response()->json([
                'o_status'  => -1,
                'o_message' => $e->getMessage(),
            ], 200);
        }

    }
}
