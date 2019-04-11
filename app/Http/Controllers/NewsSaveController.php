<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;
use Intervention\Image\Facades\Image;

class NewsSaveController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'        => 'required',
            'p_news_type'      => 'required',
            'p_news_subject'   => 'required',
            'p_news_teaser'    => 'required',
            'p_news_body'      => 'required',
            'p_period_start'   => 'required',
            'p_period_end'     => 'required',
            'p_picture_file'   => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_user_id          = intval($request->get('p_user_id'));
            $p_news_type        = $request->get('p_news_type');
            $p_news_subject     = $request->get('p_news_subject');
            $p_news_teaser      = $request->get('p_news_teaser');
            $p_news_body        = $request->get('p_news_body');
            $p_period_start     = !empty($request->get('p_period_start')) ? new \DateTime($request->get('p_period_start')) : '';
            $p_period_start     = !empty($p_period_start) ? $p_period_start->format('Y-m-d') : '';
            $p_period_end       = !empty($request->get('p_period_end')) ? new \DateTime($request->get('p_period_end')) : '';
            $p_period_end       = !empty($p_period_end) ? $p_period_end->format('Y-m-d') : '';
            $p_picture_file     = $request->file('p_picture_file');
            $imageName          = md5($p_user_id.'_'.$this->now->format('dmY_His')).'.'.$p_picture_file->getClientOriginalExtension();
            $originalPath       = 'images\news\\';
            $p_original_file    = $originalPath.$imageName;

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL news_save_proc(@o_status, @o_message, :p_user_id, :p_news_type, :p_news_subject, :p_news_teaser, :p_news_body, :p_original_file, :p_period_start, :p_period_end';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_news_type', $p_news_type, PDO::PARAM_STR);
            $stmt->bindParam(':p_news_subject', $p_news_subject, PDO::PARAM_STR);
            $stmt->bindParam(':p_news_teaser', $p_news_teaser, PDO::PARAM_STR);
            $stmt->bindParam(':p_news_body', $p_news_body, PDO::PARAM_STR);
            $stmt->bindParam(':p_original_file', $p_original_file, PDO::PARAM_STR);
            $stmt->bindParam(':p_period_start', $p_period_start, PDO::PARAM_STR);
            $stmt->bindParam(':p_period_end', $p_period_end, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();
            $stmt->closeCursor();

            // execute the second query to get output
            $row = $pdo->query("SELECT @o_status as o_status, @o_message as o_message")->fetch(PDO::FETCH_ASSOC);

            if($row['o_status'] == 1){
                $p_photo_original = Image::make($p_picture_file->getRealPath())->resize(500, 500)->save(public_path($originalPath).$imageName);
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
