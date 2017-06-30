<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ApplicationController extends Controller
{
    public function sendApplication(Request $request) {
        Mail::send('mail/new_address', [
            'houseNumber' => $request['new-house'],
            'streetName' => $request['new-street'],
            'fullAddress' => $request['comments']
        ], function($message) {
            $message->to(env('MAIL_USERNAME'))
                ->subject('FIAS: NEW ADDRESS');
        });
        DB::statement("INSERT INTO requests values(DEFAULT,".$request['person_id'].",".
            $request['new-district'].",".$request['new-region'].",".$request['new-city'].",".$request['new-street'].",".
            $request['new-house'].",".$request['comments']);
    }
}
