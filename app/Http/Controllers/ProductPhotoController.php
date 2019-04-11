<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class ProductPhotoController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_product_id'       => 'required',
        ]);
        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_product_id = $request->get('p_product_id');

            $query = "select  product_id, seq_no, thumbnail_file, original_file
                    from    product_photo_v
                    where   product_id = ".$p_product_id."
                    order by seq_no";

            $result = \DB::select(\DB::raw($query));
            $array = [];
            
            foreach ($result as $model) {
                $model->thumbnail_file = 'images/product/thumbnail/'.$model->thumbnail_file;
                $model->original_file = 'images/product/original/'.$model->original_file;
                $array [] = $model;
            }

            return response()->json($array, 200);

        } catch (Exception $e) {
            \DB::rollback();
            return response()->json([
                'o_status'  => -1,
                'o_message' => $e->getMessage(),
            ], 200);
        }
    }
}
