<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;
use Intervention\Image\Facades\Image;


class BuyerReviewPhotoController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_transaction_line_id'  => 'required',
            'p_seq_no'               => 'required',
            'p_picture_file'         => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_transaction_line_id  = intval($request->get('p_transaction_line_id'));
            $p_seq_no               = $request->get('p_seq_no');
            $p_picture_file         = $request->file('p_picture_file');
            $imageName              = md5($p_transaction_line_id.'_'.$this->now->format('dmY_His')).'.'.$p_picture_file->getClientOriginalExtension();
            $originalPath            = 'images\buyer-review\\';

            $p_original_file        = $imageName;

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL buyer_review_photo_pc(@o_status, @o_message, :p_transaction_line_id, :p_seq_no, :p_original_file)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_transaction_line_id', $p_transaction_line_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_seq_no', $p_seq_no, PDO::PARAM_INT);
            $stmt->bindParam(':p_original_file', $p_original_file, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();
            $stmt->closeCursor();

            // execute the second query to get output
            $row = $pdo->query("SELECT @o_status as o_status, @o_message as o_message")->fetch(PDO::FETCH_ASSOC);   

            if($row['o_status'] == 1){
                $p_picture_file_original = Image::make($p_picture_file->getRealPath())->resize(500, 500)->save(public_path($originalPath).$imageName);
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
