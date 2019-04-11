<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;


class UserAddressSaveController extends Controller
{

    public function index(Request $request)
    {
        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_access_token'    => 'required',
            'p_user_id'         => 'required',
            'p_receiver_name'   => 'required',
            'p_address_name'    => 'required',
            'p_phone_number'    => 'required',
            'p_province_id'     => 'required',
            'p_city_id'         => 'required',
            'p_district_id'     => 'required',
            'p_postal_code'     => 'required',
            'p_address_detail'  => 'required',
            'p_latitude'        => 'required',
            'p_longitude'       => 'required',
            'p_seller_default'  => 'required',
            'p_buyer_default'   => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_address_id       = intval($request->get('p_address_id'));
            $p_access_token     = $request->get('p_access_token');
            $p_user_id          = intval($request->get('p_user_id'));
            $p_receiver_name    = $request->get('p_receiver_name');
            $p_address_name     = $request->get('p_address_name');
            $p_phone_number     = $request->get('p_phone_number');
            $p_province_id      = $request->get('p_province_id');
            $p_city_id          = intval($request->get('p_city_id'));
            $p_district_id      = intval($request->get('p_district_id'));
            $p_postal_code      = $request->get('p_postal_code');
            $p_address_detail   = $request->get('p_address_detail');
            $p_latitude         = intval($request->get('p_latitude'));
            $p_longitude        = intval($request->get('p_longitude'));
            $p_seller_default   = $request->get('p_seller_default');
            $p_buyer_default    = $request->get('p_buyer_default');

            $pdo = \DB::connection()->getPdo();

            // calling stored procedure command
            $sql = 'CALL user_address_save_pc(@o_status, @o_message, @o_address_id, :p_address_id, :p_access_token, :p_user_id, :p_receiver_name, :p_address_name, :p_phone_number, :p_province_id, :p_city_id, :p_district_id, :p_postal_code, :p_address_detail, :p_latitude, :p_longitude, :p_seller_default, :p_buyer_default)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_address_id', $p_address_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_receiver_name', $p_receiver_name, PDO::PARAM_STR);
            $stmt->bindParam(':p_address_name', $p_address_name, PDO::PARAM_STR);
            $stmt->bindParam(':p_phone_number', $p_phone_number, PDO::PARAM_STR);
            $stmt->bindParam(':p_province_id', $p_province_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_city_id', $p_city_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_district_id', $p_district_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_postal_code', $p_postal_code, PDO::PARAM_STR);
            $stmt->bindParam(':p_address_detail', $p_address_detail, PDO::PARAM_STR);
            $stmt->bindParam(':p_latitude', $p_latitude, PDO::PARAM_STR);
            $stmt->bindParam(':p_longitude', $p_longitude, PDO::PARAM_STR);
            $stmt->bindParam(':p_seller_default', $p_seller_default, PDO::PARAM_STR);
            $stmt->bindParam(':p_buyer_default', $p_buyer_default, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();

            // return $p_address_id;

            $stmt->closeCursor();

            // execute the second query to get output

            // $row = $pdo->query("set @p_address_id = :p_address_id")->fetch(PDO::FETCH_ASSOC);
            // var_dump($row);exit();   
            $row = $pdo->query("SELECT @o_status as o_status, @o_message as o_message, @o_address_id as o_address_id")->fetch(PDO::FETCH_ASSOC);   
            \DB::commit();
            // $row['p_address_id'] = $p_address_id;

            return response()->json($row, 200);

        } catch (Exception $e) {
            \DB::rollback();
            return response()->json([
                'o_status'  => -1,
                'o_message' => $e->getMessage(),
            ], 200);
        }
    }
}
