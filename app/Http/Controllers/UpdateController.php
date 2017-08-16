<?php

namespace App\Http\Controllers;

use App\Add;
use App\Residence;

class UpdateController extends Controller
{
    /**
     * @brief Переменные для обновления
     * $updateVersion - версия для обновления
     * $deltaXML - файл для обновления в xml
     * $deltaDBF - файл для обновления в dbf
     * $addressFileName - путь к адресам (улицы, дома ...)
     */
    private $updateVersion;
    private $deltaXML;
    /* private $deltaDBF; deprecated - для получения всех версий */
    private $deltaDBF = 'http://fias.nalog.ru/Public/Downloads/Actual/fias_delta_dbf.rar';
    private $addressFileName;


    /* @brief Обновить базу данных */
    public function updateDatabase() {
        /* Поставить лимит выполнения скрипта - 10 часов */
        set_time_limit(360000);

        $this->lastFileVersion(); /* Получить информацию о последней версии */

        /* Проверка на текущую послежнюю версию обновлшения. Версия файла будет хранится отдельном файле. */
        /* TODO: Включить проверку на обновление всех строк. Удалять строки, которые уже были перенесены в БД */
        $currentVersion = file_get_contents('update_version_last.txt');
        if ($currentVersion != $this->updateVersion) {
            file_put_contents('update_version_last.txt', $this->updateVersion);
            $this->deleteAllInRarFolder();  /* Удалить все файлы в папке rarfiles */
            $this->updateAddresses();       /* Обновить адреса */
            $this->updateHouses();          /* Обновить дома */
        }
        echo "Update has been completed";
    }

    /* @brief Удалить все файлы в папке rarfiles */
    public function deleteAllInRarFolder() {
        $files = glob('rarfiles/*'); /* Получить все названия файлов */
        foreach($files as $file) {
            if(is_file($file))
                unlink($file); /* Удаление файлов */
        }
    }

    /* @brief Используется для получения rar файла и распаковки его в папку rarfiles */
    public function getRarDBF() {
        copy ($this->deltaDBF, 'rarfiles/copy_dbf.rar');

        $rar_file = rar_open('rarfiles/copy_dbf.rar');
        $list = rar_list($rar_file);
        foreach($list as $file) {
            $file->extract("rarfiles/"); // extract to the current dir
        }
        rar_close($rar_file);
    }

    /* @brief Выбор файла для парсинга (Адреса) */
    public function chooseAddressFile() {
        $glob = glob('rarfiles/*');
        foreach($glob as $file) {
            if(preg_match("/ADDROB30/", $file)) {
                $this->addressFileName = $file;
            }
        }
        return $this->addressFileName;
    }

    /* @brief Получить содержимое файла (Адреса) */
    public function updateAddresses() {
        $this->getRarDBF();                                 /* Скачать rar файл */
        $fileNameForParsing = $this->chooseAddressFile();   /* Выбор файла для парсинга */

        $db = dbase_open($fileNameForParsing, 0);
        $record_numbers = dbase_numrecords($db);
        for ($i = 1; $i <= $record_numbers; $i++) {

            $row = dbase_get_record_with_names($db, $i);
            $rowEncoded = $this->cp866ToUtf8($row); /* Поменять кодировку с cp866 на utf8*/

                    $oldAddress = Add::where('AOID', '=', $rowEncoded['AOID'])->first();

                    if(isset($oldAddress)) {
                        $oldAddress->delete();
                    }

                    $newAddress = new Add();
                    $newAddress->ACTSTATUS = $rowEncoded['ACTSTATUS'];
                    $newAddress->AOGUID = $rowEncoded['AOGUID'];
                    $newAddress->AOID = $rowEncoded['AOID'];
                    $newAddress->AOLEVEL = $rowEncoded['AOLEVEL'];
                    $newAddress->AREACODE = $rowEncoded['AREACODE'];
                    $newAddress->AUTOCODE = $rowEncoded['AUTOCODE'];
                    $newAddress->CENTSTATUS = $rowEncoded['CENTSTATUS'];
                    $newAddress->CITYCODE = $rowEncoded['CITYCODE'];
                    $newAddress->CODE = $rowEncoded['CODE'];
                    $newAddress->CURRSTATUS = $rowEncoded['CURRSTATUS'];
                    $newAddress->ENDDATE = $rowEncoded['ENDDATE'];
                    $newAddress->FORMALNAME = $rowEncoded['FORMALNAME'];
                    $rowEncoded['IFNSFL'] == '' ? $newAddress->IFNSFL = $rowEncoded['IFNSFL'] : $newAddress->IFNSFL = $rowEncoded['IFNSFL'] = 0;
                    $rowEncoded['IFNSUL'] == '' ? $newAddress->IFNSUL = $rowEncoded['IFNSUL'] : $newAddress->IFNSFL = $rowEncoded['IFNSUL'] = 0;

                    $newAddress->NEXTID = $rowEncoded['NEXTID'];
                    $newAddress->OFFNAME = $rowEncoded['OFFNAME'];
                    $newAddress->OKATO = $rowEncoded['OKATO'];
                    $newAddress->OKTMO = $rowEncoded['OKTMO'];
                    $newAddress->OPERSTATUS = $rowEncoded['OPERSTATUS'];
                    $newAddress->PARENTGUID = $rowEncoded['PARENTGUID'];
                    $newAddress->PLACECODE = $rowEncoded['PLACECODE'];
                    $newAddress->PLAINCODE = $rowEncoded['PLAINCODE'];
                    $rowEncoded['POSTALCODE'] == '' ? $newAddress->POSTALCODE = $rowEncoded['POSTALCODE'] : $newAddress->POSTALCODE = $rowEncoded['POSTALCODE'] = 0;
                    $newAddress->PREVID = $rowEncoded['PREVID'];
                    $newAddress->REGIONCODE = $rowEncoded['REGIONCODE'];
                    $newAddress->SHORTNAME = $rowEncoded['SHORTNAME'];
                    $newAddress->STREETCODE = $rowEncoded['STREETCODE'];
                    $rowEncoded['TERRIFNSUL'] == '' ? $newAddress->TERRIFNSUL = $rowEncoded['TERRIFNSFL'] : $newAddress->TERRIFNSUL = $rowEncoded['TERRIFNSUL'] = 0;
                    $rowEncoded['TERRIFNSFL'] == '' ? $newAddress->TERRIFNSFL = $rowEncoded['TERRIFNSFL'] : $newAddress->TERRIFNSFL = $rowEncoded['TERRIFNSFL'] = 0;
                    $newAddress->UPDATEDATE = $rowEncoded['UPDATEDATE'];
                    $newAddress->CTARCODE = $rowEncoded['CTARCODE'];
                    $newAddress->EXTRCODE = $rowEncoded['EXTRCODE'];
                    $newAddress->SEXTCODE = $rowEncoded['SEXTCODE'];
                    $newAddress->LIVESTATUS = $rowEncoded['LIVESTATUS'];
                    $newAddress->NORMDOC = $rowEncoded['NORMDOC'];
                    $newAddress->PLANCODE = $rowEncoded['PLANCODE'];
                    $rowEncoded['CADNUM'] == '' ? $newAddress->CADNUM = $rowEncoded['CADNUM'] : $newAddress->CADNUM = $rowEncoded['CADNUM'] = 0;
                    $newAddress->DIVTYPE = $rowEncoded['DIVTYPE'];
                    $newAddress->VERSION = $this->updateVersion;
                    $newAddress->SHORTCADNUM = '30';
                    $newAddress->save();
        }
        echo "Address update has been finished <br>";
    }

    /* @brief Выбор файла для парсинга (Дома) */
    public function chooseHousesFile() {
        $glob = glob('rarfiles/*');

        foreach($glob as $file) {
            if(preg_match("/HOUSE30/", $file)) {
                $this->addressFileName = $file;
            }
        }
        return $this->addressFileName;
    }


    /* @brief Получить содержимое файла (Дома) */
    public function updateHouses() {
        $this->getRarDBF();                               /* Скачать rar файл (DBF для домов) */
        $fileNameForParsing = $this->chooseHousesFile();    /* Выбор файла для парсинга */

        $db = dbase_open($fileNameForParsing, 0);
        $record_numbers = dbase_numrecords($db);
        for ($i = 1; $i <= $record_numbers; $i++) {

            $row = dbase_get_record_with_names($db, $i);
            $rowEncoded = $this->cp866ToUtf8($row); /* Поменять кодировку с cp866 на utf8*/

            /* Удалить дом, если совпадают ID */
            $oldHouse = Residence::where('HOUSEID', '=', $rowEncoded['HOUSEID'])->first();
            if(isset($oldHouse)) {
                $oldHouse->delete();
            }

            $newHouse = new Residence();

            $newHouse->AOGUID = $rowEncoded['AOGUID'];
            $newHouse->BUILDNUM = $rowEncoded['BUILDNUM'];
            $newHouse->ENDDATE = $rowEncoded['ENDDATE'];
            $newHouse->ESTSTATUS = $rowEncoded['ESTSTATUS'];
            $newHouse->HOUSEGUID = $rowEncoded['HOUSEGUID'];
            $newHouse->HOUSEID = $rowEncoded['HOUSEID'];
            $newHouse->HOUSENUM = $rowEncoded['HOUSENUM'];
            $newHouse->STATSTATUS = $rowEncoded['STATSTATUS'];
            $rowEncoded['IFNSFL'] == '' ? $newHouse->IFNSFL = $rowEncoded['IFNSFL'] : $newHouse->IFNSFL = $rowEncoded['IFNSFL'] = 0;
            $rowEncoded['IFNSUL'] == '' ? $newHouse->IFNSUL = $rowEncoded['IFNSUL'] : $newHouse->IFNSFL = $rowEncoded['IFNSUL'] = 0;

            $newHouse->OKATO = $rowEncoded['OKATO'];
            $newHouse->OKTMO = $rowEncoded['OKTMO'];
            $rowEncoded['POSTALCODE'] == '' ? $newHouse->POSTALCODE = $rowEncoded['POSTALCODE'] : $newHouse->POSTALCODE = $rowEncoded['POSTALCODE'] = 0;
            $newHouse->STARTDATE = $rowEncoded['STARTDATE'];
            $newHouse->STRUCNUM = $rowEncoded['STRUCNUM'];
            $newHouse->STRSTATUS = $rowEncoded['STRSTATUS'];
            $rowEncoded['TERRIFNSUL'] == '' ? $newHouse->TERRIFNSUL = $rowEncoded['TERRIFNSFL'] : $newHouse->TERRIFNSUL = $rowEncoded['TERRIFNSUL'] = 0;
            $rowEncoded['TERRIFNSFL'] == '' ? $newHouse->TERRIFNSFL = $rowEncoded['TERRIFNSFL'] : $newHouse->TERRIFNSFL = $rowEncoded['TERRIFNSFL'] = 0;
            $newHouse->UPDATEDATE = $rowEncoded['UPDATEDATE'];
            $newHouse->NORMDOC = $rowEncoded['NORMDOC'];
            $newHouse->COUNTER = $rowEncoded['COUNTER'];
            $rowEncoded['CADNUM'] == '' ? $newHouse->CADNUM = $rowEncoded['CADNUM'] : $newHouse->CADNUM = $rowEncoded['CADNUM'] = 0;
            $newHouse->DIVTYPE = $rowEncoded['DIVTYPE'];
            $newHouse->VERSION = $this->updateVersion;

            $newHouse->save();
        }
        echo "Houses update has been finished <br>";
    }

    /* @brief Конвертация из одной кодировки в другую */
    public function cp866ToUtf8($row) {
        $rowEncoded = array();
        foreach($row as $rowKey => $rowValue) {
            $encodedValue = iconv("cp866", "UTF-8", $rowValue);
            $rowEncoded[$rowKey] = $encodedValue;
        }

        return $rowEncoded;
    }

    /* @brief Используется для получения последней версии fias */
    public function lastFileVersion() {
        $lastVersion = file_get_contents('https://fias.nalog.ru/Public/Downloads/Actual/VerDate.txt');
        $this->updateVersion = $lastVersion;

        return $lastVersion;
    }

    /* @brief Используется для получения всех версий fias */
    public function filesVersions() {
        $fias_url     = 'http://fias.nalog.ru/WebServices/Public/DownloadService.asmx';
        $fias_methods = (object) [
            'all'  => 'GetAllDownloadFileInfo',
            'last' => 'GetLastDownloadFileInfo'
        ];

        $fias_method  = $fias_methods->all;
        $fias_request = `
            <?xml version="1.0" encoding="utf-8"?>
            <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
              <soap12:Body>
                <{$fias_method} xmlns="{$fias_url}" />
              </soap12:Body>
            </soap12:Envelope>
            XML
            `;

        $url = $fias_url;

        $curl = curl_init($url);

        $headers = [
            'Content-Type: text/xml; charset=utf-8',
            "SOAPAction: \"$fias_url/$fias_method\"",
            'Content-Length: ' . strlen($fias_request),
        ];
        curl_setopt_array( $curl,
            [
                CURLOPT_POST           => true,
                CURLOPT_HEADER         => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => $fias_request
            ] );

        $result = curl_exec($curl);
        curl_close($curl);

        $xmlUploaded = file_get_contents('versions.xml');
        /* Форматирование строки. Simple XML не понимает формат xml fias */
        $formattedResultBeginning = substr($xmlUploaded, 319);
        $stringLength = strlen($formattedResultBeginning);
        $formattedResultEnd = substr($formattedResultBeginning, 0, $stringLength - 61);

        /* Конвертирование из строки xml в строку массив php*/
        $xmlVersion = simplexml_load_string($formattedResultEnd);
        $jsonResultVersion = json_encode($xmlVersion, TRUE);
        $phpArrayResultVersion = json_decode($jsonResultVersion);

        $lastVersion = end($phpArrayResultVersion->DownloadFileInfo);

        $this->updateVersion = $lastVersion->VersionId;
        $this->deltaXML = $lastVersion->FiasDeltaXmlUrl;
        $this->deltaDBF = $lastVersion->FiasDeltaDbfUrl;

        echo "Versions has been acquired <br> $this->updateVersion";
    }
}
