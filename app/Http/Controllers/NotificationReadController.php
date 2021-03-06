<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class NotificationReadController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_notification_id'    => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_notification_id           = $request->get('p_notification_id');

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL user_notification_read_pc(:p_notification_id)';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_notification_id', $p_notification_id, PDO::PARAM_INT);

            // execute the stored procedure
            $stmt->execute();

            // return $p_product_name;

            $stmt->closeCursor();

            // execute the second query to get output
            \DB::commit();

            return response()->json([
                'o_status'  => 1,
                'o_message' => 'Success',
            ], 200);

        } catch (Exception $e) {
            \DB::rollback();
            return response()->json([
                'o_status'  => -1,
                'o_message' => $e->getMessage(),
            ], 200);
        }
    }
}
