<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class ApplicationController extends Controller
{
    public function sendApplication(Request $request) {
        Mail::send('mail/new_address', [
            'houseNumber' => $request['data']['new-house'],
            'streetName' => $request['data']['new-street'],
            'fullAddress' => $request['data']['comments']
        ], function($message) {
            $message->to(env('MAIL_USERNAME'))
                ->subject('FIAS: NEW ADDRESS');
        });

        if ($request['data']['person_id'] == 'undefined') {
            DB::statement("INSERT INTO requests values(DEFAULT,".'0'.",'".
                $request['data']['new-district']."','".$request['data']['new-region']."','".$request['data']['new-city']."','".$request['data']['new-street']."','".
                $request['data']['new-house']."','".$request['data']['comments']."')");
        } else if (isset($request['data']['person_id'])) {
            DB::statement("INSERT INTO requests values(DEFAULT,".$request['data']['person_id'].",'".
                $request['data']['new-district']."','".$request['data']['new-region']."','".$request['data']['new-city']."','".$request['data']['new-street']."','".
                $request['data']['new-house']."','".$request['data']['comments']."')");
        } else {
            DB::statement("INSERT INTO requests values(DEFAULT,".'0'.",'".
                $request['data']['new-district']."','".$request['data']['new-region']."','".$request['data']['new-city']."','".$request['data']['new-street']."','".
                $request['data']['new-house']."','".$request['data']['comments']."')");
        }

        return "Request has been performed. Application will be sent to the database.";
    }

    public function getAllApplications() {
        $requests = DB::table('requests')->get();

        return view('applications')->with('requests', $requests);
    }

    public function delete($id) {
        DB::table('requests')->where('id', '=', $id)->delete();

        return redirect()->back();
    }
}
