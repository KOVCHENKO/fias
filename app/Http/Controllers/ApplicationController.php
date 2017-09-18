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

    /* For laravel blade */
    public function getAllApplications() {
        $requests = DB::table('requests')->get();

        return view('applications')->with('requests', $requests);
    }

    /* For Vue Spa - get view */
    public function getAllApplicationsView() {
        return view('applications_vue');
    }

    /* For Vue Spa - just request */
    public function getAllApplicationsSPA() {
        $requests = DB::table('requests')->get();

        return $requests;
    }

    /* Add address to database */
    public function addAddressToDatabase(Request $request, QuerryController $qc) {
        $houseGUID = $qc->getLastHOUSEGUID();
        $shortCadNum = $qc->defineShortCadNum($request['DISTRICTID'], $request['CITYID']);

        DB::table('hous')->insert([
            'AOGUID' => $request['AOGUID'],
            'BUILDNUM' => NULL, 'ENDDATE' => NULL, 'ESTSTATUS' => NULL,
            'HOUSEGUID' => '00000000-0000-0000-0001-'.$houseGUID,
            'HOUSEID' => '00000000-0000-0000-0000-'.$houseGUID,
            'HOUSENUM' => $request['HOUSENUM'],
            'STATSTATUS' => NULL, 'IFNSFL' => NULL, 'IFNSUL' => NULL, 'OKATO' => NULL, 'OKTMO' => NULL,
            'POSTALCODE' => $request['POSTALCODE'],
            'STARTDATE' => NULL, 'STRUCNUM' => NULL, 'STRSTATUS' => NULL, 'TERRIFNSFL' => NULL,
            'TERRIFNSUL' => NULL, 'UPDATEDATE' => NULL, 'NORMDOC' => NULL, 'COUNTER' => NULL, 'CADNUM' => NULL,
            'DIVTYPE' => NULL, 'POS' => NULL, 'LOWERCORNER' => NULL, 'UPPERCORNER' => NULL, 'POSTYPE' => NULL,
            'VERSION' => '001', 'SHORTCADNUM' => $shortCadNum
        ]);

        return 'success';
    }

    public function delete($id) {
        DB::table('requests')->where('id', '=', $id)->delete();

        return 'success';
    }
}
