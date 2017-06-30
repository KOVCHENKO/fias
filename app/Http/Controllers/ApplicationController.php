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
        DB::statement("INSERT INTO requests values(DEFAULT,".$request['data']['person_id'].",'".
            $request['data']['new-district']."','".$request['data']['new-region']."','".$request['data']['new-city']."','".$request['data']['new-street']."','".
            $request['data']['new-house']."','".$request['data']['comments']."')");

    }
}
