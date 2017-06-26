<?php

namespace App\Http\Controllers;

use App\Address;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistrictController extends Controller
{
    /* Получить адреса Камызякского района */
    public function getAll($districtId, $districtOffName, $cityKey) {
        $posType = 0;

        $districtCities = Address::where('PARENTGUID', '=', $districtId)->get();
//        foreach($districtCities as $cityKey => $cityValue) {


            /* First city */
            $districtCity = $districtCities[$cityKey]->AOGUID;
            $cityName =  $districtCities[$cityKey]->FORMALNAME;

            $districtAddress = DB::select('select address."PARENTGUID", address."AOGUID", address."FORMALNAME", houses."HOUSEGUID", houses."HOUSENUM", houses."HOUSEID"
                            from houses inner join address on
                            (address."AOGUID" = houses."AOGUID")
                            where houses."AOGUID" in 
                            (select "AOGUID" from address where "PARENTGUID" =\''.$districtCity.'\') and houses."POS" IS NULL');

//            dd($districtAddress);
            foreach($districtAddress as $key => $value) {
                try {
                    $fullAddress = 'Астраханская область, '.$districtOffName.' район, '.$cityName.', '.$districtAddress[$key]->FORMALNAME.', '.$districtAddress[$key]->HOUSENUM;

                    /* Запрос через Guzzle на Yandex */
                    $client = new Client();
                    $res = $client->request('GET', 'https://geocode-maps.yandex.ru/1.x/?geocode=' . $fullAddress . '&results=1&format=json');
//                    $res = $client->request('GET', 'https://http://0s.m5sw6y3pmrss23lbobzq.pfqw4zdfpaxhe5i.cmle.ru/1.x/?geocode=' . $fullAddress . '&results=1&format=json');
                    $resJson = json_decode($res->getBody(), TRUE);

//                    dd($resJson);

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
        }

        public function getAllPosNull() {
            /* Поставить лимит выполнения скрипта - 10 часов */
            set_time_limit(36000);

            $houses = DB::select('select * from hous where "VERSION" IS NOT NULL and "POS" IS NULL');

            foreach($houses as $key => $value) {
                $districtAddress = DB::select('SELECT distinct a."FORMALNAME" AS street, b."FORMALNAME" AS city, hous."AOGUID", c."FORMALNAME" as district,
                                              hous."HOUSENUM" as number, hous."HOUSEID" as id 
                                            FROM addrs AS a 
                                            JOIN addrs AS b on (a."PARENTGUID" = b."AOGUID") 
                                            JOIN addrs AS c on (b."PARENTGUID" = c."AOGUID")
                                            JOIN hous on (a."AOGUID" = hous."AOGUID")
                                            WHERE hous."AOGUID" = \'' . $value->AOGUID . '\' and hous."VERSION" IS NOT NULL');

                foreach ($districtAddress as $key => $value) {
                    if ($value->city == 'Астрахань') {
                        $fullAddress = 'Астраханская область, город Астрахань, ' . $value->street . ', ' . trim($value->number);

                        $position = $this->getPosition($fullAddress);

                        /* Получить id дома */
                        $singleHouseId = '\'' . $value->id . '\'';

                        DB::statement('UPDATE "hous" set
                                                        "POS" = ' . $position["POS"] . ',
                                                        "LOWERCORNER" = ' . $position["LOWERCORNER"] . ',
                                                        "UPPERCORNER" = ' . $position["UPPERCORNER"] . ',
                                                        "POSTYPE" = ' . $position["POSTYPE"] . '
                                                       where "HOUSEID" = ' . $singleHouseId);
                    } else {
                        $fullAddress = 'Астраханская область, ' . $value->district . ' район, город ' . $value->city . ', ' . $value->street . ', ' . trim($value->number);
                        $position = $this->getPosition($fullAddress);

                        /* Получить id дома */
                        $singleHouseId = '\'' . $value->id . '\'';

                        DB::statement('UPDATE "hous" set
                                                        "POS" = ' . $position["POS"] . ',
                                                        "LOWERCORNER" = ' . $position["LOWERCORNER"] . ',
                                                        "UPPERCORNER" = ' . $position["UPPERCORNER"] . ',
                                                        "POSTYPE" = ' . $position["POSTYPE"] . '
                                                       where "HOUSEID" = ' . $singleHouseId);



                    }
                }
            }
        }

        public function getPosition($fullAddress) {
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

                $positions = array(
                    "POS" => $posPoint,
                    "LOWERCORNER" => $lowerCornerPoint,
                    "UPPERCORNER" => $upperCornerPoint,
                    "POSTYPE" => $posType
                );

                return $positions;
            }
        }
}
