<?php

include_once "vtlib/Vtiger/Mailer.php";
class MultipleSMTP_Mailer_Model extends Vtiger_Mailer
{
    public static function getInstance()
    {
        return new self();
    }
    /**
     * Initialize this instance
     * @access private
     */
    public function initialize()
    {
        $this->IsSMTP();
        global $adb;
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $result = $adb->pquery("SELECT * FROM vte_multiple_smtp WHERE userid=?", array($currentUserModel->getId()));
        if ($_REQUEST["from_serveremailid"]) {
            $result = $adb->pquery("SELECT * FROM vte_multiple_smtp WHERE id=?", array($_REQUEST["from_serveremailid"]));
        }
        if ($adb->num_rows($result)) {
            $this->Host = $adb->query_result($result, 0, "server");
            $this->Username = decode_html($adb->query_result($result, 0, "server_username"));
            $this->Password = decode_html($adb->query_result($result, 0, "server_password"));
            $this->SMTPAuth = $adb->query_result($result, 0, "smtp_auth");
            $hostinfo = explode("://", $this->Host);
            $smtpsecure = $hostinfo[0];
            if ($smtpsecure == "tls") {
                $this->SMTPSecure = $smtpsecure;
                $this->Host = $hostinfo[1];
            }
            if (empty($this->SMTPAuth)) {
                $this->SMTPAuth = false;
            }
            $this->ConfigSenderInfo($adb->query_result($result, 0, "from_email_field"));
            $this->_serverConfigured = true;
        }
    }
    public function Send($sync = false, $linktoid = false)
    {
        parent::Send(true, false);
        if ($this->SendFolder) {
            $this->copyToFolder($this);
        }
        return true;
    }
    public function copyToFolder($mail)
    {
        $c = ini_get("default_socket_timeout");
        ini_set("default_socket_timeout", 5);
        imap_timeout(IMAP_OPENTIMEOUT, 5);
        imap_timeout(IMAP_READTIMEOUT, 5);
        imap_timeout(IMAP_WRITETIMEOUT, 5);
        imap_timeout(IMAP_CLOSETIMEOUT, 5);
        $header = $mail->MIMEHeader;
        $header = str_replace($mail->From, "", $header);
        $header = explode(" ", (string) $header);
        $to = "";
        for ($i = 0; $i < count($header); $i++) {
            if (strpos($header[$i], "@")) {
                $to = $header[$i];
                break;
            }
        }
        $body = $mail->Body;
        $body = explode(">", $body);
        $body = str_replace($body[0], "", $mail->Body);
        $body = trim($body, ">");
        $url = str_replace("//", "", (string) $mail->Host);
        $url = explode(":", (string) $url);
        $host = "{" . (string) $url[1] . ":993/imap/ssl" . "/novalidate-cert}";
        $imapStream = imap_open($host, $mail->Username, $mail->Password);
        $SentBox = "{" . (string) $url[1] . ":993/imap/ssl" . "/novalidate-cert}" . "Sent";
        $imap_appen = imap_append($imapStream, (string) $SentBox, "From: " . $mail->From . "\r\n" . "To: " . $to . "\r\n" . "Subject: " . $mail->Subject . "\r\n" . "\r\n" . (string) $body . "\r\n");
        imap_close($imapStream);
        ini_set("default_socket_timeout", $c);
        if ($imap_appen) {
            return true;
        }
    }
    /**
     * Function returns error from phpmailer
     * @return <String>
     */
    public function getError()
    {
        return $this->ErrorInfo;
    }
    public function convertToValidURL($htmlContent)
    {
        if (!$this->dom) {
            $this->dom = new DOMDocument();
            @$this->dom->loadHTML($htmlContent);
        }
        $anchorElements = $this->dom->getElementsByTagName("a");
        $urls = array();
        foreach ($anchorElements as $anchorElement) {
            $url = $anchorElement->getAttribute("href");
            if (!empty($url) && !preg_match("~^(?:f|ht)tps?://~i", $url) && strpos("\$", $url[0]) !== 0 && strpos($url, "mailto:") !== 0 && strpos($url, "tel:") !== 0 && $url[0] !== "#" && !preg_match("/news:\\/\\//i", $url)) {
                $url = "http://" . $url;
                $urls[$anchorElement->getAttribute("href")] = $url;
                $htmlContent = $this->replaceURLWithValidURLInContent($htmlContent, $anchorElement->getAttribute("href"), $url);
            }
        }
        return $htmlContent;
    }
    public function replaceURLWithValidURLInContent($htmlContent, $searchURL, $replaceWithURL)
    {
        $search = "\"" . $searchURL . "\"";
        $toReplace = "\"" . $replaceWithURL . "\"";
        $pos = strpos($htmlContent, $search);
        if ($pos != false) {
            $replacedContent = substr_replace($htmlContent, $toReplace, $pos) . substr($htmlContent, $pos + strlen($search));
            return $replacedContent;
        }
        return $htmlContent;
    }
}

?>