<?php

require_once "modules/SignedRecord/resources/signature-to-image/signature-to-image.php";
/**
 * Class SignedRecord_Field_Model
 */
class SignedRecord_Field_Model extends Vtiger_Field_Model
{
    /**
     * Function to check whether field is ajax editable'
     * @return <Boolean>
     */
    public function isAjaxEditable()
    {
        return false;
    }
    /**
     * Function to retieve display value for a value
     * @param <String> $value - value which need to be converted to display value
     * @return <String> - converted display value
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        if ($this->name == "signature" || $this->name == "secondary_signature") {
            if (strpos($value, "data:image/png;base64") !== false) {
                $img = $value;
            } else {
                $img = $this->sigJsonToImage($value);
            }
            return "<img src=\"" . $img . "\" style=\"height: 100px;\"/>";
        }
        if ($this->name == "filename" || $this->name == "secondary_filename") {
            return "<a href=\"index.php?module=SignedRecord&action=DownloadFile&record=" . $record . "\">" . basename($value) . "</a>";
        }
        if ($this->name == "cf_signature_time") {
            $dateTimeValue = new DateTimeField(date("Y-m-d") . " " . $value);
            return $dateTimeValue->getDisplayTime();
        }
        if ($this->name == "signedrecord_emails1") {
            $listEmail = array();
            if ($value != "") {
                $value = json_decode(html_entity_decode($value));
                foreach ($value as $key => $emails) {
                    foreach ($emails as $email) {
                        if ($email != "") {
                            $listEmail[] = $email;
                        }
                    }
                }
                return implode(", ", $listEmail);
            } else {
                return $value;
            }
        } else {
            return parent::getDisplayValue($value, $record, $recordInstance);
        }
    }
    /**
     * @link https://github.com/thomasjbradley/signature-to-image/
     * @link http://stackoverflow.com/questions/22266402/how-to-encode-an-image-resource-to-base64
     *
     * @param string $json
     * @return object
     */
    public function sigJsonToImage($json)
    {
        $json = json_decode(htmlspecialchars_decode($json));
        $img = sigJsonToImage($json, array("imageSize" => array(500, 180)));
        ob_start();
        imagepng($img);
        $contents = ob_get_contents();
        ob_end_clean();
        imagedestroy($img);
        $dataUri = "data:image/png;base64," . base64_encode($contents);
        return $dataUri;
    }
}

?>