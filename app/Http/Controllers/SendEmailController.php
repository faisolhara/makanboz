<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Mail\DemoEmail;
use Illuminate\Support\Facades\Mail;


class SendEmailController extends Controller
{

    public function index(Request $request)
    {
        $objDemo = new \stdClass();
        $objDemo->demo_one = 'Demo One Value';
        $objDemo->demo_two = 'Demo Two Value';
        $objDemo->sender = 'SenderUserName';
        $objDemo->receiver = 'ReceiverUserName';
 
        Mail::to($request->get('email'))->send(new DemoEmail($objDemo));
    }
}
