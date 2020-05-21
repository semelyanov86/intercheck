<?php


//This is the access privilege file
$is_admin=false;

$current_user_roles='H33';

$current_user_parent_role_seq='H1::H2::H33';

$current_user_profiles=array(2,);

$profileGlobalPermission=array('1'=>1,'2'=>1,);

$profileTabsPermission=array('1'=>0,'2'=>0,'3'=>0,'4'=>0,'6'=>0,'7'=>0,'8'=>0,'9'=>0,'10'=>0,'13'=>0,'14'=>0,'15'=>0,'16'=>0,'18'=>0,'19'=>0,'20'=>0,'21'=>0,'22'=>0,'23'=>0,'24'=>0,'25'=>0,'26'=>0,'27'=>0,'30'=>0,'31'=>0,'32'=>0,'33'=>0,'34'=>0,'35'=>0,'36'=>0,'37'=>0,'38'=>0,'39'=>0,'40'=>0,'41'=>0,'42'=>0,'43'=>0,'44'=>0,'45'=>0,'46'=>0,'47'=>0,'48'=>0,'49'=>0,'50'=>0,'51'=>0,'52'=>0,'53'=>0,'54'=>0,'55'=>0,'56'=>0,'57'=>0,'58'=>0,'59'=>0,'60'=>0,'61'=>0,'62'=>0,'63'=>0,'64'=>0,'65'=>0,'28'=>0,);

$profileActionPermission=array(2=>array(0=>1,1=>1,2=>1,3=>0,4=>0,7=>1,5=>1,6=>1,10=>0,),4=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>1,6=>1,8=>0,10=>0,),6=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>1,6=>1,8=>0,10=>0,),7=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>1,6=>1,8=>0,9=>0,10=>0,),8=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,6=>1,),9=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,),13=>array(0=>1,1=>1,2=>1,3=>0,4=>0,7=>1,5=>1,6=>1,8=>0,10=>0,),14=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>1,6=>1,10=>0,),15=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,),16=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,),18=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>1,6=>1,10=>0,),19=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>1,6=>1,10=>1,),20=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,),21=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,),22=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,),23=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,),25=>array(0=>1,1=>0,2=>0,3=>0,4=>0,7=>0,6=>0,13=>0,),26=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,),31=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,10=>0,),34=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,10=>0,),36=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,11=>1,12=>1,14=>0,15=>0,),38=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>1,6=>1,10=>1,),39=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,10=>0,),40=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,10=>0,),46=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,),47=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,),48=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,),50=>array(0=>0,1=>0,2=>1,3=>0,4=>0,7=>0,5=>0,6=>0,8=>0,10=>0,),51=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,8=>0,),52=>array(0=>1,1=>1,2=>1,3=>0,4=>0,7=>1,5=>0,6=>0,8=>0,),53=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,8=>0,),56=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,8=>0,10=>0,),60=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,10=>0,),61=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,10=>0,),62=>array(0=>0,1=>0,2=>0,3=>0,4=>0,7=>0,5=>0,6=>0,10=>0,),63=>array(0=>1,1=>1,2=>1,3=>0,4=>0,7=>1,5=>0,6=>0,8=>0,10=>0,),);

$current_user_groups=array(26,27,);

$subordinate_roles=array();

$parent_roles=array('H1','H2',);

$subordinate_roles_users=array();

$user_info=array('user_name'=>'dep','is_admin'=>'off','user_password'=>'$2y$10$S.4C/JyOlVKL8GMgZH7aKOWFCdNSPacJg3FBnpGrql95MObr1S6Xq','confirm_password'=>'$2y$10$6o/Lpcq3f9t7433aKn2zUe0aJgO22WTBFKMWt7tijWt4BMJ4s9SZ2','first_name'=>'dep','last_name'=>'dep','roleid'=>'H33','email1'=>'harlybarlycrm@yahoo.com','status'=>'Active','activity_view'=>'Today','lead_view'=>'Today','hour_format'=>'12','end_hour'=>'','start_hour'=>'09:00','is_owner'=>'','title'=>'Department Manager','phone_work'=>'','department'=>'Department Manager','phone_mobile'=>'','reports_to_id'=>'','phone_other'=>'','email2'=>'','phone_fax'=>'','secondaryemail'=>'','phone_home'=>'','date_format'=>'dd-mm-yyyy','signature'=>'','description'=>'','address_street'=>'Anuoki street, 2-88','address_city'=>'New York','address_state'=>'','address_postalcode'=>'','address_country'=>'USA','accesskey'=>'NSWjbFA3nD1lfl9e','time_zone'=>'Asia/Jerusalem','currency_id'=>'2','currency_grouping_pattern'=>'123,456,789','currency_decimal_separator'=>'.','currency_grouping_separator'=>',','currency_symbol_placement'=>'$1.0','imagename'=>'','internal_mailer'=>'0','theme'=>'softed','language'=>'en_us','reminder_interval'=>'','phone_crm_extension'=>'','no_of_currency_decimals'=>'2','truncate_trailing_zeros'=>'0','dayoftheweek'=>'Sunday','callduration'=>'5','othereventduration'=>'5','calendarsharedtype'=>'public','default_record_view'=>'Detail','leftpanelhide'=>'0','rowheight'=>'medium','defaulteventstatus'=>'','defaultactivitytype'=>'','hidecompletedevents'=>'0','defaultcalendarview'=>'','cloud_zadarma_extension'=>'','cloud_pbx_extension'=>'','group_view'=>'0','user_groups'=>'26 |##| 27','user_profiles'=>'2','all_groups'=>'0','currency_name'=>'USA, Dollars','currency_code'=>'USD','currency_symbol'=>'&#36;','conv_rate'=>'1.00000','record_id'=>'','record_module'=>'','id'=>'10');
?>