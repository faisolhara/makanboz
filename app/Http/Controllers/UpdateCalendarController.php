<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use PDO;

class UpdateCalendarController extends Controller
{
    protected $now;

    public function index(Request $request)
    {
        $this->now = new \DateTime();

        \DB::beginTransaction();

         $validation = Validator::make($request->all(),[ 
            'p_user_id'        => 'required',
            'p_date'           => 'required',
            'p_off_day'        => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return $errors->toJson();
        }

        try {
            $p_user_id  = intval($request->get('p_user_id'));
            $p_off_day  = $request->get('p_off_day');
            $p_date     = !empty($request->get('p_date')) ? new \DateTime($request->get('p_date')) : '';
            $p_date     = !empty($p_date) ? $p_date->format('Y-m-d') : '';

            $pdo = \DB::connection()->getPdo();
            // calling stored procedure command
            $sql = 'CALL update_calendar(@o_status, @o_message, :p_user_id, :p_date, :p_off_day';

            // prepare for execution of the stored procedure
            $stmt = $pdo->prepare($sql);

            // pass value to the command
            $stmt->bindParam(':p_user_id', $p_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':p_date', $p_date, PDO::PARAM_STR);
            $stmt->bindParam(':p_off_day', $p_off_day, PDO::PARAM_STR);

            // execute the stored procedure
            $stmt->execute();
            $stmt->closeCursor();

            // execute the second query to get output
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
