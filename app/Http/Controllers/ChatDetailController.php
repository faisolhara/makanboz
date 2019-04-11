<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class ChatDetailController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'       => 'required',
            'p_seller_id'     => 'required',
            'p_buyer_id'      => 'required',
            'p_product_id'    => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_user_id      = intval($request->get('p_user_id'));
            $p_seller_id    = intval($request->get('p_seller_id'));
            $p_buyer_id     = intval($request->get('p_buyer_id'));
            $p_product_id   = intval($request->get('p_product_id'));

            $query = "select  u.user_id, u.user_name, u.profile_file,
                            if(cl.sender_id = ".$p_user_id.", 'Sender', 'Recipient') as chat_by,
                            cl.chat_text, cl.chat_date
                    from    chat_line cl
                            join users u
                              on cl.sender_id = u.user_id
                    where   cl.seller_id = ".$p_seller_id."
                            and cl.buyer_id = ".$p_buyer_id."
                            and cl.product_id = ".$p_product_id."
                            and case
                                when u.user_id = ".$p_user_id." then
                                  cl.buyer_deleted
                                else
                                  cl.seller_deleted
                                end = 'N'
                    order by cl.chat_date      
                    ";

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
