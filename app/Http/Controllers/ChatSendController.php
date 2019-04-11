<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;
use Intervention\Image\Facades\Image;


class ChatSendController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_access_token'  => 'required',
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
            $p_access_token     = $request->get('p_access_token');
            $p_user_id          = intval($request->get('p_user_id'));
            $p_seller_id        = intval($request->get('p_seller_id'));
            $p_buyer_id         = intval($request->get('p_buyer_id'));
            $p_product_id       = intval($request->get('p_product_id'));
            $p_chat_text        = $request->get('p_chat_text');
            $p_picture_file     = $request->file('p_picture_file');
            $p_share_product_id = intval($request->get('p_share_product_id'));

            $imageName          = md5($p_product_id.'_'.$this->now->format('dmY_His')).'.'.$p_picture_file->getClientOriginalExtension();
            $path               = 'images\chat\\';
            $p_picture_file     = $path.$imageName;

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command

            $sql = 'CALL chat_send_pc(@o_status, @o_message, :p_access_token, :p_user_id, :p_seller_id, :p_buyer_id, :p_product_id, :p_chat_text, :p_picture_file, :p_share_product_id)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_seller_id', $p_seller_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_buyer_id', $p_buyer_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_product_id', $p_product_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_chat_text', $p_chat_text, PDO::PARAM_STR);
            $stmt->bindParam(':p_picture_file', $p_picture_file, PDO::PARAM_STR);
            $stmt->bindParam(':p_share_product_id', $p_share_product_id, PDO::PARAM_INT);

            // execute the stored procedure
            $stmt->execute();

            // return $p_product_name;

            $stmt->closeCursor();

            // execute the second query to get output
                $row = $pdo->query("SELECT @o_status as o_status, @o_message as o_message")->fetch(PDO::FETCH_ASSOC);   

            $p_picture_file     = $request->file('p_picture_file');
            if($row['o_status'] == -1){
                $p_picture_file = Image::make($p_picture_file->getRealPath())->resize(500, 500)->save(public_path($path).$imageName);
            }
            \DB::commit();

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
