<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class DefaultShopDeliveryController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_cart_id'                 => 'required',
            'p_destination_address_id' => 'required',
        ]);

        
        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_destination_address_id = $request->get('p_destination_address_id');
            $p_cart_id                 = json_decode($request->get('p_cart_id'));
            $p_temp_cart_id = '';
            foreach ($p_cart_id as $key => $row) {
                $p_temp_cart_id .= $row->p_cart_id;
                if($key + 1 < count($p_cart_id)){
                    $p_temp_cart_id .= ",";
                }
            }
            $p_cart_id = $p_temp_cart_id;
            $query = "select  sc.delivery_date, u.shop_name, 
                            concat(date_format(date_add(sc.delivery_date, interval p.delivery_start_time minute),'%H:%i'),' - ',
                                   date_format(date_add(sc.delivery_date, interval p.delivery_finish_time minute),'%H:%i')) as delivery_time,
                            concat('Seller Delivery', ' (' ,sum(sc.quantity), ')') as delivery_name, 
                            get_delivery_cost_fc(p.product_id, ".$p_destination_address_id.") as delivery_cost 
                    from    shopping_cart sc 
                            join products p 
                              on sc.product_id = p.product_id
                            join users u
                              on p.seller_id = u.user_id
                    where   sc.cart_id in (".$p_cart_id.") 
                    group by sc.delivery_date, u.shop_name, sc.delivery_date, p.delivery_start_time, p.delivery_finish_time, p.product_id
                    order by sc.delivery_date, u.shop_name, p.delivery_start_time, p.delivery_finish_time 
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
