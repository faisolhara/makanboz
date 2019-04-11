<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class FollowingListController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'       => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_user_id          = $request->get('p_user_id');
            $p_selected_user_id = $request->get('p_selected_user_id');
            $p_keyword          = $request->get('p_keyword');
            $p_offset           = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
            $p_limit            = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

            $query = "select  ufv.follow_date, ufv.following_user_id, ufv.following_profile_file, 
                                ufv.following_user_name, ufv.following_shop_name,
                                case
                                when uf.user_id is not null then
                                  'Y'
                                else
                                  'N'
                                end follow_flag
                        from    user_following_v ufv
                                join user_follow uf
                                  on uf.user_id = ".$p_user_id."
                                     and uf.following_user_id = ufv.following_user_id
                        where   ufv.user_id = ".$p_selected_user_id." 
                                and (ufv.following_user_name like concat('%','".$p_keyword."','%')
                                     or ufv.following_shop_name like concat('%','".$p_keyword."','%'))
                        order by ufv.follow_date desc
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
