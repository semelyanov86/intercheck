<?php

require_once "data/CRMEntity.php";
require_once "data/Tracker.php";
require_once "vtlib/Vtiger/Module.php";
class PlatformIntegration extends CRMEntity
{
    /**
     * Invoked when special actions are performed on the module.
     * @param String Module name
     * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
     */
    public function vtlib_handler($modulename, $event_type)
    {
        if ($event_type == "module.postinstall") {
            $this->addWidgetTo();
            self::createScheduler();
            $this->createHandle();            
            $this->initData();
            $this->initDefaultMappingFields();
        } else {
            if ($event_type == "module.disabled") {
                $this->removeWidgetTo();
                self::deactiveScheduler();
                $this->removeHandle();
            } else {
                if ($event_type == "module.enabled") {
                    $this->addWidgetTo();
                    self::createScheduler();
                    $this->createHandle();
                } else {
                    if ($event_type == "module.preuninstall") {
                        $this->removeWidgetTo();
                        self::deleteScheduler();
                        $this->removeHandle();                        
                    } else {
                        if ($event_type != "module.preupdate") {
                            if ($event_type == "module.postupdate") {
                                $this->removeWidgetTo();
                                $this->addWidgetTo();
                                self::deleteScheduler();
                                self::createScheduler();
                                $this->removeHandle();
                                $this->createHandle();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Add widget to other module.
     * @param unknown_type $moduleNames
     * @return unknown_type
     */
    private function addWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        global $root_directory;
        $widgetType = "HEADERSCRIPT";
        $widgetName = "PlatformIntegrationJs";
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
        } else {
            $template_folder = "layouts/v7";
        }
        $link = $template_folder . "/modules/PlatformIntegration/resources/PlatformIntegration.js";
        $widgetTypeCSS = "HEADERCSS";
        $widgetNameCSS = "PlatformIntegrationCSS";
        $linkCSS = $template_folder . "/modules/PlatformIntegration/css/PlatformIntegration_icon.css";
        include_once "vtlib/Vtiger/Module.php";
        $moduleNames = array("PlatformIntegration");
        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->addLink($widgetTypeCSS, $widgetNameCSS, $linkCSS);
            }
        }
        $sql = "SELECT * FROM vtiger_settings_field WHERE `name`=?";
        $res = $adb->pquery($sql, array("PlatformIntegration"));
        if ($adb->num_rows($res) == 0) {
            $max_id = $adb->getUniqueID("vtiger_settings_field");
            $adb->pquery("INSERT INTO `vtiger_settings_field` (`fieldid`, `blockid`, `name`, `description`, `linkto`, `sequence`) VALUES (?, ?, ?, ?, ?, ?)", array($max_id, "4", "PlatformIntegration", "Settings area for PlatformIntegration", "index.php?module=PlatformIntegration&parent=Settings&view=Settings", $max_id));
        }
    }
    private function removeWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        $widgetType = "HEADERSCRIPT";
        $widgetName = "PlatformIntegrationJs";
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
            $vtVersion = "vt6";
            $linkVT6 = $template_folder . "/modules/PlatformIntegration/resources/PlatformIntegration.js";
        } else {
            $template_folder = "layouts/v7";
            $vtVersion = "vt7";
        }
        $link = $template_folder . "/modules/PlatformIntegration/resources/PlatformIntegration.js";
        $widgetTypeCSS = "HEADERCSS";
        $widgetNameCSS = "PlatformIntegrationCSS";
        $linkCSS = $template_folder . "/modules/PlatformIntegration/css/PlatformIntegration_icon.css";
        include_once "vtlib/Vtiger/Module.php";
        $moduleNames = array("PlatformIntegration");
        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->deleteLink($widgetTypeCSS, $widgetNameCSS, $linkCSS);
                if ($vtVersion == "vt6") {
                }
            }
        }
        $adb->pquery("DELETE FROM vtiger_settings_field WHERE `name` = ?", array("PlatformIntegration"));
    }
    private function initData()
    {
        global $adb;
        $sql = "SELECT * FROM platformintegration_api LIMIT 1";
        $res = $adb->pquery($sql, array());
        if ($adb->num_rows($res) == 0) {
            $adb->pquery("INSERT INTO platformintegration_api(sync_picklist, latest_update) VALUES(?, ?)", array(1, date("YmdHis")));
        }
        $this->initDataForPlatformModules();
        $this->initDataForPlatformModulesFields();
        $this->initDataForPicklistFields();
        $this->createAllCustomFields();
        $this->createPlatformGroup();
    }
    public function initDataForPlatformModules()
    {
        global $adb;
        $adb->pquery("TRUNCATE TABLE platformintegration_modules");
        $from_date = date("Y-m-d");
        $params = array(array("Company", "Customer", "Accounts", "LBL_PLATFORMINTEGRATION_TAB_CUSTOMER", 1, 1, "", "", "", 1, 1, "VT2Platform,2VT", "1", $from_date, "LBL_TOOLTIP_INFO"), array("Customer", "Customer", "Contacts", "LBL_PLATFORMINTEGRATION_TAB_CUSTOMER", 1, 2, "", "", "", 1, 1, "VT2Platform,Platform2VT", "1", $from_date, "LBL_TOOLTIP_INFO"), array("Product", "Item", "Products", "LBL_PLATFORMINTEGRATION_TAB_PRODUCT_SERVICE", 2, 1, "[\"Name != 'Discount' AND Type='Inventory'\",\"Name != 'Discount' AND Type='NonInventory'\"]", "{\"Type\":\"NonInventory\"}", "", 1, 1, "VT2Platform,Platform2VT", "", "", ""), array("Service", "Item", "Services", "LBL_PLATFORMINTEGRATION_TAB_PRODUCT_SERVICE", 2, 2, "[\"Name != 'Discount' AND Type='Service'\"]", "{\"Type\":\"Service\"}", "", 1, 1, "VT2Platform,Platform2VT", "", "", ""), array("Invoice", "Invoice", "Invoice", "LBL_PLATFORMINTEGRATION_TAB_INVOICE", 3, 1, "", "", "", 1, "", "VT2Platform,Platform2VT", "1", $from_date, "LBL_TOOLTIP_INFO"), array("Payment", "Payment", "VTEPayments", "LBL_PLATFORMINTEGRATION_TAB_PAYMENTS", 3, 1, "", "", "", 0, "", "Platform2VT", "1", $from_date, "LBL_TOOLTIP_INFO_PAYMENTS"), array("Category", "Item", "", "", NULL, NULL, "[\"Type='Category'\"]", "{\"Type\":\"Category\"}", "Name", 0, 1, "", "", "", ""), array("Account", "Account", "", "", NULL, NULL, "", "{\"AccountType\":\"Expense\",\"AccountSubType\":\"Utilities\"}", "Name", 0, 1, "", "", "", ""), array("PaymentMethod", "PaymentMethod", "", "", NULL, NULL, "", "{\"Type\":\"NON_CREDIT_CARD\"}", "Name", 0, 1, "", "", "", ""), array("Term", "Term", "", "", NULL, NULL, "", "{\"Type\":\"STANDARD\"}", "Name", 0, 1, "", "", "", ""), array("ExpenseAccount", "Account", "", "", NULL, NULL, "[\"AccountType='Cost of Goods Sold' AND AccountSubType='SuppliesMaterialsCogs'\"]", "{\"AccountType\":\"Cost of Goods Sold\",\"AccountSubType\":\"SuppliesMaterialsCogs\"}", "Name", 0, 1, "", "", "", ""), array("InventoryAssetAccount", "Account", "", "", NULL, NULL, "[\"AccountType='Other Current Asset' AND AccountSubType='Inventory'\"]", "{\"AccountType\":\"Other Current Asset\",\"AccountSubType\":\"Inventory\"}", "Name", 0, 1, "", "", "", ""), array("DepositToAccount", "Account", "", "", NULL, NULL, "[\"AccountType='Other Current Asset'\", \"AccountType='Bank'\"]", "{\"AccountType\":\"Other Current Asset\",\"AccountSubType\":\"Other Current Assets\"}", "Name", 0, 1, "", "", "", ""));
        $sql = "INSERT INTO platformintegration_modules(platform_module, platform_module_table, vt_module" . ", tab, tab_seq, seq_in_tab, conditions, default_value, representation_field" . ", has_custom_fields, has_active_field, allow_sync, sync_scope, has_from_date, from_date, tooltip) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?)";
        foreach ($params as $param) {
            $adb->pquery($sql, $param);
        }
    }
    public function initDataForPlatformModulesFields()
    {
        global $adb;
        $adb->pquery("TRUNCATE TABLE platformintegration_modules_fields");
        if (true) {
            $paramsCustomer = array(array("Customer", "Balance", "LBL_CUSTOMER_OPENNING_BALANCE", 1, 0, "", "number", 0, 0), array("Customer", "BillAddr.City", "LBL_CUSTOMER_BILLING_CITY", 1, 0, "", "text", 0, 255), array("Customer", "BillAddr.Country", "LBL_CUSTOMER_BILLING_COUNTRY", 1, 0, "", "text", 0, 255), array("Customer", "BillAddr.CountrySubDivisionCode", "LBL_CUSTOMER_BILLING_STATE", 1, 0, "", "text", 0, 255), array("Customer", "BillAddr.Line", "LBL_CUSTOMER_BILLING_ADDRESS", 1, 0, "", "text", 0, 2000), array("Customer", "BillAddr.PostalCode", "LBL_CUSTOMER_BILLING_POSTAL_CODE", 1, 0, "", "text", 0, 30), array("Customer", "CompanyName", "LBL_CUSTOMER_COMPANY", 1, 0, "", "text", 1, 100), array("Customer", "DisplayName", "LBL_CUSTOMER_DISPLAY_NAME_AS", 1, 0, "", "", 1, 500), array("Customer", "FamilyName", "LBL_CUSTOMER_LAST_NAME", 1, 0, "", "text", 0, 100), array("Customer", "Fax.FreeFormNumber", "LBL_CUSTOMER_FAX", 1, 0, "", "text", 0, 30), array("Customer", "GivenName", "LBL_CUSTOMER_FIRST_NAME", 1, 0, "", "text", 0, 100), array("Customer", "MiddleName", "LBL_CUSTOMER_MIDDLE_NAME", 1, 0, "", "text", 0, 100), array("Customer", "Mobile.FreeFormNumber", "LBL_CUSTOMER_MOBILE", 1, 0, "", "text", 0, 30), array("Customer", "Notes", "LBL_CUSTOMER_NOTES", 1, 0, "", "text", 0, 4000), array("Customer", "PaymentMethodRef", "LBL_CUSTOMER_PREFERRED_PAYMENT_METHOD", 1, 1, "PaymentMethod", "", 0, 0), array("Customer", "PreferredDeliveryMethod", "LBL_CUSTOMER_PREFERRED_DELIVERY_METHOD", 1, 1, "DeliveryMethod", "", 0, 0), array("Customer", "PrimaryEmailAddr.Address", "LBL_CUSTOMER_EMAIL", 1, 0, "", "email", 0, 100), array("Customer", "PrimaryPhone.FreeFormNumber", "LBL_CUSTOMER_PHONE", 1, 0, "", "text", 0, 30), array("Customer", "PrintOnCheckName", "LBL_CUSTOMER_PRINT_ON_CHECK_AS", 1, 0, "", "text", 0, 110), array("Customer", "ResaleNum", "LBL_CUSTOMER_EXEMPTION_DETAILS", 1, 0, "", "number", 0, 0), array("Customer", "SalesTermRef", "LBL_CUSTOMER_TERMS", 1, 1, "Term", "", 0, 0), array("Customer", "ShipAddr.City", "LBL_CUSTOMER_SHIPPING_CITY", 1, 0, "", "text", 0, 255), array("Customer", "ShipAddr.Country", "LBL_CUSTOMER_SHIPPING_COUNTRY", 1, 0, "", "text", 0, 255), array("Customer", "ShipAddr.CountrySubDivisionCode", "LBL_CUSTOMER_SHIPPING_STATE", 1, 0, "", "text", 0, 255), array("Customer", "ShipAddr.Line", "LBL_CUSTOMER_SHIPPING_ADDRESS", 1, 0, "", "text", 0, 2000), array("Customer", "ShipAddr.PostalCode", "LBL_CUSTOMER_SHIPPING_POSTAL_CODE", 1, 0, "", "text", 0, 30), array("Customer", "Suffix", "LBL_CUSTOMER_SUFFIX", 1, 0, "", "text", 0, 16), array("Customer", "Title", "LBL_CUSTOMER_TITLE", 1, 0, "", "text", 0, 16), array("Customer", "WebAddr.URI", "LBL_CUSTOMER_WEBSITE", 1, 0, "", "text", 0, 1000));
        }
        if (true) {
            $paramsCompany = array(array("Company", "Balance", "LBL_CUSTOMER_OPENNING_BALANCE", 1, 0, "", "number", 0, 0), array("Company", "BillAddr.City", "LBL_CUSTOMER_BILLING_CITY", 1, 0, "", "text", 0, 255), array("Company", "BillAddr.Country", "LBL_CUSTOMER_BILLING_COUNTRY", 1, 0, "", "text", 0, 255), array("Company", "BillAddr.CountrySubDivisionCode", "LBL_CUSTOMER_BILLING_STATE", 1, 0, "", "text", 0, 255), array("Company", "BillAddr.Line", "LBL_CUSTOMER_BILLING_ADDRESS", 1, 0, "", "text", 0, 2000), array("Company", "BillAddr.PostalCode", "LBL_CUSTOMER_BILLING_POSTAL_CODE", 1, 0, "", "text", 0, 30), array("Company", "CompanyName", "LBL_CUSTOMER_COMPANY", 1, 0, "", "text", 1, 100), array("Company", "DisplayName", "LBL_CUSTOMER_DISPLAY_NAME_AS", 1, 0, "", "text", 1, 500), array("Company", "FamilyName", "LBL_CUSTOMER_LAST_NAME", 1, 0, "", "text", 0, 100), array("Company", "Fax.FreeFormNumber", "LBL_CUSTOMER_FAX", 1, 0, "", "text", 0, 30), array("Company", "GivenName", "LBL_CUSTOMER_FIRST_NAME", 1, 0, "", "text", 0, 100), array("Company", "MiddleName", "LBL_CUSTOMER_MIDDLE_NAME", 1, 0, "", "text", 0, 100), array("Company", "Mobile.FreeFormNumber", "LBL_CUSTOMER_MOBILE", 1, 0, "", "text", 0, 30), array("Company", "Notes", "LBL_CUSTOMER_NOTES", 1, 0, "", "text", 0, 4000), array("Company", "PaymentMethodRef", "LBL_CUSTOMER_PREFERRED_PAYMENT_METHOD", 1, 1, "PaymentMethod", "", 0, 0), array("Company", "PreferredDeliveryMethod", "LBL_CUSTOMER_PREFERRED_DELIVERY_METHOD", 1, 1, "DeliveryMethod", "", 0, 0), array("Company", "PrimaryEmailAddr.Address", "LBL_CUSTOMER_EMAIL", 1, 0, "", "email", 0, 100), array("Company", "PrimaryPhone.FreeFormNumber", "LBL_CUSTOMER_PHONE", 1, 0, "", "text", 0, 30), array("Company", "PrintOnCheckName", "LBL_CUSTOMER_PRINT_ON_CHECK_AS", 1, 0, "", "text", 0, 110), array("Company", "ResaleNum", "LBL_CUSTOMER_EXEMPTION_DETAILS", 1, 0, "", "number", 0, 0), array("Company", "SalesTermRef", "LBL_CUSTOMER_TERMS", 1, 1, "Term", "", 0, 0), array("Company", "ShipAddr.City", "LBL_CUSTOMER_SHIPPING_CITY", 1, 0, "", "text", 0, 255), array("Company", "ShipAddr.Country", "LBL_CUSTOMER_SHIPPING_COUNTRY", 1, 0, "", "text", 0, 255), array("Company", "ShipAddr.CountrySubDivisionCode", "LBL_CUSTOMER_SHIPPING_STATE", 1, 0, "", "text", 0, 255), array("Company", "ShipAddr.Line", "LBL_CUSTOMER_SHIPPING_ADDRESS", 1, 0, "", "text", 0, 2000), array("Company", "ShipAddr.PostalCode", "LBL_CUSTOMER_SHIPPING_POSTAL_CODE", 1, 0, "", "text", 0, 30), array("Company", "Suffix", "LBL_CUSTOMER_SUFFIX", 1, 0, "", "text", 0, 16), array("Company", "Title", "LBL_CUSTOMER_TITLE", 1, 0, "", "text", 0, 16), array("Company", "WebAddr.URI", "LBL_CUSTOMER_WEBSITE", 1, 0, "", "text", 0, 1000));
        }
        if (true) {
            $paramsProduct = array(array("Product", "Active", "LBL_ITEM_ACTIVE", 1, 0, "", "boolean", 1, 0), array("Product", "AssetAccountRef", "LBL_ITEM_INVENTORY_ASSET_ACCOUNT", 1, 1, "InventoryAssetAccount", "", 1, 0), array("Product", "Description", "LBL_ITEM_SALES_INFORMATION", 1, 0, "", "text", 0, 4000), array("Product", "ExpenseAccountRef", "LBL_ITEM_EXPENSE_ACCOUNT", 1, 1, "Account", "", 1, 0), array("Product", "IncomeAccountRef", "LBL_ITEM_INCOME_ACCOUNT", 1, 1, "Account", "", 1, 0), array("Product", "InvStartDate", "LBL_ITEM_INVSTARTDATE", 1, 0, "", "date", 0, 0), array("Product", "Name", "LBL_ITEM_NAME", 1, 0, "", "text", 0, 100), array("Product", "ParentRef", "LBL_ITEM_CATEGORY", 1, 1, "Category", "", 0, 0), array("Product", "PurchaseCost", "LBL_ITEM_COST", 1, 0, "", "number", 0, 0), array("Product", "PurchaseDesc", "LBL_ITEM_PURCHASING_INFORMATION", 1, 0, "", "text", 0, 1000), array("Product", "QtyOnHand", "LBL_ITEM_QTYONHAND", 1, 0, "", "number", 0, 0), array("Product", "ReorderPoint", "LBL_ITEM_REORDER_POINT", 1, 0, "", "number", 0, 0), array("Product", "Sku", "LBL_ITEM_SKU", 1, 0, "", "text", 0, 100), array("Product", "Taxable", "LBL_ITEM_TAXABLE", 1, 0, "", "boolean", 1, 0), array("Product", "Type", "LBL_ITEM_TYPE", 1, 1, "", "", 1, 0), array("Product", "UnitPrice", "LBL_ITEM_SALES_PRICE_RATE", 1, 0, "", "number", 0, 0));
        }
        if (true) {
            $paramsService = array(array("Service", "Active", "LBL_ITEM_ACTIVE", 1, 0, "", "boolean", 1, 0), array("Service", "Description", "LBL_ITEM_SALES_INFORMATION", 1, 0, "", "text", 0, 4000), array("Service", "ExpenseAccountRef", "LBL_ITEM_EXPENSE_ACCOUNT", 1, 1, "Account", "", 1, 0), array("Service", "IncomeAccountRef", "LBL_ITEM_INCOME_ACCOUNT", 1, 1, "Account", "", 1, 0), array("Service", "Name", "LBL_ITEM_NAME", 1, 0, "", "text", 0, 100), array("Service", "ParentRef", "LBL_ITEM_CATEGORY", 1, 1, "Category", "", 0, 0), array("Service", "PurchaseCost", "LBL_ITEM_COST", 1, 0, "", "number", 0, 0), array("Service", "PurchaseDesc", "LBL_ITEM_PURCHASING_INFORMATION", 1, 0, "", "text", 0, 1000), array("Service", "Sku", "LBL_ITEM_SKU", 1, 0, "", "text", 0, 100), array("Service", "Taxable", "LBL_ITEM_TAXABLE", 1, 0, "", "boolean", 1, 0), array("Service", "Type", "LBL_ITEM_TYPE", 1, 1, "", "", 1, 0), array("Service", "UnitPrice", "LBL_ITEM_SALES_PRICE_RATE", 1, 0, "", "number", 0, 0));
        }
        if (true) {
            $paramsInvoice = array(array("Invoice", "Balance", "LBL_INVOICE_BALANCE", 1, 0, "", "", 1, 0), array("Invoice", "BillAddr.City", "LBL_INVOICE_BILLADDR_CITY", 1, 0, "", "text", 0, 255), array("Invoice", "BillAddr.Country", "LBL_INVOICE_BILLADDR_COUNTRY", 1, 0, "", "text", 0, 255), array("Invoice", "BillAddr.CountrySubDivisionCode", "LBL_INVOICE_BILLADDR_STATE", 1, 0, "", "text", 0, 255), array("Invoice", "BillAddr.Line", "LBL_INVOICE_BILLADDR_LINE", 1, 0, "", "text", 0, 2000), array("Invoice", "BillAddr.PostalCode", "LBL_INVOICE_BILLADDR_POSTALCODE", 1, 0, "", "text", 0, 30), array("Invoice", "BillEmail.Address", "LBL_INVOICE_BILLEMAIL_ADDRESS", 1, 0, "", "email", 1, 100), array("Invoice", "CustomerMemo", "LBL_INVOICE_CUSTOMERMEMO", 1, 0, "", "text", 0, 1000), array("Invoice", "CustomerRef", "LBL_INVOICE_CUSTOMERREF", 1, 0, "Company", "reference", 1, 0), array("Invoice", "CustomField.DefinitionId1", "LBL_INVOICE_CUSTOMFIELD_DEFINITIONID1", 1, 0, "", "CustomField", 0, 31), array("Invoice", "CustomField.DefinitionId2", "LBL_INVOICE_CUSTOMFIELD_DEFINITIONID2", 1, 0, "", "CustomField", 0, 31), array("Invoice", "CustomField.DefinitionId3", "LBL_INVOICE_CUSTOMFIELD_DEFINITIONID3", 1, 0, "", "CustomField", 0, 31), array("Invoice", "Deposit", "LBL_INVOICE_DEPOSIT", 1, 0, "", "", 1, 0), array("Invoice", "DiscountLineDetailAmount", "LBL_INVOICE_DISCOUNTLINEDETAIL_AMOUNT", 1, 0, "", "", 1, 0), array("Invoice", "DiscountLineDetailPercent", "LBL_INVOICE_DISCOUNTLINEDETAIL_PERCENT", 1, 0, "", "", 1, 0), array("Invoice", "DocNumber", "LBL_INVOICE_DOCNUMBER", 1, 0, "", "text", 1, 0), array("Invoice", "DueDate", "LBL_INVOICE_DUEDATE", 1, 0, "", "date", 0, 0), array("Invoice", "InvoiceStatus", "LBL_INVOICE_STATUS", 1, 0, "", "", 1, 0), array("Invoice", "PrivateNote", "LBL_INVOICE_PRIVATENOTE", 1, 0, "", "text", 0, 4000), array("Invoice", "SalesTermRef", "LBL_INVOICE_SALESTERMREF", 1, 1, "Term", "", 0, 0), array("Invoice", "ShipAddr.City", "LBL_INVOICE_SHIPADDR_CITY", 1, 0, "", "text", 0, 255), array("Invoice", "ShipAddr.Country", "LBL_INVOICE_SHIPADDR_COUNTRY", 1, 0, "", "text", 0, 255), array("Invoice", "ShipAddr.CountrySubDivisionCode", "LBL_INVOICE_SHIPADDR_STATE", 1, 0, "", "text", 0, 255), array("Invoice", "ShipAddr.Line", "LBL_INVOICE_SHIPADDR_LINE", 1, 0, "", "text", 0, 2000), array("Invoice", "ShipAddr.PostalCode", "LBL_INVOICE_SHIPADDR_POSTALCODE", 1, 0, "", "text", 0, 30), array("Invoice", "ShipDate", "LBL_INVOICE_SHIPDATE", 1, 0, "", "date", 0, 0), array("Invoice", "ShipMethodRef", "LBL_INVOICE_SHIPMETHODREF", 1, 0, "", "text", 0, 31), array("Invoice", "ShippingItem", "LBL_INVOICE_SHIPPING_ITEM_ID", 1, 0, "", "", 1, 0), array("Invoice", "SubTotalLineDetail", "LBL_INVOICE_SUBTOTALLINEDETAIL", 1, 0, "", "", 1, 0), array("Invoice", "TotalAmt", "LBL_INVOICE_TOTALAMT", 1, 0, "", "", 1, 0), array("Invoice", "TrackingNum", "LBL_INVOICE_TRACKINGNUM", 1, 0, "", "text", 0, 31), array("Invoice", "TxnDate", "LBL_INVOICE_TXNDATE", 1, 0, "", "date", 0, 0), array("Invoice", "TxnTaxDetail", "LBL_INVOICE_TXNTAXDETAIL", 1, 0, "", "", 1, 0));
        }
        if (true) {
            $paramsPayment = array(array("Payment", "LinkedTxn.TxnId", "LBL_PAYMENT_LINKEDTXN", 1, 0, "Invoice", "reference", 1, 0), array("Payment", "TxnDate", "LBL_PAYMENT_TXNDATE", 1, 0, "", "date", 1, 0), array("Payment", "PaymentMethodRef", "LBL_PAYMENT_PREFERRED_PAYMENT_METHOD", 1, 1, "PaymentMethod", "", 1, 0), array("Payment", "PaymentRefNum", "LBL_PAYMENT_PAYMENTREFNUM", 1, 0, "", "text", 1, 21), array("Payment", "DepositToAccountRef", "LBL_PAYMENT_DEPOSITTOACCOUNTREF", 1, 0, "DepositToAccount", "", 1, 0), array("Payment", "PaymentAmount", "LBL_PAYMENT_AMOUNT", 1, 0, "", "", 1, 0));
        }
        $params = array_merge($paramsCustomer, $paramsCompany, $paramsProduct, $paramsService, $paramsInvoice, $paramsPayment);
        foreach ($params as $param) {
            $sql = "INSERT INTO platformintegration_modules_fields(platform_module, platform_field, platform_field_label," . " is_active, is_picklist, module_ref, data_type, non_editable, max_len) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $adb->pquery($sql, $param);
        }
    }
    public function initDefaultMappingFields()
    {
        global $adb;
        global $vtiger_current_version;
        $adb->pquery("TRUNCATE TABLE platformintegration_mapping_fields");
        if (true) {
            $paramsCustomer = array();
        }
        if (true) {
            $paramsCompany = array(array("Company", "CompanyName", "Accounts", "accountname", 1), array("Company", "DisplayName", "Accounts", "accountname", 1), array("Company", "PrimaryEmailAddr.Address", "Accounts", "email1", 1), array("Company", "PrimaryPhone.FreeFormNumber", "Accounts", "phone", 1), array("Company", "Fax.FreeFormNumber", "Accounts", "fax", 1), array("Company", "BillAddr.Line", "Accounts", "bill_street", 1), array("Company", "BillAddr.City", "Accounts", "bill_city", 1), array("Company", "BillAddr.CountrySubDivisionCode", "Accounts", "bill_state", 1), array("Company", "BillAddr.PostalCode", "Accounts", "bill_code", 1), array("Company", "BillAddr.Country", "Accounts", "bill_country", 1), array("Company", "ShipAddr.Line", "Accounts", "ship_street", 1), array("Company", "ShipAddr.City", "Accounts", "ship_city", 1), array("Company", "ShipAddr.CountrySubDivisionCode", "Accounts", "ship_state", 1), array("Company", "ShipAddr.PostalCode", "Accounts", "ship_code", 1), array("Company", "ShipAddr.Country", "Accounts", "ship_country", 1), array("Company", "Notes", "Accounts", "description", 1));
        }
        if (true) {
            $paramsProduct = array(array("Product", "Name", "Products", "productname", 1), array("Product", "Active", "Products", "discontinued", 1), array("Product", "Sku", "Products", "productcode", 1), array("Product", "ParentRef", "Products", "productcategory", 1), array("Product", "AssetAccountRef", "Products", "cf_inventory_asset_account", 1), array("Product", "Description", "Products", "description", 1), array("Product", "UnitPrice", "Products", "unit_price", 1), array("Product", "IncomeAccountRef", "Products", "cf_income_account_pro", 1), array("Product", "ExpenseAccountRef", "Products", "cf_expense_account_pro", 1), array("Product", "Type", "Products", "cf_inventory_type", 1), array("Product", "InvStartDate", "Products", "sales_start_date", 1), array("Product", "Taxable", "Products", "cf_is_taxable", 1));
            if (!version_compare($vtiger_current_version, "7.0.0", "<")) {
                $paramsProduct[] = array("Product", "PurchaseCost", "Products", "purchase_cost", 1);
            }
        }
        if (true) {
            $paramsService = array(array("Service", "ParentRef", "Services", "servicecategory", 1), array("Service", "Name", "Services", "servicename", 1), array("Service", "Active", "Services", "discontinued", 1), array("Service", "Description", "Services", "description", 1), array("Service", "UnitPrice", "Services", "unit_price", 1), array("Service", "IncomeAccountRef", "Services", "cf_income_account_ser", 1), array("Service", "ExpenseAccountRef", "Services", "cf_expense_account_ser", 1), array("Service", "Taxable", "Services", "cf_is_taxable", 1));
            if (!version_compare($vtiger_current_version, "7.0.0", "<")) {
                $paramsService[] = array("Service", "PurchaseCost", "Services", "purchase_cost", 1);
            }
        }
        if (true) {
            $paramsInvoice = array(array("Invoice", "CustomerRef", "Invoice", "account_id", 1), array("Invoice", "BillEmail.Address", "Invoice", "EmailFromCustomer", 1), array("Invoice", "DocNumber", "Invoice", "cf_platform_invoice_no", 1), array("Invoice", "DiscountLineDetailPercent", "Invoice", "hdnDiscountPercent", 1), array("Invoice", "DiscountLineDetailAmount", "Invoice", "hdnDiscountAmount", 1), array("Invoice", "ShippingItem", "Invoice", "hdnS_H_Amount", 1), array("Invoice", "TxnTaxDetail", "Invoice", "TxnTaxDetail", 1), array("Invoice", "TotalAmt", "Invoice", "hdnGrandTotal", 1), array("Invoice", "Deposit", "Invoice", "received", 1), array("Invoice", "Balance", "Invoice", "balance", 1), array("Invoice", "BillAddr.Line", "Invoice", "bill_street", 1), array("Invoice", "BillAddr.City", "Invoice", "bill_city", 1), array("Invoice", "BillAddr.CountrySubDivisionCode", "Invoice", "bill_state", 1), array("Invoice", "BillAddr.PostalCode", "Invoice", "bill_code", 1), array("Invoice", "BillAddr.Country", "Invoice", "bill_country", 1), array("Invoice", "ShipAddr.Line", "Invoice", "ship_street", 1), array("Invoice", "ShipAddr.City", "Invoice", "ship_city", 1), array("Invoice", "ShipAddr.CountrySubDivisionCode", "Invoice", "ship_state", 1), array("Invoice", "ShipAddr.PostalCode", "Invoice", "ship_code", 1), array("Invoice", "ShipAddr.Country", "Invoice", "ship_country", 1), array("Invoice", "TxnDate", "Invoice", "invoicedate", 1), array("Invoice", "DueDate", "Invoice", "duedate", 1), array("Invoice", "SubTotalLineDetail", "Invoice", "hdnSubTotal", 1), array("Invoice", "InvoiceStatus", "Invoice", "cf_platform_status_inv", 1));
        }
        if (true) {
            $paramPayment = array(array("Payment", "LinkedTxn.TxnId", "VTEPayments", "invoice", 1), array("Payment", "TxnDate", "VTEPayments", "date", 1), array("Payment", "PaymentMethodRef", "VTEPayments", "payment_type", 1), array("Payment", "PaymentRefNum", "VTEPayments", "reference", 1), array("Payment", "DepositToAccountRef", "VTEPayments", "description", 1), array("Payment", "PaymentAmount", "VTEPayments", "amount_paid", 1));
        }
        $params = array_merge($paramsCustomer, $paramsCompany, $paramsProduct, $paramsService, $paramsInvoice, $paramPayment);
        foreach ($params as $param) {
            $sql = "INSERT INTO platformintegration_mapping_fields(platform_module, platform_field, vt_module," . " vt_field, is_active) VALUES(?, ?, ?, ?, ?)";
            $adb->pquery($sql, $param);
        }
    }
    public function initDataForPicklistFields()
    {
        global $adb;
        if (true) {
            $params = array(array("", "", "DeliveryMethod", "", "Print", "Print later"), array("", "", "DeliveryMethod", "", "Email", "Send later"), array("", "", "DeliveryMethod", "", "None", "None"), array("Product", "Type", "", "", "Inventory", "Inventory"), array("Product", "Type", "", "", "NonInventory", "NonInventory"), array("Service", "Type", "", "", "Service", "Service"));
            foreach ($params as $param) {
                $sql = "SELECT id FROM platformintegration_picklist_fields WHERE platform_module=? AND platform_field=? AND platform_source_module=? AND platform_type=? AND platform_value=? AND platform_name=?";
                $res = $adb->pquery($sql, array($param));
                if ($adb->num_rows($res) == 0) {
                    $sql = "INSERT INTO platformintegration_picklist_fields(platform_module, platform_field, platform_source_module, platform_type" . ", platform_value, platform_name) VALUES(?, ?, ?, ?, ?, ?)";
                    $adb->pquery($sql, $param);
                }
            }
        }
    }
    public function createAllCustomFields()
    {
        global $adb;
        $obj = new PlatformIntegration_Base_Model();
        $sql = "SELECT DISTINCT vt_module, has_custom_fields FROM platformintegration_modules WHERE vt_module IS NOT NULL AND vt_module != ''";
        $res = $adb->pquery($sql, array());
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                if ($row["has_custom_fields"] == "1") {
                    $module = $row["vt_module"];
                    $focus = CRMEntity::getInstance($module);
                    $table = $focus->customFieldTable[0];
                    $blocks = array("Platform");
                    $fields = array("Platform" => array("cf_sync_to_platformintegration" => array("label" => "Sync to Platform", "uitype" => 56, "displaytype" => 1), "cf_last_date_synched" => array("label" => "Last Date Synched", "uitype" => 70, "displaytype" => 2, "typeofdata" => "DT~O", "columntype" => "datetime")));
                    if ($module == "Products") {
                        $fields["Platform"]["cf_inventory_type"] = array("label" => "Inventory Type", "uitype" => 16, "displaytype" => 1, "picklistvalues" => array("Inventory", "NonInventory"), "defaultvalue" => "NonInventory");
                        $fields["Platform"]["cf_inventory_asset_account"] = array("label" => "Inventory asset account", "uitype" => 16, "displaytype" => 1, "picklistvalues" => array("Inventory", "Inventory Asset"));
                        $fields["Platform"]["cf_expense_account_pro"] = array("label" => "Expense account", "uitype" => 16, "displaytype" => 1, "picklistvalues" => array("Cost of Goods Sold"));
                        $fields["Platform"]["cf_income_account_pro"] = array("label" => "Income account", "uitype" => 16, "displaytype" => 1, "picklistvalues" => array("Sales"));
                        $fields["Platform"]["cf_is_taxable"] = array("label" => "Is taxable?", "uitype" => 56, "displaytype" => 1);
                    } else {
                        if ($module == "Services") {
                            $fields["Platform"]["cf_expense_account_ser"] = array("label" => "Expense account", "uitype" => 16, "displaytype" => 1, "picklistvalues" => array("Cost of Goods Sold"));
                            $fields["Platform"]["cf_income_account_ser"] = array("label" => "Income account", "uitype" => 16, "displaytype" => 1, "picklistvalues" => array("Sales"));
                            $fields["Platform"]["cf_is_taxable"] = array("label" => "Is taxable?", "uitype" => 56, "displaytype" => 1);
                        } else {
                            if ($module == "Invoice") {
                                $fields["Platform"]["cf_platform_invoice_no"] = array("label" => "Platform Inovice No", "uitype" => 1, "displaytype" => 2);
                                $fields["Platform"]["cf_platform_status_inv"] = array("label" => "Platform Status", "uitype" => 16, "displaytype" => 2, "picklistvalues" => array("Due", "Paid", "Partially paid", "Overdue"));
                            } else {
                                $fields["Platform"]["cf_platform_status"] = array("label" => "Platform Status", "uitype" => 16, "displaytype" => 1, "picklistvalues" => array("Active", "Inactive"));
                            }
                        }
                    }
                    $obj->createCustomField($blocks, $fields, $module, $table);
                }
            }
        }
    }
    public function createPlatformGroup()
    {
        global $adb;
        $groupName = PlatformIntegration_Base_Model::$groupName;
        $description = "This group is created for using module `PlatformIntegration` (Please does not edit or delete it)";
        $sql = "SELECT groupid FROM vtiger_groups WHERE groupname=?";
        $res = $adb->pquery($sql, array($groupName));
        if ($adb->num_rows($res) == 0) {
            $obj = new PlatformIntegration_Base_Model();
            $userId = $obj->getFirstAdminUserId();
            if (empty($userId)) {
                $userId = 1;
            }
            $recordModel = new Settings_Groups_Record_Model();
            $recordModel->set("groupname", $groupName);
            $recordModel->set("description", $description);
            $recordModel->set("group_members", array("Users:" . $userId));
            $recordModel->set("cf_do_not_create_queue_for_platformintegration", true);
            $recordModel->save();
        }
    }
    private function createScheduler()
    {
        $adb = PearDatabase::getInstance();
        $sql = "SELECT id FROM `vtiger_cron_task` WHERE `module` = 'PlatformIntegration'";
        $res = $adb->pquery($sql, array());
        if (!$adb->num_rows($res)) {
            $adb->pquery("INSERT INTO `vtiger_cron_task` (`name`, `handler_file`, `frequency`, `status`, `module`, `sequence`) VALUES ('PlatformIntegration', 'modules/PlatformIntegration/cron/PlatformIntegration.service', '7200', '0', 'PlatformIntegration', '100')", array());
        }
    }
    private function deactiveScheduler()
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery("UPDATE `vtiger_cron_task` SET `status`='1' WHERE (`module`='PlatformIntegration')", array());
    }
    private function deleteScheduler()
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery("DELETE FROM `vtiger_cron_task` WHERE (`module`='PlatformIntegration')", array());
    }
    private function createHandle()
    {
        global $adb;
        $em = new VTEventsManager($adb);
        $em->registerHandler("vtiger.entity.aftersave", "modules/PlatformIntegration/PlatformIntegrationHandler.php", "PlatformIntegrationHandler");
    }
    private function removeHandle()
    {
        global $adb;
        $em = new VTEventsManager($adb);
        $em->unregisterHandler("PlatformIntegrationHandler");
    }
}

?>