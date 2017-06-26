<?php

namespace App\Http\Controllers;

use App\Example;
use App\House;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class HouseController extends Controller
{
    public function getAll($start, $end) {
        $printedResponseString = '';

        for ($j = $start; $j < $end; $j = $j + 500) {

       /* Получить 4000 домов */
       $houses = House::offset($j)->paginate(500);

       /* Включить цикл прохождения по домам */
       for($i = 0; $i < 500; $i++) {

           $singleHouse = $houses[$i];

           /* Номер дома для формрования адреса */
           $singleHouseNumber = $singleHouse->HOUSENUM;

           /* Выбор улицы, которая пренадлежит дому */
           $street = DB::table('address')->where('AOGUID', $singleHouse->AOGUID)->get();

           /* Наименование улицы для формирования адреса */
           $streetName = $street[0]->FORMALNAME;

           /* Выбор родительского элемента улицы (город/село/поселок) */
           $city = DB::table('address')->where('AOGUID', $street[0]->PARENTGUID)->get();
           $cityName = $city[0]->FORMALNAME;

               if ($cityName == "Астрахань") {
                   /* Полный адрес, если улица находится в городе "Астрахань" */
                   $fullAddress = $cityName.'+'.$streetName.'+'.$singleHouseNumber;

                   /* Запрос через Guzzle на Yandex */
                   $client = new Client();
                   $res = $client->request('GET', 'https://geocode-maps.yandex.ru/1.x/', [
                       'query' => [
                           'geocode' => $fullAddress,

                       ]
                   ]);

                   /* Получить xml данные о местоположении */
                   $responseAnswer = $res->getBody();

                   /* Конвертировать xml в массив php */
                   $xml = simplexml_load_string($responseAnswer);
                   $json = json_encode($xml);
                   $array = json_decode($json,TRUE);


                       $arrayFeatureMember = $array['GeoObjectCollection']['featureMember'];

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
                       $posPoint = 'POINT('.str_replace(" ", ",", $pos).')';
                       $lowerCornerPoint = 'POINT('.str_replace(" ", ",", $lowerCorner).')';
                       $upperCornerPoint = 'POINT('.str_replace(" ", ",", $upperCorner).')';

                       /* Получить id дома */
                       $singleHouseId = '\''.$singleHouse->HOUSEGUID.'\'';

                       /* Обновить данные о доме */
                       DB::statement('UPDATE "houses" set
                                    "POS" = '.$posPoint.',
                                    "LOWERCORNER" = '.$lowerCornerPoint.',
                                    "UPPERCORNER" = '.$upperCornerPoint.'
                                   where "HOUSEGUID" = '.$singleHouseId);

                       $printString = "Цикл: ".$j.", Номер: ".$i.", Инфо: ".$singleHouse->HOUSEGUID.", NewPos: ".$posPoint.", NewLowerCorner: ".$lowerCornerPoint." ,NewUpperCornerPoint: ".$upperCornerPoint."<br>";

                   $printedResponseString = $printedResponseString.$printString;
//                       echo "Цикл: ".$j.", Номер: ".$i.", Инфо: ".$singleHouse->HOUSEGUID.", NewPos: ".$posPoint.", NewLowerCorner: ".$lowerCornerPoint." ,NewUpperCornerPoint: ".$upperCornerPoint;
//                       echo "<br>";
                   }
           }
        }
        echo $printedResponseString;
    }

    /* Получить точки по домам в районах */
    public function getDistrictAddress() {
        $printedResponseString = '';

        $houses = House::whereNull('POS')->offset(800)->paginate(500);

        /* Включить цикл прохождения по домам */
        for($i = 0; $i < 500; $i++) {
            $singleHouse = $houses[$i];
            $singleHouseNumber = $singleHouse->HOUSENUM;

            /* Выбор улицы, которая пренадлежит дому */
            $street = DB::table('address')->where('AOGUID', $singleHouse->AOGUID)->get();

            /* Наименование улицы для формирования адреса */
            $streetName = $street[0]->FORMALNAME;

            /* Выбор родительского элемента улицы (город/село/поселок) */
            $city = DB::table('address')->where('AOGUID', $street[0]->PARENTGUID)->get();
            $cityName = $city[0]->FORMALNAME;

            /* Выбор родительского элемента - район */
            $district = DB::table('address')->where('AOGUID', $city[0]->PARENTGUID)->get();
            $districtName = $district[0]->FORMALNAME;

            $fullAddress = 'Астраханская+область+'.$districtName.'+'.$cityName.'+'.$streetName.'+'.$singleHouseNumber;

            /* Запрос через Guzzle на Yandex */
            $client = new Client();
            $res = $client->request('GET', 'https://geocode-maps.yandex.ru/1.x', [
                'query' => [
                    'geocode' => $fullAddress,
//                    'results' => 1
                ]
            ]);

            /* Получить xml данные о местоположении */
            $responseAnswer = $res->getBody();

            /* Конвертировать xml в массив php */
            $xml = simplexml_load_string($responseAnswer);
            $json = json_encode($xml);
            $array = json_decode($json,TRUE);
            /* По адресам yandex - для Астраханского адреса может быть как первый элемент, так и единственный элемент*/
            $arrayComponents = $array['GeoObjectCollection']['featureMember'];
//dd($array);
            if (isset($arrayComponents[0])) {
                $arrayComponnetsFirst = $arrayComponents[0];
            } else {
                $arrayComponnetsFirst  = $arrayComponents;
            }

            $components = $arrayComponnetsFirst['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['Component'];

            /* Посмотреть, есть ли номер дома по yandex API, если есть, то проставить */
            foreach($components as $value) {
                foreach($value as $key => $innerValue) {
                    if($innerValue == 'house') {

                        $arrayFeatureMember = $array['GeoObjectCollection']['featureMember'];

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
                        $posPoint = 'POINT('.str_replace(" ", ",", $pos).')';
                        $lowerCornerPoint = 'POINT('.str_replace(" ", ",", $lowerCorner).')';
                        $upperCornerPoint = 'POINT('.str_replace(" ", ",", $upperCorner).')';

                        /* Получить id дома */
                        $singleHouseId = '\''.$singleHouse->HOUSEGUID.'\'';

                        /* Обновить данные о доме */
                        DB::statement('UPDATE "houses" set
                                        "POS" = '.$posPoint.',
                                        "LOWERCORNER" = '.$lowerCornerPoint.',
                                        "UPPERCORNER" = '.$upperCornerPoint.'
                                       where "HOUSEGUID" = '.$singleHouseId);

                        $printString = "Номер: ".$i.", Инфо: ".$singleHouse->HOUSEGUID.", NewPos: ".$posPoint.", NewLowerCorner: ".$lowerCornerPoint." ,NewUpperCornerPoint: ".$upperCornerPoint."<br>";
                        $printedResponseString = $printedResponseString.$printString;
                    }
                }
            }
        }
        echo $printedResponseString;
    }

    /* Только для Астрахани */
    public function getOnlyForAstrakhan($start, $end) {
        $printedResponseString = '';

        $posType = 0;

        /* Пустить в цикл */
        for ($j = $start; $j < $end; $j = $j + 100) {

            /* Включить цикл прохождения по домам */
            $astraAddress = DB::select('select address."PARENTGUID", address."AOGUID", address."FORMALNAME", houses."HOUSEGUID", houses."HOUSENUM", houses."HOUSEID"
                            from houses inner join address on
                            (address."AOGUID" = houses."AOGUID")
                            where houses."AOGUID" in 
                            (select "AOGUID" from address where "PARENTGUID" = \'a101dd8b-3aee-4bda-9c61-9df106f145ff\') and houses."POS" IS NULL LIMIT 100 OFFSET '.$j.'');

            for ($i = 0; $i < 100; $i++) {
                /* CURL ошибка */
                try {

                    /* Полный адрес, если улица находится в городе "Астрахань" */
                    $fullAddress = 'Астрахань+' . $astraAddress[$i]->FORMALNAME . '+' . $astraAddress[$i]->HOUSENUM;

                    /* Запрос через Guzzle на Yandex */
                    $client = new Client();
                    $res = $client->request('GET', 'https://geocode-maps.yandex.ru/1.x/?geocode=' . $fullAddress . '&results=1&format=json');

                    $resJson = json_decode($res->getBody(), TRUE);

                    /* Ошибка здесь undefined offset[0]* Исправление if */
                    if(isset($resJson['response']['GeoObjectCollection']['featureMember'][0])) {
                        $array = $resJson['response']['GeoObjectCollection']['featureMember'][0];
                        $arrayComponents = $array['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['Components'];

                        /* Посмотреть, есть ли номер дома по yandex API, если есть, то проставить */
                        foreach ($arrayComponents as $value) {
                            foreach ($value as $key => $innerValue) {
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
                        $singleHouseId = '\'' . $astraAddress[$i]->HOUSEID . '\'';

                        /* Обновить данные о доме */
                        DB::statement('UPDATE "houses" set
                                                    "POS" = ' . $posPoint . ',
                                                    "LOWERCORNER" = ' . $lowerCornerPoint . ',
                                                    "UPPERCORNER" = ' . $upperCornerPoint . ',
                                                    "POSTYPE" = ' . $posType . '
                                                   where "HOUSEID" = ' . $singleHouseId);

                        $printString = "Номер: " . $i . "Инфо: " . $astraAddress[$i]->HOUSEID . ", PosType: " . $posType . ", NewPos: " . $posPoint . ", NewLowerCorner: " . $lowerCornerPoint . " ,NewUpperCornerPoint: " . $upperCornerPoint . "<br>";
                        $printedResponseString = $printedResponseString . $printString;
                    }
                } catch (Exception $e) {

                }
            }
        }

        echo $printedResponseString;
    }

    public function getPos() {
        $houses = House::paginate(100);
    }




}
