<?php

class ITS4YouReports_Bing_Map {
    private static $db = null;

    private static $version = '1.0';
    private static $baseUrl = 'https://dev.virtualearth.net/REST/v1/Locations';
    private static $apiKey = null;

    private static $apiKeysTable = 'its4you_reports_maps_api_keys';

    /**
     * ITS4YouReports_Bing_Map constructor.
     */
    public function __construct() {
        self::$db = self::getDbInstance();
    }

    /**
     * @return PearDatabase|null
     */
    private static function getDbInstance() {
        if (!self::$db) {
            self::$db = PearDatabase::getInstance();
        }

        return self::$db;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function getApiKeyForJs() {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        self::$apiKey = ITS4YouReports_Bing_Map::getUserApiKey($currentUser->getId());
        if (empty(self::$apiKey)) {
            self::$apiKey = ITS4YouReports_Bing_Map::getDefaultApiKey();
        }

        if (empty(self::$apiKey)) {
            throw new Exception(vtranslate('LBL_EMPTY_API_KEY', 'ITS4YouReports'));
        }

        return self::getApiKey();
    }

    /**
     * @return string
     */
    private static function getApiKey() {
        return self::$apiKey;
    }

    /**
     * @return string
     */
    public static function getUserApiKeyType() {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        $result = self::getDbInstance()->pquery(sprintf('SELECT CASE 
                                                WHEN maps_api_key_value != "" 
                                                THEN "user" 
                                                ELSE "default" 
                                                END AS type 
                                                FROM %s 
                                                WHERE userid=?', self::$apiKeysTable), [$currentUser->getId()]);

        if ($result && self::getDbInstance()->num_rows($result)) {
            $row = self::getDbInstance()->fetchByAssoc($result, 0);
            $type = $row['type'];
        } else {
            $type = 'default';
        }

        return $type;
    }

    /**
     * @return string
     */
    public static function getDefaultApiKey() {
        $apiKey = '';

        $result = self::getDbInstance()->pquery(sprintf('SELECT maps_api_key_value AS api_key
                                                FROM %s 
                                                WHERE userid=?', self::$apiKeysTable), [0]);

        if ($result && self::getDbInstance()->num_rows($result)) {
            $row = self::getDbInstance()->fetchByAssoc($result, 0);
            $apiKey = $row['api_key'];
        }

        return $apiKey;
    }

    /**
     * @param $userId
     *
     * @return string
     */
    public static function getUserApiKey($userId) {
        $apiKey = '';

        if ($userId) {
            $result = self::getDbInstance()->pquery(sprintf('SELECT maps_api_key_value AS api_key
                                                    FROM %s 
                                                    WHERE userid=?', self::$apiKeysTable), [$userId]);

            if ($result && self::getDbInstance()->num_rows($result)) {
                $row = self::getDbInstance()->fetchByAssoc($result, 0);
                $apiKey = $row['api_key'];
            }
        }

        return $apiKey;
    }

    /**
     * @param $userId
     * @param $apiKey
     *
     * @return mixed
     */
    public static function saveUserApiKey($userId, $apiKey) {
        $useType = ($userId ? 'user' : 'default');

        if ('default' === $useType) {
            $currentUser = Users_Record_Model::getCurrentUserModel();
            self::getDbInstance()->pquery(sprintf('DELETE FROM %s WHERE userid=?', self::$apiKeysTable), [$currentUser->getId()]);
        }

        $query = sprintf('REPLACE INTO %s (userid, maps_api_use_type, maps_api_key_value)
                                    VALUES (?, ?, ?)', self::$apiKeysTable);

        return self::getDbInstance()->pquery($query, [$userId, $useType, $apiKey]);
    }

    /**
     * @return string
     */
    private static function getBaseUrl() {
        return self::$baseUrl;
    }

    /**
     * @return ITS4YouReports_Bing_Map
     */
    public static function getInstance() {
        return new self();
    }

    /**
     * @param $addressData
     *
     * @return string
     */
    private static function getAddressText($addressData) {
        global $default_charset;
		return html_entity_decode(implode(',', $addressData), ENT_QUOTES, $default_charset);
    }

    /**
     * @param $addressData
     *
     * @return string
     */
    private static function getLocationQuery($addressData) {
        return str_replace(' ', '%20', self::getAddressText($addressData));
    }

    /**
     * @param $addressData
     *
     * @return string
     * @throws Exception
     */
    private static function getFindUrl($addressData) {
        return self::getBaseUrl() . '/' . self::getLocationQuery($addressData) . '?key=' . self::getApiKeyForJs() . '&output=xml';
    }
    
    /**
     * @param $addresses
     *
     * @return array
     * @throws Exception
     */
    public static function handleGeoLocationsByAddress($addresses) {
		//self::testExample();exit;
		global $default_charset;
        $notificationCss = 'modules/ITS4YouReports/map_notifications/notifications.css';
        $notificationJs = 'modules/ITS4YouReports/map_notifications/notifications.js';

        $addressesLatLong = [];
        ob_clean();
		echo '
        <!DOCTYPE html>
		<meta charset="utf-8" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		';

        if (file_exists($notificationCss)) {
            echo '<link rel="stylesheet" href="', $notificationCss, '">';
        }

        if (file_exists($notificationJs)) {
            echo '<script src="', $notificationJs, '"></script>';
        }

        $warningMessages = [];

        foreach ($addresses as $addressKey => $rowData) {
            $pinText = '';
            $addressText = '';
            $latitude = '';
            $longitude = '';

            if (!empty($rowData)) {
                $addressData = $rowData[0];
                $addressText = self::getAddressText($addressData);

                if (!empty($addressData)) {
                    $pinText = $rowData[1];
                    $pinDescription = $rowData[2];

                    $geoLatLongObj = ITS4YouReports_GeoLocationsLatLong_Helper::getInstance($addressText);
                    if (empty($geoLatLongObj)) {
						// Construct the final Locations API URI - first real address check
                        //$addressData = array_reverse($addressData);
                        $findUrl = self::getFindUrl($addressData);
                        // get the response from the Locations API and store it in a string
                        $output = file_get_contents($findUrl);

                        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
                        $status = (int) $match[1];
                        
                        if(200 === $status) {
                            if (!empty($output)) {
                                $latitude = '';
                                $longitude = '';
                                // create an XML element based on the XML string
                                $response = new SimpleXMLElement($output);
                                // Extract data (e.g. latitude and longitude) from the results
                                if (!empty($response->ResourceSets->ResourceSet->Resources->Location)) {
                                    $latitude = $response->ResourceSets->ResourceSet->Resources->Location->Point->Latitude->__toString();
                                    $longitude = $response->ResourceSets->ResourceSet->Resources->Location->Point->Longitude->__toString();
                                }
                                $geoLatLongObj = ITS4YouReports_GeoLocationsLatLong_Helper::saveLatLong($addressText, $latitude, $longitude);
                            }
                        }  else {
							$warningText = $addressText . ' > ' . self::http_response_code($status) . '\n';
							if (!in_array($warningText, $warningMessages)) {
								$warningMessages[] = $warningText;
							}
							$geoLatLongObj = ITS4YouReports_GeoLocationsLatLong_Helper::saveLatLong($addressText, 0, 0);
						}
                        sleep(0.01); // to prevent blocking by call limits !!!
                    }
                    if (!empty($geoLatLongObj)) {
                        $getLatLong = $geoLatLongObj->getLatLong();
                        $latitude = $getLatLong[0];
                        $longitude = $getLatLong[1];
                    }
                    if (empty($latitude) || empty($latitude)) {
						$warningText = sprintf('empty latitude,longitude (%s,%s): %s ', $latitude, $longitude, $addressText) . '\n';
						if (!in_array($warningText, $warningMessages)) {
							$warningMessages[] = $warningText;
						}
					}
                }
            }

            if (!empty($latitude) && !empty($longitude)) {
                $addressesLatLong['found'][$addressKey] = [$latitude, $longitude, $addressText, $pinText, $pinDescription];
            } else {
                $addressesLatLong['not_found'][$addressKey] = [$latitude, $longitude, $addressText, $pinText, $pinDescription];
            }
        }
        if (!empty($warningMessages)) {
        	echo '<script type="text/javascript">';
        	if (file_exists($notificationCss) && file_exists($notificationJs)) {
                echo 'window.onload = function(){
                    const mapNotification = window.createNotification({
                      // close on click
                      closeOnClick: false,
                      // displays close button
                      displayCloseButton: true,
                      positionClass: "nfc-bottom-left",
                      // callback
                      onclick: false,
                      // timeout in milliseconds
                      showDuration: 100000,
                      // success, info, warning, error, and none
                      theme: "warning"
                    });
                    mapNotification({ 
                      title: "Title",
                      message: "' . implode($warningMessages) . '"
                    });
                };';
            } else {
                echo 'alert("',implode($warningMessages),'")';
            }
            echo '</script>';
        }

        return $addressesLatLong;
    }

    /**
     * @param array $reportData
     */
    public static function resultsToTitle(array $reportData) {
        if (!empty($reportData)) {
            $found = (int) count($reportData['found']);
            $notFound = (int) count($reportData['not_found']);
            $ofRecords = $found + $notFound;
            echo '<script type="text/javascript">';
            echo 'console.log("ITS4YouReports locations found ' . $found . '");';
            echo 'console.log("ITS4YouReports locations not found ' . $notFound . '");';
            echo 'console.log("ITS4YouReports locations of total ' . $ofRecords . '");';
            echo '</script>';
            echo '<script type="text/javascript">window.document.title = "', sprintf('%s %s %s %s', vtranslate('LBL_DISPLAYING_RESULTS', 'ITS4YouReports'), $found, vtranslate('LBL_OF'), $ofRecords), '";</script>';
        } else {
            echo vtranslate('LBL_NO_RECORDS_FOUND');
        }
    }

    public static function isReadyForMaps($reportId) {
        $isReady = false;

        if ($reportId) {
            $recordModel = ITS4YouReports_Record_Model::getInstanceById($reportId);

            if (!empty($recordModel->report->reportinformations['maps'])) {
                foreach (ITS4YouReports_BingMaps_View::$addressColumns as $bingColumnName) {
                    if (!empty($recordModel->report->reportinformations['maps'][$bingColumnName])) {
                        $isReady = true;
                        break;
                    }
                }
            }

        }

        return $isReady;
    }

    /**
     * @param $reportId
     *
     * @return string|null
     */
    public static function getGenerateMapUrl($reportId) {
        $url = null;

        if ($reportId) {
            $url = sprintf('index.php?module=ITS4YouReports&view=BingMaps&record=%s', $reportId);
        }

        return $url;
    }

    /**
     * @param null $code
     *
     * @return int|mixed|null
     */
    public static function http_response_code($code = null) {
        switch ($code) {
            case 100:
                $text = 'Continue';
                break;
            case 101:
                $text = 'Switching Protocols';
                break;
            case 200:
                $text = 'OK';
                break;
            case 201:
                $text = 'Created';
                break;
            case 202:
                $text = 'Accepted';
                break;
            case 203:
                $text = 'Non-Authoritative Information';
                break;
            case 204:
                $text = 'No Content';
                break;
            case 205:
                $text = 'Reset Content';
                break;
            case 206:
                $text = 'Partial Content';
                break;
            case 300:
                $text = 'Multiple Choices';
                break;
            case 301:
                $text = 'Moved Permanently';
                break;
            case 302:
                $text = 'Moved Temporarily';
                break;
            case 303:
                $text = 'See Other';
                break;
            case 304:
                $text = 'Not Modified';
                break;
            case 305:
                $text = 'Use Proxy';
                break;
            case 400:
                $text = 'Bad Request';
                break;
            case 401:
                $text = 'Unauthorized';
                break;
            case 402:
                $text = 'Payment Required';
                break;
            case 403:
                $text = 'Forbidden';
                break;
            case 404:
                $text = 'Not Found';
                break;
            case 405:
                $text = 'Method Not Allowed';
                break;
            case 406:
                $text = 'Not Acceptable';
                break;
            case 407:
                $text = 'Proxy Authentication Required';
                break;
            case 408:
                $text = 'Request Time-out';
                break;
            case 409:
                $text = 'Conflict';
                break;
            case 410:
                $text = 'Gone';
                break;
            case 411:
                $text = 'Length Required';
                break;
            case 412:
                $text = 'Precondition Failed';
                break;
            case 413:
                $text = 'Request Entity Too Large';
                break;
            case 414:
                $text = 'Request-URI Too Large';
                break;
            case 415:
                $text = 'Unsupported Media Type';
                break;
            case 500:
                $text = 'Internal Server Error';
                break;
            case 501:
                $text = 'Not Implemented';
                break;
            case 502:
                $text = 'Bad Gateway';
                break;
            case 503:
                $text = 'Service Unavailable';
                break;
            case 504:
                $text = 'Gateway Time-out';
                break;
            case 505:
                $text = 'HTTP Version not supported';
                break;
            default:
                $text = 'Unknown http status code "' . htmlentities($code) . '"';
                break;
        }

        return $text;
    }
    
    private static function testExample() {
		ob_clean();
		//error_reporting(E_ERROR);ini_set('display_errors', 1);
		// URL of Bing Maps REST Services Locations API   
		$baseURL = "http://dev.virtualearth.net/REST/v1/Locations";  
		  
		// Create variables for search parameters (encode all spaces by specifying '%20' in the URI)  
		$key = self::getApiKeyForJs();  
		$addressData = [];
		$addressData[] = str_ireplace(" ","%20", 'Presov');  
		$addressData[] = str_ireplace(" ","%20", 'Slovenska 69');  
		/*
		$addressData[] = str_ireplace(" ","%20", 'Slovenska 69');  
		$addressData[] = str_ireplace(" ","%20", 'Slovensko');  
		$addressData[] = str_ireplace(" ","%20", 'Presov');  
		$addressData[] = str_ireplace(" ","%20", '08001');
		*/
		/*
		$addressData[] = 'Poříční%203010';  
		$addressData[] = 'Česká%20Lípa';
		$addressData[] = '47001';
		*/
		  
		// Compose URI for Locations API request  
		$findUrl = self::getFindUrl($addressData);
		
		// get the response from the Locations API and store it in a string
		$output = file_get_contents($findUrl);

        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        $status = (int) $match[1];
		  
        if(200 === $status) {
            if (!empty($output)) {
                $latitude = '';
                $longitude = '';
                // create an XML element based on the XML string
                $response = new SimpleXMLElement($output);
                // Extract data (e.g. latitude and longitude) from the results
                if (!empty($response->ResourceSets->ResourceSet->Resources->Location)) {
                    $latitude = $response->ResourceSets->ResourceSet->Resources->Location->Point->Latitude->__toString();
                    $longitude = $response->ResourceSets->ResourceSet->Resources->Location->Point->Longitude->__toString();
                }
                echo '<pre>';print_r([self::getAddressText($addressData), $latitude, $longitude]);echo '</pre>';
            }
        } else {
            throw new Exception(self::http_response_code($status));
        }
	}

}