<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuerryController extends Controller
{

    /* Выбрать все города в общем по всей области - в скобках - название области */
    public function getAllCities() {
        $allCitiesArray = array();

        $allCities = DB::select('select b."FORMALNAME" as "district", a."FORMALNAME" as "city", a."PARENTGUID", a."AOGUID", a."SHORTNAME" 
                                from addrs as a
                                JOIN addrs as b
                                ON (a."PARENTGUID" = b."AOGUID")
                                where (a."AOLEVEL" = 4 or a."AOLEVEL" = 6) and a."ACTSTATUS" = 1');

        foreach($allCities as $key => $value) {
            array_push($allCitiesArray, ([
                'FORMALNAME' => $value->city.' ('.$value->district.')',
                'PARENTGUID' => $value->PARENTGUID,
                'AOGUID' => $value->AOGUID,
                'SHORTNAME' => $value->SHORTNAME,
            ]));
        }

        return $allCitiesArray;
    }

    /* Выбрать район по id города */
    public function getDistrictByCityId($parentId) {
        $districtName = DB::table('addrs')
            ->select('AOGUID', 'FORMALNAME', 'SHORTCADNUM', 'AOLEVEL')
            ->where([
                'AOGUID' => $parentId,
                'ACTSTATUS' => 1
            ])->get();

        if ($districtName[0]->AOLEVEL == 1) {
            return '[]';
        } else {
            return $districtName;
        }
    }

    /* Выбрать все районы Астраханской области */
    public function chooseDistrict() {
        $allDistricts = DB::table('addrs')
            ->select('AOGUID', 'FORMALNAME', 'SHORTCADNUM')
            ->where([
            'AOLEVEL' => 3,
            'ACTSTATUS' => 1
        ])->get();

        return $allDistricts;
    }

    /* Выбрать все города/села Района */
    public function chooseCity($districtId) {
        $allCities = DB::table('addrs')
            ->select('AOGUID', 'FORMALNAME', 'SHORTCADNUM')
            ->where([
                'PARENTGUID' => $districtId,
                'ACTSTATUS' => 1
            ])
            ->orderBy('CENTSTATUS', 'desc')
            ->get();

        return $allCities;
    }

    /* Выбрать все улицы */
    public function chooseStreet($cityId) {
        $allStreets = DB::table('addrs')
            ->select('AOGUID', 'FORMALNAME', 'SHORTNAME', 'SHORTCADNUM')
            ->where([
                'PARENTGUID' => $cityId,
                'ACTSTATUS' => 1
                ])
            ->get();

        return $allStreets;
    }

    /* Выбрать все дома на улице */
    public function chooseBuilding($streetId) {
        $allBuildings = DB::table('hous')
            ->select('HOUSENUM', 'STRUCNUM', 'STRSTATUS', 'HOUSEGUID', 'BUILDNUM', 'POSTALCODE', 'SHORTCADNUM')
            ->distinct()
            ->where('AOGUID', '=', $streetId)
            ->get();

        $allHousesInStreet = array();

        foreach($allBuildings as $key => $value) {
            switch($value->STRSTATUS) {
                case 0: /* Признаки отсутствуют */
                    if (!isset($value->BUILDNUM)) {
                        $formalName = $value->HOUSENUM;
                    } else {
                        $formalName = $value->HOUSENUM.', корп. '.$value->BUILDNUM;
                    }
                    break;
                case 1: /* Признак строение "стр" */
                    if (!isset($value->BUILDNUM)) {
                        $formalName = $value->HOUSENUM.', стр. '.$value->STRUCNUM;
                    } else {
                        $formalName = $value->HOUSENUM.', корп. '.$value->BUILDNUM.', стр. '.$value->STRUCNUM;
                    }
                    break;
                case 2: /* Признак сооружение "соор" */
                    if (!isset($value->BUILDNUM)) {
                        $formalName = $value->HOUSENUM.', соор. '.$value->STRUCNUM;
                    } else {
                        $formalName = $value->HOUSENUM.', корп. '.$value->BUILDNUM.', соор. '.$value->STRUCNUM;
                    }
                    break;
                case 3:
                    if (!isset($value->BUILDNUM)) {
                        $formalName = $value->HOUSENUM.', литер. '.$value->STRUCNUM;
                    } else {
                        $formalName = $value->HOUSENUM.', корп. '.$value->BUILDNUM.', Литер. '.$value->STRUCNUM;
                    }
                    break;
            }

            $singleHouse = ([
                'FORMALNAME' => $formalName,
                'POSTALCODE' => $value->POSTALCODE,
                'SHORTCADNUM' => $value->SHORTCADNUM,
            ]);

            array_push($allHousesInStreet, $singleHouse);
        }

        return $allHousesInStreet;
    }

    /* Обновить информацию об адресе вручную */
    public function updatePosition($second, $first) {


    }
}
