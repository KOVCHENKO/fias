<?php
/**
 * @brief - Обновить базу данных
 */
Route::get('/update_all', 'UpdateController@updateDatabase');

/**
 * @brief - Проставить адреса
 */
Route::get('/null', 'DistrictController@getAllPosNull');


/**
 * @brief - Группа маршрутов для выборки данных
 */
/* Выбрать районы
* Второй маршрут - выбрать район по id города
*/
Route::get('/choose_district/{parent_id}', 'QuerryController@getDistrictByCityId');
Route::get('/choose_district_by_parent', 'QuerryController@chooseDistrict');

/* Выбрать города
http://fiasadr/choose_city/46c0e38f-d339-4149-acfa-0d6ae968d2b6 - Черноярского района
Второй маршрут - выбрать все города во всей области
*/
Route::get('/choose_city/{district_id}', 'QuerryController@chooseCity');
Route::get('/choose_cities/', 'QuerryController@getAllCities');

/* Выбрать улицы
http://fiasadr/choose_street/a80776c8-5a17-42c9-b498-41c0a0aa84fd
*/
Route::get('/choose_street/{city_id}', 'QuerryController@chooseStreet');

/* Все здания на улице
http://fiasadr/choose_building/37934bab-6962-4c66-ac61-c9276f78dbcc
*/
Route::get('/choose_building/{street_id}', 'QuerryController@chooseBuilding');


/* Отправить заявку, если номера дома нет в списке ФИАС */
Route::post('/send_application', 'ApplicationController@sendApplication');


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/houses/{start}/{end}', 'HouseController@getAll');

Route::get('/pos', 'HouseController@getPos');

Route::get('/districts', 'HouseController@getDistrictAddress');

/* Выбрать все дома Астрахани */
Route::get('/astra/{start}/{end}', 'HouseController@getOnlyForAstrakhan');

/* Все дома Ахтубинского района */
Route::get('/ahtubinsk', 'AddressController@getAhtubinsk');

/* Все дома Камызякского района */
Route::get('/kamyzyak', 'KamyzyakController@getKamyzyak');

Route::get('/district_all/{districtId}/{districtOffName}/{cityKey}', 'DistrictController@getAll');


Route::get('/update', 'UpdateController@updateAddresses');

Route::get('/extract', 'UpdateController@getRar');

Route::get('/download','UpdateController@downloadFiasFile');

Route::get('/version', 'UpdateController@filesVersions');

Route::get('/update_houses', 'UpdateController@updateHouses');

Route::get('/delete', 'UpdateController@deleteAllInRarFolder');

