<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuerryController extends Controller
{
    /* Выбрать все районы Астраханской области */
    public function chooseDistrict() {
        $allDistricts = DB::table('addrs')->where([
            'AOLEVEL' => 3,
            'ACTSTATUS' => 1
        ])->get();

        return $allDistricts;
    }

    /* Выбрать все города/села Района */
    public function chooseCity($districtId) {
        $allCities = DB::table('addrs')
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
            ->where('AOGUID', '=', $streetId)
            ->get();

        return $allBuildings;
    }

    public function updatePosition($first, $second) {


    }
}
