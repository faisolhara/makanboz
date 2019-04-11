<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class NotificationTransactionImageController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_notification_id'         => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {

            $p_notification_id = $request->get('p_notification_id');

            $query = "select  thumbnail_file
                        from    notification_transaction_image_v
                        where   notification_id = ".$p_notification_id."
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
