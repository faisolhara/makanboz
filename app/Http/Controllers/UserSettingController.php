<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class UserSettingController extends Controller
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

            $query = "select  user_id, notification_sound, notification_enabled, notification_order_update,
                                notification_chat, notification_review, notification_bozpay, hide_activity
                        from    user_setting_v
                        where   user_id = ".$p_user_id;

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
