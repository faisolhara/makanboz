<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;
use Intervention\Image\Facades\Image;

class ProductSaveController extends Controller
{
    protected $now;
    protected $error;

    public function index(Request $request)
    {
        $this->now   = new \DateTime();
        $this->error = FALSE;

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_header' => 'required',
            'p_photos' => 'required',
            'p_prices' => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $productSave = $this->productSave($request);
            if($productSave['o_status'] == -1){
                \DB::rollback();
                return response()->json([
                    'o_status'  => -1,
                    'o_message' => $productSave,
                    ], 200);
            }

            $updateMinMaxPrice = $this->updateMinMaxPrice($productSave);
            if($updateMinMaxPrice['o_status'] == -1){
                \DB::rollback();
                return response()->json([
                    'o_status'  => -1,
                    'o_message' => $updateMinMaxPrice,
                    ], 200);
            }

            $productPriceDel = $this->productPriceDel($request, $productSave);
            if($productPriceDel['o_status'] == -1){
                \DB::rollback();
                return response()->json([
                    'o_status'  => -1,
                    'o_message' => $productPriceDel,
                    ], 200);
            }
            
            $productPriceSaves = $this->productPriceSave($request, $productSave);
            foreach($productPriceSaves as $productPriceSave){
                if($productPriceSave['o_status'] == -1){
                    \DB::rollback();
                    return response()->json([
                        'o_status'  => -1,
                        'o_message' => $productPriceSave,
                        ], 200);
                }
            }

            $productPhotoDel = $this->productPhotoDel($request, $productSave);
            if($productPhotoDel['o_status'] == -1){
                \DB::rollback();
                return response()->json([
                    'o_status'  => -1,
                    'o_message' => $productPhotoDel,
                    ], 200);
            }
            
            $productPhotoSaves = $this->productPhotoSave($request, $productSave);
            foreach($productPhotoSaves as $productPhotoSave){
                if($productPhotoSave['o_status'] == -1){
                    \DB::rollback();
                    return response()->json([
                        'o_status'  => -1,
                        'o_message' => $productPhotoSave,
                        ], 200);
                }
            }
            
            $this->productPhotoUpload($request, $productSave);

            \DB::commit();
            return response()->json([
                'o_status'  => 1,
                'o_message' => [
                    'o_header' => $productSave,
                    'o_photos' => $productPhotoSaves,
                    'o_prices' => $productPriceSaves,
                ],
            ], 200);

        } catch (Exception $e) {
            \DB::rollback();
            return response()->json([
                'o_status'  => -1,
                'o_message' => $productSave,
            ], 200);
        }
    }

    protected function productSave(Request $request){
        $header         = json_decode($request->get('p_header'));
        
        $p_product_id               = intval($header->p_product_id);
        $p_access_token             = $header->p_access_token;
        $p_user_id                  = intval($header->p_user_id);
        $p_product_name             = $header->p_product_name;
        $p_product_description      = $header->p_product_description;
        $p_hashtag                  = $header->p_hashtag;
        $p_unit_price               = $header->p_unit_price;
        $p_category_id              = intval($header->p_category_id);
        $p_available_type           = $header->p_available_type;
        $p_open_off_day             = $header->p_open_off_day;
        $p_available_start_date     = !empty($header->p_available_start_date) ? new \DateTime($header->p_available_start_date) : '';
        $p_available_finish_date    = !empty($header->p_available_finish_date) ? new \DateTime($header->p_available_finish_date) : '';
        $p_available_start_date     = !empty($p_available_start_date) ? $p_available_start_date->format('Y-m-d') : '';
        $p_available_finish_date    = !empty($p_available_finish_date) ? $p_available_finish_date->format('Y-m-d') : '';
        $p_available_sun            = $header->p_available_sun;
        $p_available_mon            = $header->p_available_mon;
        $p_available_tue            = $header->p_available_tue;
        $p_available_wed            = $header->p_available_wed;
        $p_available_thu            = $header->p_available_thu;
        $p_available_fri            = $header->p_available_fri;
        $p_available_sat            = $header->p_available_sat;
        $p_stock_type               = $header->p_stock_type;
        $p_stock_available          = $header->p_stock_available;
        $p_preorder_day             = intval($header->p_preorder_day);
        $p_preorder_time            = intval($header->p_preorder_time);
        $p_delivery_start_time      = intval($header->p_delivery_start_time);
        $p_delivery_finish_time     = intval($header->p_delivery_finish_time);
        $p_product_weight_gr        = intval($header->p_product_weight_gr);
        $p_price_type               = $header->p_price_type;
        $p_free_delivery_overwrite  = $header->p_free_delivery_overwrite;
        $p_free_delivery_km         = intval($header->p_free_delivery_km);
        $p_is_published             = $header->p_is_published;

        $pdo = \DB::connection()->getPdo();

        // calling stored procedure command
        $sql = 'CALL product_save_pc(@o_status, @o_message, @o_product_id, :p_product_id, :p_access_token, :p_user_id, :p_product_name, :p_product_description, :p_hashtag, :p_unit_price, :p_category_id, :p_available_type, :p_open_off_day, :p_available_start_date, :p_available_finish_date, :p_available_sun, :p_available_mon, :p_available_tue, :p_available_wed, :p_available_thu, :p_available_fri, :p_available_sat, :p_stock_type, :p_stock_available, :p_preorder_day, :p_preorder_time, :p_delivery_start_time, :p_delivery_finish_time, :p_product_weight_gr, :p_price_type, :p_free_delivery_overwrite, :p_free_delivery_km, :p_is_published)';

        // prepare for execution of the stored procedure
        $stmt = $pdo->prepare($sql);

        // pass value to the command
        $stmt->bindParam(':p_product_id', $p_product_id, PDO::PARAM_INT);
        $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
        $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
        $stmt->bindParam(':p_product_name', $p_product_name, PDO::PARAM_STR);
        $stmt->bindParam(':p_product_description', $p_product_description, PDO::PARAM_STR);
        $stmt->bindParam(':p_hashtag', $p_hashtag, PDO::PARAM_STR);
        $stmt->bindParam(':p_unit_price', $p_unit_price, PDO::PARAM_STR);
        $stmt->bindParam(':p_category_id', $p_category_id, PDO::PARAM_INT);
        $stmt->bindParam(':p_available_type', $p_available_type, PDO::PARAM_STR);
        $stmt->bindParam(':p_open_off_day', $p_open_off_day, PDO::PARAM_STR);
        $stmt->bindParam(':p_available_start_date', $p_available_start_date, PDO::PARAM_STR);
        $stmt->bindParam(':p_available_finish_date', $p_available_finish_date, PDO::PARAM_STR);
        $stmt->bindParam(':p_available_sun', $p_available_sun, PDO::PARAM_STR);
        $stmt->bindParam(':p_available_mon', $p_available_mon, PDO::PARAM_STR);
        $stmt->bindParam(':p_available_tue', $p_available_tue, PDO::PARAM_STR);
        $stmt->bindParam(':p_available_wed', $p_available_wed, PDO::PARAM_STR);
        $stmt->bindParam(':p_available_thu', $p_available_thu, PDO::PARAM_STR);
        $stmt->bindParam(':p_available_fri', $p_available_fri, PDO::PARAM_STR);
        $stmt->bindParam(':p_available_sat', $p_available_sat, PDO::PARAM_STR);
        $stmt->bindParam(':p_stock_type', $p_stock_type, PDO::PARAM_STR);
        $stmt->bindParam(':p_stock_available', $p_stock_available, PDO::PARAM_INT);
        $stmt->bindParam(':p_preorder_day', $p_preorder_day, PDO::PARAM_INT);
        $stmt->bindParam(':p_preorder_time', $p_preorder_time, PDO::PARAM_INT);
        $stmt->bindParam(':p_delivery_start_time', $p_delivery_start_time, PDO::PARAM_INT);
        $stmt->bindParam(':p_delivery_finish_time', $p_delivery_finish_time, PDO::PARAM_INT);
        $stmt->bindParam(':p_product_weight_gr', $p_product_weight_gr, PDO::PARAM_INT);
        $stmt->bindParam(':p_price_type', $p_price_type, PDO::PARAM_STR);
        $stmt->bindParam(':p_free_delivery_overwrite', $p_free_delivery_overwrite, PDO::PARAM_STR);
        $stmt->bindParam(':p_free_delivery_km', $p_free_delivery_km, PDO::PARAM_INT);
        $stmt->bindParam(':p_is_published', $p_is_published, PDO::PARAM_STR);

        // execute the stored procedure
        $stmt->execute();
        $stmt->closeCursor();

        // execute the second query to get output
        return $pdo->query("SELECT @o_status as o_status, @o_message as o_message, @o_product_id as o_product_id")->fetch(PDO::FETCH_ASSOC);
    }

    protected function updateMinMaxPrice($productSave){
        
        $p_product_id  = $productSave['o_product_id'];
        $pdo = \DB::connection()->getPdo();

        // calling stored procedure command
        $sql = 'CALL update_min_max_price_pc(:p_product_id)';

        // prepare for execution of the stored procedure
        $stmt = $pdo->prepare($sql);

        // pass value to the command
        $stmt->bindParam(':p_product_id', $p_product_id, PDO::PARAM_INT);

        // execute the stored procedure
        $stmt->execute();
        $stmt->closeCursor();

        return [
            'o_status'  => 1,
            'o_message' => '',
        ];
    }

    protected function productPhotoDel(Request $request, $productSave){
        $header         = json_decode($request->get('p_header'));
        $p_access_token = $header->p_access_token;
        $p_user_id      = intval($header->p_user_id);
        $p_product_id   = $productSave['o_product_id'];

        $pdo = \DB::connection()->getPdo();
        // calling stored procedure command
        $sql = 'CALL product_price_del_pc(@o_status, @o_message, :p_access_token, :p_user_id, :p_product_id)';

        // prepare for execution of the stored procedure
        $stmt = $pdo->prepare($sql);

        // pass value to the command
        $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
        $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
        $stmt->bindParam(':p_product_id', $p_product_id, PDO::PARAM_INT);

        // execute the stored procedure
        $stmt->execute();
        $stmt->closeCursor();

        // execute the second query to get output
        return $pdo->query("SELECT @o_status as o_status, @o_message as o_message")->fetch(PDO::FETCH_ASSOC);   
    }

    protected function productPhotoSave(Request $request, $productSave){
        $header    = json_decode($request->get('p_header'));
        $photos    = json_decode($request->get('p_photos'));
        $output    = [];

        foreach ($photos as $photo) {
            
            $p_access_token = $header->p_access_token;
            $p_user_id      = intval($header->p_user_id);
            $p_product_id   = $productSave['o_product_id'];
            $p_seq_no       = $photo->p_seq_no;

            $base64         = 'data:image/jpeg;base64,'.$photo->p_photo;
            $imageName      = md5($p_product_id.'_'.$this->now->format('dmY_His')).'.'.explode('/', explode(':', substr($base64, 0, strpos($base64, ';')))[1])[1];

            $p_photo        = $photo->p_photo;
            $originalPath   = 'images\product\original\\';
            $thumbnailPath  = 'images\product\thumbnail\\';

            $p_original_file    = $imageName;
            $p_thumbnail_file   = $imageName;

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL product_photo_save_pc(@o_status, @o_message, :p_access_token, :p_user_id, :p_product_id, :p_seq_no, :p_thumbnail_file, :p_original_file)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_product_id', $p_product_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_seq_no', $p_seq_no, PDO::PARAM_INT);
            $stmt->bindParam(':p_thumbnail_file', $p_thumbnail_file, PDO::PARAM_STR);
            $stmt->bindParam(':p_original_file', $p_original_file, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();
            $stmt->closeCursor();

            // execute the second query to get output
            $row = $pdo->query("SELECT @o_status as o_status, @o_message as o_message")->fetch(PDO::FETCH_ASSOC);   

            // if($row['o_status'] == 1){
            //     $p_photo_original = Image::make(base64_decode($photo->p_photo))->resize(300, null, function ($constraint) {
            //                                     $constraint->aspectRatio();
            //                                 })->save(public_path($originalPath).$imageName);
            //     $p_photo_thumbnail = Image::make(base64_decode($photo->p_photo))->resize(100, null, function ($constraint) {
            //                                     $constraint->aspectRatio();
            //                                 })->save(public_path($thumbnailPath).$imageName);
            // }
            
            $output [] = $row;
        }

        return $output;
    }

    protected function productPhotoUpload(Request $request, $productSave){
        $header    = json_decode($request->get('p_header'));
        $photos    = json_decode($request->get('p_photos'));
        $output    = [];

        foreach ($photos as $photo) {
            
            $p_product_id   = $productSave['o_product_id'];
            $base64         = 'data:image/jpeg;base64,'.$photo->p_photo;
            $imageName      = md5($p_product_id.'_'.$this->now->format('dmY_His')).'.'.explode('/', explode(':', substr($base64, 0, strpos($base64, ';')))[1])[1];

            $p_photo        = $photo->p_photo;
            $originalPath   = 'images\product\original\\';
            $thumbnailPath  = 'images\product\thumbnail\\';

        
            list($width, $height) = getimagesize($base64);

            if($width > $height){
                $p_photo_original = Image::make(base64_decode($photo->p_photo))->resize(300, null, function ($constraint) {
                                            $constraint->aspectRatio();
                                        })->save(public_path($originalPath).$imageName);
            }else{
                $p_photo_original = Image::make(base64_decode($photo->p_photo))->resize(null, 300, function ($constraint) {
                                            $constraint->aspectRatio();
                                        })->save(public_path($originalPath).$imageName);
            }

            $p_photo_thumbnail = Image::make(base64_decode($photo->p_photo))->resize(100, null, function ($constraint) {
                                            $constraint->aspectRatio();
                                        })->save(public_path($thumbnailPath).$imageName);
        }
    }

    protected function productPriceDel(Request $request, $productSave){
        $header    = json_decode($request->get('p_header'));

        $p_access_token = $header->p_access_token;
        $p_user_id      = intval($header->p_user_id);
        $p_product_id   = $productSave['o_product_id'];

        $pdo = \DB::connection()->getPdo();
        // calling stored procedure command
        $sql = 'CALL product_price_del_pc(@o_status, @o_message, :p_access_token, :p_user_id, :p_product_id)';

        // prepare for execution of the stored procedure
        $stmt = $pdo->prepare($sql);

        // pass value to the command
        $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
        $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
        $stmt->bindParam(':p_product_id', $p_product_id, PDO::PARAM_INT);

        // execute the stored procedure
        $stmt->execute();
        $stmt->closeCursor();

        // execute the second query to get output
        return $pdo->query("SELECT @o_status as o_status, @o_message as o_message")->fetch(PDO::FETCH_ASSOC);   
    }

     protected function productPriceSave(Request $request, $productSave){
        $header    = json_decode($request->get('p_header'));
        $prices    = json_decode($request->get('p_prices'));
        $output    = [];

        foreach ($prices as $price) {
            $p_access_token = $header->p_access_token;
            $p_user_id      = intval($header->p_user_id);
            $p_access_token = $header->p_access_token;
            $p_user_id      = intval($header->p_user_id);
            $p_product_id   = $productSave['o_product_id'];
            $p_plu_code     = $price->p_plu_code;
            $p_plu_name     = $price->p_plu_name;
            $p_min_quantity = intval($price->p_min_quantity);
            $p_max_quantity = intval($price->p_max_quantity);
            $p_unit_price   = floatval($price->p_unit_price);

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL product_price_save_pc(@o_status, @o_message, :p_access_token, :p_user_id, :p_product_id, :p_plu_code, :p_plu_name, :p_min_quantity, :p_max_quantity, :p_unit_price)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_product_id', $p_product_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_plu_code', $p_plu_code, PDO::PARAM_STR);
            $stmt->bindParam(':p_plu_name', $p_plu_name, PDO::PARAM_STR);
            $stmt->bindParam(':p_min_quantity', $p_min_quantity, PDO::PARAM_INT);
            $stmt->bindParam(':p_max_quantity', $p_max_quantity, PDO::PARAM_INT);
            $stmt->bindParam(':p_unit_price', $p_unit_price, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();
            $stmt->closeCursor();

            $output[] = $pdo->query("SELECT @o_status as o_status, @o_message as o_message")->fetch(PDO::FETCH_ASSOC);
        }

        return $output;  
    }

}
