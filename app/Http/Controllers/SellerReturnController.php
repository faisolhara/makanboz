<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;
use Intervention\Image\Facades\Image;


class SellerReturnController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_access_token'           => 'required',
            'p_transaction_line_id'    => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_access_token           = $request->get('p_access_token');
            $p_transaction_line_id    = intval($request->get('p_transaction_line_id'));
            $p_return_note            = $request->get('p_return_note');

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL seller_return_pc(@o_status, @o_message, :p_access_token, :p_transaction_line_id, :p_return_note)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_access_token', $p_access_token, PDO::PARAM_STR);
            $stmt->bindParam(':p_transaction_line_id', $p_transaction_line_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_return_note', $p_return_note, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();

            // return $p_product_name;

            $stmt->closeCursor();

            // execute the second query to get output

            // $row = $pdo->query("set @p_product_name = :p_product_name")->fetch(PDO::FETCH_ASSOC);
            // var_dump($row);exit();   
            $row = $pdo->query("SELECT @o_status as o_status, @o_message as o_message")->fetch(PDO::FETCH_ASSOC);   
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
