<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class ShoppingCartListController extends Controller
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
            $p_user_id      = $request->get('p_user_id');

            $query = "select  user_id, cart_id, delivery_date, day_name, product_id, thumbnail_file, original_file,
                    product_name, shop_name, quantity, plu_code, plu_name, delivery_time, unit_price, subtotal, price_change, date_valid
                    price_change, date_valid
                    from    shopping_cart_v
                    where   user_id = ".$p_user_id." 
                    order by delivery_date
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
