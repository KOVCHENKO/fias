<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ApplicationController extends Controller
{
    public function sendApplication(Request $request) {
        Mail::send('mail/new_address', [
            'houseNumber' => $request['houseNumber'],
            'streetName' => $request['streetName'],
            'fullAddress' => $request['fullAddress']
        ], function($message) {
            $message->to(env('MAIL_USERNAME'))
                ->subject('FIAS: NEW ADDRESS');
        });
    }
}
