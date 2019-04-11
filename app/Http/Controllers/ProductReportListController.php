<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class ProductReportListController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

        //  $validation = Validator::make($request->all(),[ 
        //     'p_province_id'         => 'required',
        // ]);

        
        // if($validation->fails()){
        //     $errors = $validation->errors();
        //     return $errors->toJson();
        // }


        try {
            $query = "select  report_list_id, description
                        from    product_report_list_v
                        order by seq_no";

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
