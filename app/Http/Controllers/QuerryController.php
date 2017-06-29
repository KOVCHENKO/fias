<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuerryController extends Controller
{

    /* Выбрать все города в общем по всей области */
    public function getAllCities() {
        return DB::select('select "FORMALNAME", "PARENTGUID", "AOGUID", "SHORTNAME" from addrs 
                            where ("AOLEVEL" = 4 or "AOLEVEL" = 6) and "ACTSTATUS" = 1');
    }

    /* Выбрать район по id города */
    public function getDistrictByCityId($parentId) {
        $districtName = DB::table('addrs')
            ->select('AOGUID', 'FORMALNAME', 'SHORTCADNUM')
            ->where([
                'AOGUID' => $parentId,
                'ACTSTATUS' => 1
            ])->first();

        return $districtName;
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

        return $allBuildings;
    }

    /* Обновить информацию об адресе вручную */
    public function updatePosition($second, $first) {


    }
}
