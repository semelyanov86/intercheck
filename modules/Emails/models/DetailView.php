<?php

class Emails_DetailView_Model extends Vtiger_DetailView_Model
{
    public function getDetailViewLinks($linkParams)
    {
        $linkModelList = parent::getDetailViewLinks($linkParams);
        unset($linkModelList['DETAILVIEWBASIC']);
        return $linkModelList;
    }
}