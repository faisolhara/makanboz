<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class NewsListController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

        try {

            $p_offset = !empty($request->get('p_offset')) ? $request->get('p_offset') : 0;
            $p_limit = !empty($request->get('p_limit')) ? $request->get('p_limit') : 10;

            $query = "select  news_id, news_date, news_type, news_subject, news_teaser, news_body, picture_file
                        from    news_v
                        where   date_format(now(),'%Y%m%d') between date_format(period_start,'%Y%m%d') and date_format(period_end,'%Y%m%d')
                                and news_type = 'update'
                        order by news_id desc
                        limit ".$p_offset."," .$p_limit;

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
