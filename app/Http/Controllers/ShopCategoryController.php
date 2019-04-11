<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class ShopCategoryController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_seller_id'       => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_seller_id          = $request->get('p_seller_id');

            $query = "select  c.category_id, c.icon_file, c.category_name, count(p.product_id) as total_product
                        from    categories c force index (categories_idx2)
                                join products p
                                  on c.category_id = p.category_id
                                     and p.is_active = 'Y'
                                     and p.is_published = 'Y' -- tergantung kalo yang melihat pemilik, maka semua tampil         
                                     and p.seller_id = ".$p_seller_id."
                        where   c.parent_category_id = 0
                                and c.is_active = 'Y'
                        group by c.category_id, c.icon_file, c.category_name
                        order by c.seq_no";

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
