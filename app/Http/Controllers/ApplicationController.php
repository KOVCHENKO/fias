<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;


class ApplicationController extends Controller
{
    /* Email для уведомления пользователя о том, что внесен */
    protected $notificationEmail;
    protected $verifiedNotificationEmail;


    public function sendApplication(Request $request) {
        $returnEmail = $request['data']['verified-email'];

        if($this->applicationEmailValidator($returnEmail) == true) {
            return json_encode([
                'status' => 'error',
                'message' => 'Заявка с таким email уже была принята. Ответ будет выслан на данный email'
            ]);
        } else {
            Mail::send('mail/new_address', [
                'cityName' => $request['data']['new-city'],
                'houseNumber' => $request['data']['new-house'],
                'streetName' => $request['data']['new-street'],
                'fullAddress' => $request['data']['comments'],
                'return_email' => $request ['data']['return-email']
            ], function($message) {
                $message->to(env('MAIL_USERNAME'))
                    ->subject('FIAS: NEW ADDRESS');
            });

            if ($request['data']['person_id'] == 'undefined') {
                DB::statement("INSERT INTO requests values(DEFAULT,".'0'.",'".
                    $request['data']['new-district']."','".$request['data']['new-region']."','".$request['data']['new-city']."','".$request['data']['new-street']."','".
                    $request['data']['new-house']."','".$request['data']['comments']."','".$request['data']['return-email']."','".$request['data']['verified-email']."')");
            } else if (isset($request['data']['person_id'])) {
                DB::statement("INSERT INTO requests values(DEFAULT,".$request['data']['person_id'].",'".
                    $request['data']['new-district']."','".$request['data']['new-region']."','".$request['data']['new-city']."','".$request['data']['new-street']."','".
                    $request['data']['new-house']."','".$request['data']['comments']."','".$request['data']['return-email']."','".$request['data']['verified-email']."')");            } else {
                DB::statement("INSERT INTO requests values(DEFAULT,".'0'.",'".
                    $request['data']['new-district']."','".$request['data']['new-region']."','".$request['data']['new-city']."','".$request['data']['new-street']."','".
                    $request['data']['new-house']."','".$request['data']['comments']."','".$request['data']['return-email']."','".$request['data']['verified-email']."')");            }

            return json_encode([
                'status' => 'success',
                'message' => 'Ваше обращение отправлено на рассмотрение. В ближайшее время на почтовый ящик, 
                                указанный в поле "Обратный адрес эл.почты".(Не забудьте проверить папку "СПАМ". Письмо может оказаться там) 
                                придет письмо с уведомлением, просле чего Вы сможете продолжить заполнение персональной информации.'
            ]);
        }


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

    /* Внести новую улицу в БД */
    public function addStreetToDatabase(Request $request, QuerryController $qc) {
        $lastAOGUID = $qc->getLastHOUSEGUID();
        $shortCadNum = $qc->defineShortCadNum($request['DISTRICTID'], $request['CITYID']);

        DB::table('addrs')->insert([
            'ACTSTATUS' => 1,
            'AOGUID' => '00000000-0000-0000-0001-'.$lastAOGUID,
            'AOID' => '00000000-0000-0000-0000-'.$lastAOGUID,
            'AOLEVEL' => 7,
            'AREACODE' => NULL, 'AUTOCODE' => NULL, 'CENTSTATUS' => 0,
            'CITYCODE' => NULL, 'CODE' => NULL, 'CURRSTATUS' => 1,
            'ENDDATE' => '2079-06-06 00:00:00',
            'FORMALNAME' => $request['FORMALNAME'],
            'IFNSFL' => NULL, 'IFNSUL' => NULL, 'NEXTID' => NULL,
            'OFFNAME' => $request['FORMALNAME'],
            'OKATO' => NULL, 'OKTMO' => NULL, 'OPERSTATUS' => NULL,
            'PARENTGUID' => $request['PARENTGUID'],
            'PLACECODE' => NULL, 'PLAINCODE' => NULL,
            'POSTALCODE' => $request['POSTALCODE'],
            'PREVID' => NULL, 'REGIONCODE' => NULL, 'SHORTNAME' => $request['SHORTNAME'],
            'STARTDATE' => Carbon::now()->toDateTimeString(),
            'STREETCODE' => NULL, 'TERRIFNSFL' => NULL, 'TERRIFNSUL' => NULL,
            'UPDATEDATE' => NULL, 'CTARCODE' => NULL, 'EXTRCODE' => NULL, 'SEXTCODE' => NULL, 'LIVESTATUS' => NULL,
            'NORMDOC' => NULL, 'PLANCODE' => NULL, 'CADNUM' => NULL, 'DIVTYPE' => NULL,
            'VERSION' => '002', 'SHORTCADNUM' => $shortCadNum
        ]);

        return 'success';
    }

    /* Внести новый город в БД */
    public function addCityToDatabase(Request $request, QuerryController $qc) {
        $lastAOGUID = $qc->getLastHOUSEGUID();
        $shortCadNum = $qc->defineShortCadNum($request['DISTRICTID'], $request['CITYID']);

        DB::table('addrs')->insert([
            'ACTSTATUS' => 1,
            'AOGUID' => '00000000-0000-0000-0001-'.$lastAOGUID,
            'AOID' => '00000000-0000-0000-0000-'.$lastAOGUID,
            'AOLEVEL' => 7,
            'AREACODE' => NULL, 'AUTOCODE' => NULL, 'CENTSTATUS' => 0,
            'CITYCODE' => NULL, 'CODE' => NULL, 'CURRSTATUS' => 1,
            'ENDDATE' => '2079-06-06 00:00:00',
            'FORMALNAME' => $request['FORMALNAME'],
            'IFNSFL' => NULL, 'IFNSUL' => NULL, 'NEXTID' => NULL,
            'OFFNAME' => $request['FORMALNAME'],
            'OKATO' => NULL, 'OKTMO' => NULL, 'OPERSTATUS' => NULL,
            'PARENTGUID' => $request['PARENTGUID'],
            'PLACECODE' => NULL, 'PLAINCODE' => NULL,
            'POSTALCODE' => $request['POSTALCODE'],
            'PREVID' => NULL, 'REGIONCODE' => NULL, 'SHORTNAME' => $request['SHORTNAME'],
            'STARTDATE' => Carbon::now()->toDateTimeString(),
            'STREETCODE' => NULL, 'TERRIFNSFL' => NULL, 'TERRIFNSUL' => NULL,
            'UPDATEDATE' => NULL, 'CTARCODE' => NULL, 'EXTRCODE' => NULL, 'SEXTCODE' => NULL, 'LIVESTATUS' => NULL,
            'NORMDOC' => NULL, 'PLANCODE' => NULL, 'CADNUM' => NULL, 'DIVTYPE' => NULL,
            'VERSION' => '002', 'SHORTCADNUM' => $shortCadNum
        ]);

        return 'success';
    }



    /* Удалить запрос на внесение в БД */
    public function delete($id) {
        DB::table('requests')->where('id', '=', $id)->delete();

        return 'success';
    }

    public function notifyUser(Request $request) {
        $this->notificationEmail = $request['notificationEmail'];
        $this->verifiedNotificationEmail = $request['verifiedEmail'];

        Mail::send('mail/user_application_notification', [
            'information' => 'dummy_variable_information'
        ], function($message) {
            $message->to($this->verifiedNotificationEmail)
                ->subject('Адрес в АС ПУД Зарегистрирован');
        });

        if($this->notificationEmail != '') {
            Mail::send('mail/user_application_notification', [
                'information' => 'dummy_variable_information'
            ], function($message) {
                $message->to($this->notificationEmail)
                    ->subject('Адрес в АС ПУД Зарегистрирован');
            });
        }

        return 'User has been notified regarding new email';
    }

    /* Функция валидатор */
    public function applicationEmailValidator($requestedEmail) {
        $emailsForCheck = DB::table('requests')
            ->select('verified_email')->get();

        foreach($emailsForCheck as $emailForCheck) {
            if($emailForCheck->verified_email == $requestedEmail) {
                return true;
                break;
            }
        }

        return false;
    }
}
