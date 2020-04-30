<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */

require_once 'qCal/Loader.php';
function qCal_Autoloader($name) {

    // Try to load only concerned class...
    if (strpos($name, 'qCal') === 0) {
        qCal_Loader::loadClass($name);
    }

}

spl_autoload_register("qCal_Autoloader");
