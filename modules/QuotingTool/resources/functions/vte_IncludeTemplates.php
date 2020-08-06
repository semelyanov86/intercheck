<?php

if (!function_exists('includeTemplate')) {
    /**
     * @param $value
     * @param int $decimal
     * @return float
     */
    function includeTemplate($fieldName = '',$crmid = 0, $templateId = 0)
    {
        global $site_URL, $current_user,$adb, $vtiger_current_version;
        $crmid = preg_replace("/\s|&nbsp;/",'',$crmid);
        $fieldName = preg_replace("/\s|&nbsp;/",'', $fieldName);
        $oppRecordModel = Vtiger_Record_Model::getInstanceById($crmid);
        if ($fieldName != '') {
            $entityId = $oppRecordModel->get($fieldName);
            if ($entityId == 0 || $entityId == '') {
                return '';
            }
        }else{
            $entityId = $crmid;
        }
        $recordModel = new QuotingTool_Record_Model();
        /** @var QuotingTool_Record_Model $record */
        $record = $recordModel->getById($templateId);
        $record = $record->decompileRecord($entityId, array('content'));
        // Merge special tokens
        $keys_values = array();
        //company token
        $companyModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
        $quotingTool = new QuotingTool();
        $companyfields = array();
        $varContent = $quotingTool->getVarFromString($record->get('content'));
        foreach ($companyModel->getFields() as $key => $val) {
            if ($key == 'logo') {
                continue;
            }
            $companyfields["$"."Vtiger_Company_".$key."$"] = $companyModel->get($key);

        }
        foreach ($varContent as $var) {
            if ($var == '$custom_user_signature$') {
                $keys_values['$custom_user_signature$'] = nl2br($current_user->signature);
            }
            if (array_key_exists($var, $companyfields)) {
                $keys_values[$var] = $companyfields[$var];
            }
        }
        if (!empty($keys_values)) {
            $record->set('content', $quotingTool->mergeCustomTokens($record->get('content'), $keys_values));
        }
        //Processing for fieldImage added
        $full_content = $record->get('content');
        $tmp_html = str_get_html($full_content);
        foreach ($tmp_html->find('img') as $img) {
            $json_data_info = $img->getAttribute('data-info');
            $data_info = json_decode(html_entity_decode($json_data_info));
            if($data_info){
                $field_id = $data_info ->settings_field_image_fields;
                if($field_id > 0){
                    $field_model = Vtiger_Field_Model::getInstance($field_id);
                    $field_name = $field_model->getName();
                    $related_record_model = Vtiger_Record_Model::getInstanceById($entityId);
                    $img_path_array = explode('$$',$related_record_model->get($field_name));
                    $img->setAttribute('src',$site_URL.$img_path_array[0]);
                }
            }
        }
        //Processing for product_image
        $QuotingToolRecordModel = new QuotingTool_Record_Model();
        foreach ($tmp_html->find('img') as $img) {
            $quoting_tool_product_image = $img->getAttribute('class');
            if($quoting_tool_product_image == "quoting_tool_product_image"){
                $product_id= $img ->getAttribute('data-productid');
                if($product_id){
                    $productRecordModel = Vtiger_Record_Model::getInstanceById($product_id);
                    if ($productRecordModel) {
                        $image = $productRecordModel->getImageDetails();
                        if ($vtiger_current_version > 7.1) {
                            $imageUrl = $QuotingToolRecordModel->getAttachmentFile($image[0]['id'], $image[0]['name']);
                        }else{
                            $img_path = $image[0]['path'].'_'.$image[0]['name'];
                            $imageUrl = $site_URL.'/'.$img_path;
                        }
                        $img->setAttribute('src',$imageUrl);
                    }else{
                        $img->setAttribute('src','');
                    }
                }
            }
        }
        $full_content = $tmp_html ->save();
        //Process for Barcode
        preg_match_all("'\[BARCODE\|(.*?)\|BARCODE\]'si", $full_content, $match);
        if(count($match) > 0){
            require_once 'modules/QuotingTool/resources/barcode/autoload.php';
            $full_content =  preg_replace_callback("/\[BARCODE\|(.+?)\|BARCODE\]/",
                function ($barcode_val)
                {
                    $array_values = explode('=',$barcode_val[1]);
                    $field_value = $array_values[1];
                    $method = $array_values[0];
                    $qt = new QuotingTool();
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    $barcode_png = '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($field_value,$qt->barcode_type_code[$method])) . '" />';
                    return $barcode_png;
                }, $full_content);
        }
        $record->set('content', $full_content);
        if (!empty($keys_values)) {
            $record->set('content', $quotingTool->mergeCustomTokens($record->get('content'), $keys_values));
        }
        // Create PDF
        // Get data info settings
        $content = $record->get('content');

        $html = str_get_html($content);
        // If not found table block
        if (!$html) {
            return $content;
        }
        foreach ($html->find('table') as $table) {
            $table->removeAttribute('data-info');
        }
        $content = $html->save();
        return $content;
    }
}