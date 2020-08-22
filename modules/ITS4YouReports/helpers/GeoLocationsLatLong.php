<?php

class ITS4YouReports_GeoLocationsLatLong_Helper {

    private static $db = null;
    private static $tableName = 'its4you_reports_geolocations';

    public $latitude = null;
    public $longitude = null;

    /**
     * ITS4YouReports_GeoLocationsLatLong_Helper constructor.
     */
    public function __construct() {
        self::$db = PearDatabase::getInstance();
    }

    /**
     * @param string $locationText
     *
     * @return ITS4YouReports_GeoLocationsLatLong_Helper|null
     */
    public static function getInstance($locationText = '') {
        $returnGeoLatLongObj = null;

        if (!empty($locationText)) {
            $getLatLongObj = new self();
            if (!empty($getLatLongObj->loadLatLong($locationText))) {
                $returnGeoLatLongObj = $getLatLongObj;
            }
        }

        return $returnGeoLatLongObj;
    }

    /**
     * @return string
     */
    private static function getSelectLatLongQuery() {
        return sprintf('SELECT latitude, longitude FROM %s WHERE location_text=?', self::$tableName);
    }

    /**
     * @return string
     */
    private static function getInsertLatLongQuery() {
        return sprintf('INSERT INTO %s (`location_text`, `latitude`, `longitude`) VALUES (?, ?, ?) 
                                 ON DUPLICATE KEY UPDATE location_text=?', self::$tableName);
    }

    /**
     * @param $locationText
     *
     * @return array
     */
    private function loadLatLong($locationText) {
        if (!empty($locationText)) {
            $result = self::$db->pquery(self::getSelectLatLongQuery(), [$locationText]);
            
            if ($result && self::$db->num_rows($result)) {
                $row = self::$db->fetchByAssoc($result, 0);
                $this->setLat($row['latitude']);
                $this->setLong($row['longitude']);
            }
        }

        return self::getLatLong();
    }

    /**
     * @param $locationText
     * @param $latitude
     * @param $longitude
     *
     * @return ITS4YouReports_GeoLocationsLatLong_Helper
     * @throws Exception
     */
    public static function saveLatLong($locationText, $latitude, $longitude) {
        $geoLatLongObj = new self();

        if (!empty($locationText)) {
            $geoLatLongObj->setLat($latitude);
            $geoLatLongObj->setLong($longitude);

            try {
                self::$db->pquery(self::getInsertLatLongQuery(), [$locationText, $latitude, $longitude, $locationText]);
            } catch (Exception $e) {
                throw new Exception('saveLatLong: ' . self::$db->convert2Sql(self::getInsertLatLongQuery(), [$locationText, $latitude, $longitude, $locationText]) . PHP_EOL . $e->getMessage());
            }
        }

        return $geoLatLongObj;
    }

    /**
     * @param null $latitude
     */
    public function setLat($latitude) {
        $this->latitude = $latitude;
    }

    /**
     * @param null $longitude
     */
    public function setLong($longitude) {
        $this->longitude = $longitude;
    }

    /**
     * @return array
     */
    public function getLatLong() {
        $return = [];

        if ('' !== (string) $this->latitude && '' !== (string) $this->longitude) {
			$return = [$this->latitude, $this->longitude];
		}

        return $return;
    }

}