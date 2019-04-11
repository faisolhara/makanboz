<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;
use Intervention\Image\Facades\Image;


class ProductPhotoSaveController extends Controller
{
    protected $now;
    const SALT = 'makanboz';

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_access_token' => 'required',
            'p_user_id'      => 'required',
            'p_product_id'   => 'required',
            'p_seq_no'       => 'required',
            'p_photo'        => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }


        try {
            $p_access_token = $request->get('p_access_token');
            $p_user_id      = intval($request->get('p_user_id'));
            $p_product_id   = intval($request->get('p_product_id'));
            $p_seq_no       = $request->get('p_seq_no');
            $p_photo        = $request->file('p_photo');
            $imageName      = md5($p_product_id.'_'.$this->now->format('dmY_His')).'.'.$p_photo->getClientOriginalExtension();
            $originalPath   = 'images\product\original\\';
            $thumbnailPath  = 'images\product\thumbnail\\';

            $p_original_file    = $originalPath.$imageName;
            $p_thumbnail_file   = $thumbnailPath.$imageName;

            // $pdo = \DB::connection()->getPdo();
            // // calling stored procedure command
            // $sql = 'CALL product_photo_save_pc(@o_status, @o_message, :p_access_token, :p_user_id, :p_product_id, :p_seq_no, :p_thumbnail_file, :p_original_file)';

            // // prepare for execution of the stored procedure
            // $stmt = $pdo->prepare($sql);

            // // pass value to the command
            // $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            // $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
            // $stmt->bindParam(':p_product_id', $p_product_id, PDO::PARAM_INT);
            // $stmt->bindParam(':p_seq_no', $p_product_id, PDO::PARAM_INT);
            // $stmt->bindParam(':p_thumbnail_file', $p_thumbnail_file, PDO::PARAM_STR);
            // $stmt->bindParam(':p_original_file', $p_original_file, PDO::PARAM_STR);

            // execute the stored procedure
            // $stmt->execute();
            // $stmt->closeCursor();

            // execute the second query to get output
            // $row = $pdo->query("SELECT @o_status as o_status, @o_message as o_message")->fetch(PDO::FETCH_ASSOC);   

            // if($row['o_status'] == -1){
                list($width, $height) = getimagesize($request->file('p_photo'));
                if($width > $height){
                    $p_photo_original = Image::make($p_photo->getRealPath())->resize(300, null, function ($constraint) {
                                            $constraint->aspectRatio();
                                        })->save(public_path($originalPath).$imageName);
                    $p_photo_thumbnail = Image::make($p_photo->getRealPath())->resize(100, null, function ($constraint) {
                                                $constraint->aspectRatio();
                                            })->save(public_path($thumbnailPath).$imageName);
                }else{
                    $p_photo_original = Image::make($p_photo->getRealPath())->resize(null, 300, function ($constraint) {
                                            $constraint->aspectRatio();
                                        })->save(public_path($originalPath).$imageName);
                    $p_photo_thumbnail = Image::make($p_photo->getRealPath())->resize(null, 100, function ($constraint) {
                                                $constraint->aspectRatio();
                                            })->save(public_path($thumbnailPath).$imageName);
                }

            // }
            
            // \DB::commit();

            // return response()->json($row, 200);

        } catch (Exception $e) {
            \DB::rollback();
            return response()->json([
                'o_status'  => -1,
                'o_message' => $e->getMessage(),
            ], 200);
        }
    }
}
