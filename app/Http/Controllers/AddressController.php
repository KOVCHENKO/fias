<?php

namespace App\Http\Controllers;

use App\Address;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    /* Получить адреса Ахтубинского района */
    public function getAhtubinsk() {
        $posType = 0;

        $districtCities = Address::where('PARENTGUID', '=', '7e684a70-a23a-41b9-9440-302712c3e41d')->get();

        foreach($districtCities as $cityKey => $cityValue) {
        /* First city */
        $districtCity = $districtCities[$cityKey]->AOGUID;
        $cityName =  $districtCities[$cityKey]->FORMALNAME;

        $districtAddress = DB::select('select address."PARENTGUID", address."AOGUID", address."FORMALNAME", houses."HOUSEGUID", houses."HOUSENUM", houses."HOUSEID"
                            from houses inner join address on
                            (address."AOGUID" = houses."AOGUID")
                            where houses."AOGUID" in 
                            (select "AOGUID" from address where "PARENTGUID" =\''.$districtCity.'\') and houses."POS" IS NULL');

        //        dd($districtAddress);
        foreach($districtAddress as $key => $value) {
            try {
                    $fullAddress = 'Астраханская область, Ахтубинский район, '.$cityName.', '.$districtAddress[$key]->FORMALNAME.', '.$districtAddress[$key]->HOUSENUM;

                    /* Запрос через Guzzle на Yandex */
                    $client = new Client();
                    $res = $client->request('GET', 'https://geocode-maps.yandex.ru/1.x/?geocode=' . $fullAddress . '&results=1&format=json');
                    $resJson = json_decode($res->getBody(), TRUE);


                    if(isset($resJson['response']['GeoObjectCollection']['featureMember'][0])) {
                        /* Проставляем адреса */
                        $resJson['response']['GeoObjectCollection']['featureMember'][0];

                        $array = $resJson['response']['GeoObjectCollection']['featureMember'][0];
                        $arrayComponents = $array['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['Components'];

                        /* Посмотреть, есть ли номер дома по yandex API, если есть, то проставить */
                        foreach ($arrayComponents as $value) {
                            foreach ($value as $innerKey => $innerValue) {
                                if ($innerValue == 'house') {
                                    $posType = 1;
                                    break;
                                } elseif ($innerValue == 'street') {
                                    $posType = 2;
                                    break;
                                } else {
                                    $posType = 0;
                                }
                            }
                        }

                        $arrayFeatureMember = $resJson['response']['GeoObjectCollection']['featureMember'];

                        /* По адресам yandex - для Астраханского адреса может быть как первый элемент, так и единственный элемент*/
                        if (isset($arrayFeatureMember[0])) {
                            $arrayFeatureMemberFirst = $arrayFeatureMember[0];
                        } else {
                            $arrayFeatureMemberFirst = $arrayFeatureMember;
                        }

                        /* $pos - местоположение, $lowerCorner - Нижний угол, $upperCorner - Верхний угол */
                        $pos = $arrayFeatureMemberFirst['GeoObject']['Point']['pos'];
                        $lowerCorner = $arrayFeatureMemberFirst['GeoObject']['boundedBy']['Envelope']['lowerCorner'];
                        $upperCorner = $arrayFeatureMemberFirst['GeoObject']['boundedBy']['Envelope']['upperCorner'];

                        /* Перевести точки в данные point */
                        $posPoint = 'POINT(' . str_replace(" ", ",", $pos) . ')';
                        $lowerCornerPoint = 'POINT(' . str_replace(" ", ",", $lowerCorner) . ')';
                        $upperCornerPoint = 'POINT(' . str_replace(" ", ",", $upperCorner) . ')';

                        /* Получить id дома */
                        $singleHouseId = '\'' . $districtAddress[$key]->HOUSEID . '\'';

                        /* Обновить данные о доме */
                        DB::statement('UPDATE "houses" set
                                                        "POS" = ' . $posPoint . ',
                                                        "LOWERCORNER" = ' . $lowerCornerPoint . ',
                                                        "UPPERCORNER" = ' . $upperCornerPoint . ',
                                                        "POSTYPE" = ' . $posType . '
                                                       where "HOUSEID" = ' . $singleHouseId);

                        echo "Номер: " . $key . "Инфо: " . $districtAddress[$key]->HOUSEID . ", PosType: " . $posType . ", NewPos: " . $posPoint . ", NewLowerCorner: " . $lowerCornerPoint . " ,NewUpperCornerPoint: " . $upperCornerPoint . "<br>";
                    }
                } catch (Exception $e) {

                }

            }
//            dd($value);
        }
        }
//        dd($districtAddress);
}
