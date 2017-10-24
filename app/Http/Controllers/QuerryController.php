<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuerryController extends Controller
{
    protected $cityId;

    /* Выбрать все города в общем по всей области - в скобках - название области */
    public function getAllCities() {
        $allCitiesArray = array();

        $allCities = DB::select('select b."FORMALNAME" as "district", a."FORMALNAME" as "city", a."PARENTGUID", a."AOGUID", a."SHORTNAME" 
                                from addrs as a
                                JOIN addrs as b
                                ON (a."PARENTGUID" = b."AOGUID")
                                where (a."AOLEVEL" = 4 or a."AOLEVEL" = 6) and a."ACTSTATUS" = 1 and b."ACTSTATUS" = 1');

        foreach($allCities as $key => $value) {
            array_push($allCitiesArray, ([
                'FORMALNAME' => trim($value->city).' ('.trim($value->district).')',
                'PARENTGUID' => $value->PARENTGUID,
                'AOGUID' => $value->AOGUID,
                'SHORTNAME' => trim($value->SHORTNAME),
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

        foreach($allStreets as $singleStreet) {
            $singleStreet->FORMALNAME = trim($singleStreet->SHORTNAME).'. '.trim($singleStreet->FORMALNAME);
        }

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
                    if (!isset($value->HOUSENUM) and !isset($value->BUILDNUM)) {
                        $formalName = 'стр. '.$value->STRUCNUM;
                    } elseif(!isset($value->BUILDNUM)) {
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

    /* Получить последний id дома */
    public function getLastHOUSEGUID() {
        $houses = DB::table('hous')
            ->select('HOUSEGUID', 'HOUSEID')
            ->where('VERSION', '=', '001')->get();

        $houseGUIDs = array();

        foreach ($houses as $house) {
            array_push($houseGUIDs, substr($house->HOUSEGUID, -12));
        }

        $max = '1'.max($houseGUIDs);
        $maxPlus = (float)$max + 1;
        $maxString = substr((string)$maxPlus, 1);
        return $maxString;
    }

    /* Получить последний id улицы */
    public function getLastAOGUIDofStreet() {
        $streets = DB::table('addrs')
            ->select('AOGUID', 'AOID')
            ->where('VERSION', '=', '001')->get();

        $streetGUIDs = array();

        foreach($streets as $street) {
            array_push($streetGUIDs, substr($street->AOGUID, -12));
        }

        $max = '1'.max($streetGUIDs);
        $maxPlus = (float)$max + 1;
        $maxString = substr((string)$maxPlus, 1);

        return $maxString;
    }

    /* Определить кадастровый номер района */
    public function defineShortCadNum($districtId, $cityId) {
        $shortCadnum = '';
        $this->cityId = $cityId;

        switch ($districtId) {
            case('7e684a70-a23a-41b9-9440-302712c3e41d'):
                $shortCadnum = '30:01';
                break;
            case('3543d36d-4bf6-4caa-a267-b2bad69ccd6c'):
                $shortCadnum = '30:02';
                break;
            case('67788aa7-bf40-4dba-9574-9b271ccb845c'):
                $shortCadnum = '30:03';
                break;
            case('6d400dc9-cf9a-4dfd-920d-b83261345fd2'):
                $shortCadnum = '30:04';
                break;
            case('201c5a5d-5af2-47bb-b538-c42ffd5927dc'):
                $shortCadnum = '30:05';
                break;
            case('dbb24e53-47a5-4a43-aa2e-bdfee433ab00'):
                $shortCadnum = '30:06';
                break;
            case('04b18b87-2fb9-49eb-bee4-c660f18f7ea4'):
                $shortCadnum = '30:07';
                break;
            case('2b714aee-b462-4243-9b3d-6581b44202da'):
                $shortCadnum = '30:08';
                break;
            case('a0b67c5c-0250-47cf-94a5-4c2ca29fe183'):
                $shortCadnum = '30:09';
                break;
            case('bc36238f-e341-41b3-81aa-700c30845de8'):
                $shortCadnum = '30:10';
                break;
            case('46c0e38f-d339-4149-acfa-0d6ae968d2b6'):
                $shortCadnum = '30:11';
                break;
            case('83009239-25cb-4561-af8e-7ee111b1cb73'):
                if($this->cityId == 'a101dd8b-3aee-4bda-9c61-9df106f145ff') {
                    $shortCadnum = '30:12';
                    break;
                } elseif($this->cityId == '54ecd5a8-83d9-4a85-ae2c-6fe6976ab716') {
                    $shortCadnum = '30:13';
                    break;
                } else {
                    $shortCadnum = '30:12';
                    break;
                }
                break;
        }

        return $shortCadnum;
    }
}
