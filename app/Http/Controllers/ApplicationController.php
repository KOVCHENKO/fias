<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class ApplicationController extends Controller
{
    public function sendApplication(Request $request) {
        /*Mail::send('mail/new_address', [
            'houseNumber' => $request['new-house'],
            'streetName' => $request['new-street'],
            'fullAddress' => $request['comments']
        ], function($message) {
            $message->to(env('MAIL_USERNAME'))
                ->subject('FIAS: NEW ADDRESS');
        });*/
        Log::info(var_export($request,true));
        Log::info("INSERT INTO requests values(DEFAULT,".$request->input('person_id').",".
            $request['new-district'].",".$request['new-region'].",".$request['new-city'].",".$request['new-street'].",".
            $request['new-house'].",".$request['comments']);
        DB::statement("INSERT INTO requests values(DEFAULT,".$request['person_id'].",".
            $request['new-district'].",".$request['new-region'].",".$request['new-city'].",".$request['new-street'].",".
            $request['new-house'].",".$request['comments']);

    }
}
