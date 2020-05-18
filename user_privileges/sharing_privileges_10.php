<?php


//This is the sharing access privilege file
$defaultOrgSharingPermission=array('2'=>3,'4'=>3,'6'=>2,'7'=>2,'9'=>3,'13'=>2,'16'=>3,'20'=>2,'21'=>2,'22'=>2,'23'=>2,'26'=>2,'8'=>2,'14'=>2,'31'=>2,'34'=>2,'36'=>3,'38'=>2,'39'=>2,'40'=>2,'46'=>3,'47'=>3,'48'=>2,'18'=>2,'10'=>3,'50'=>3,'51'=>3,'52'=>3,'53'=>3,'56'=>2,'60'=>2,'61'=>2,'62'=>2,'63'=>2,'65'=>2,);

$related_module_share=array(2=>array(6,),13=>array(6,),20=>array(6,2,),22=>array(6,2,20,),23=>array(6,22,),);

$Leads_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$Leads_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$Leads_Emails_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Leads_Emails_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Accounts_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$Accounts_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$Contacts_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(1,10,12,13,16,),27=>array(1,10,11,12,13,),), 'USER'=>array());

$Contacts_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(1,10,12,13,16,),27=>array(1,10,11,12,13,),), 'USER'=>array());

$Accounts_Potentials_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Accounts_Potentials_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Accounts_HelpDesk_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Accounts_HelpDesk_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Accounts_Emails_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Accounts_Emails_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Accounts_Quotes_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Accounts_Quotes_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Accounts_SalesOrder_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Accounts_SalesOrder_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Accounts_Invoice_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Accounts_Invoice_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Potentials_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$Potentials_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$Potentials_Quotes_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Potentials_Quotes_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Potentials_SalesOrder_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Potentials_SalesOrder_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$HelpDesk_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$HelpDesk_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Emails_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Emails_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Campaigns_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Campaigns_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Quotes_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Quotes_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Quotes_SalesOrder_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Quotes_SalesOrder_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$PurchaseOrder_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$PurchaseOrder_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$SalesOrder_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$SalesOrder_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$SalesOrder_Invoice_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$SalesOrder_Invoice_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$Invoice_share_read_permission=array('ROLE'=>array(),'GROUP'=>array());

$Invoice_share_write_permission=array('ROLE'=>array(),'GROUP'=>array());

$PBXManager_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$PBXManager_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$SMSNotifier_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$SMSNotifier_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$ModComments_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$ModComments_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$Transactions_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$Transactions_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$Commissions_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$Commissions_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$KYC_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$KYC_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$Alarms_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$Alarms_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(26=>array(0=>1,1=>10,2=>12,3=>13,4=>16,),27=>array(0=>1,1=>10,2=>11,3=>12,4=>13,),), 'USER'=>array());

$Countries_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$Countries_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$PlatformIntegrationQueues_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$PlatformIntegrationQueues_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$PlatformIntegrationLogs_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$PlatformIntegrationLogs_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$PlatformIntegrationLinks_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$PlatformIntegrationLinks_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$Activities_share_read_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

$Activities_share_write_permission=array('ROLE'=>array(),'GROUP'=>array(), 'USER'=>array());

?>