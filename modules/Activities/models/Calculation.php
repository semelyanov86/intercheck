<?php

class Activities_Calculation_Model
{
    private $contactModel;
    private $activityModel;
    public function __construct(Vtiger_Record_Model $contactModel, Vtiger_Record_Model $activityModel)
    {
        $this->activityModel = $activityModel;
        $this->contactModel = $contactModel;
    }

    public function calcPeriod(string $period = 'i') : int
    {
        global $log;
        $endDateStr = $this->activityModel->get('timestamp');
        if (!$endDateStr) {
            return 0;
        }
        try {
            $endDate = new DateTime($endDateStr);
        } catch (Exception $e) {
            $log->error('Calculation Model: ' . $e->getMessage());
            return 0;
        }
        $startDate = $this->getStartDate();
        if ($startDate) {
            $diff = $endDate->diff($startDate);
            return (int) $diff->$period;
        } else {
            return 0;
        }
    }

    private function getStartDate() : ?DateTime
    {
        global $log;
        global $adb;
        $fieldDate = $this->contactModel->get('cf_platform_last_login');
        $fieldTime = $this->contactModel->get('cf_last_login_time');
        if ($fieldDate && $fieldTime) {
            try {
                $startDate = new DateTime($fieldDate . ' ' . $fieldTime);
            } catch (Exception $e) {
                $log->error('Calculation Model getStartDate: ' . $e->getMessage());
                return null;
            }
            return $startDate;
        } else {
            $query = "SELECT `timestamp` FROM vtiger_activities INNER JOIN vtiger_crmentity ON vtiger_activities.activitiesid = vtiger_crmentity.crmid WHERE deleted = ? AND vtiger_activities.cf_contacts_id = ? ORDER BY `timestamp` DESC LIMIT 1";
            $res = $adb->pquery($query, array(0, $this->contactModel->getId()));
            if($res){
                $rowCount = $adb->num_rows($res);
                if($rowCount > 0){
                    $row = $adb->query_result_rowdata($res,0);
                    $time = $row['timestamp'];
                    try {
                        $startDate = new DateTime($time);
                    } catch (Exception $e) {
                        $log->error('Calculation Model getStartDate: ' . $e->getMessage());
                        return null;
                    }
                    return $startDate;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
    }
}
