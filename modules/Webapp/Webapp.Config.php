<?php

$Module_Mobile_Configuration = array(

	'Default.Skin'     => 'default.css', // Available in resources/skins
	'Navigation.Limit' => 25,

	// Control number of records sent out through API (SyncModuleRecords, Query...) which supports paging.	
	'API_RECORD_FETCH_LIMIT' => 10, // NOTE: vtws_query internally limits fetch to 100 and give room to perform 1 extra fetch to determine paging
	
);

?>
