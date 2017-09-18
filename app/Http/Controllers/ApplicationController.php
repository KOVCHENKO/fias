<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class ApplicationController extends Controller
{
    /* Email для уведомления пользователя о том, что внесен */
    protected $notificationEmail;


    public function sendApplication(Request $request) {
        $returnEmail = $request['data']['return-email'];

        if($returnEmail == '') {
            return json_encode([
               'status' => 'error',
                'message' => 'Заявка не принята. Отправьте заявку еще раз, и введите правильный email'
            ]);
        } elseif($this->applicationEmailValidator($returnEmail) == true) {
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
                    $request['data']['new-house']."','".$request['data']['comments']."','".$request['data']['return-email']."')");
            } else if (isset($request['data']['person_id'])) {
                DB::statement("INSERT INTO requests values(DEFAULT,".$request['data']['person_id'].",'".
                    $request['data']['new-district']."','".$request['data']['new-region']."','".$request['data']['new-city']."','".$request['data']['new-street']."','".
                    $request['data']['new-house']."','".$request['data']['comments']."','".$request['data']['return-email']."')");
            } else {
                DB::statement("INSERT INTO requests values(DEFAULT,".'0'.",'".
                    $request['data']['new-district']."','".$request['data']['new-region']."','".$request['data']['new-city']."','".$request['data']['new-street']."','".
                    $request['data']['new-house']."','".$request['data']['comments']."','".$request['data']['return-email']."')");
            }

            return json_encode([
                'status' => 'success',
                'message' => 'Ваше обращение отправлено на рассмотрение. В ближайшее время Ваше обращение будет рассмотрено, 
                                и на почтовый ящик, указанный в поле "Обратный адрес эл.почты" придет письмо с уведомлением, 
                                просле чего Вы сможете продолжить заполнение персональной информации.'
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

    public function delete($id) {
        DB::table('requests')->where('id', '=', $id)->delete();

        return 'success';
    }

    public function notifyUser($email) {
        $this->notificationEmail = $email;

        Mail::send('mail/user_application_notification', [
            'information' => 'dummy_variable_information'
        ], function($message) {
            $message->to($this->notificationEmail)
                ->subject('Адрес в АС ПУД Зарегистрирован');
        });

        return 'User has been notified regarding new email';
    }

    /* Функция валидатор */
    public function applicationEmailValidator($requestedEmail) {
        $emailsForCheck = DB::table('requests')
            ->select('return_email')->get();

        foreach($emailsForCheck as $emailForCheck) {
            if($emailForCheck->return_email == $requestedEmail) {
                return true;
                break;
            }
        }

        return false;
    }
}
