<?php
//site specific data
//----------------------
//modify parameter below

	define("API-KEY", "<YOUR-API KEY>"); // CHANGE

    // local or remote learning analytics backend
	define("USE_TEST_DB", true);
	define("DEBUG_MODE", false);
	define("LOG_DETAIL", true);

	if (defined("USE_TEST_DB") && (USE_TEST_DB)) {
		 define("HOST_URL", "http://localhost:8080/");
	} else {
		define("HOST_URL", "http://log.lip-app.eu/");
	}	

	define("LIP-API-KEY", "<YOUR-LIP-APP KEY>"); // can be remained by klaus@o-le.org
	
	// translate

	define("TRANSLATE-API-KEY", "<YOUR-GOOGLEAPI-KEY>"); // can be remained by googleapis.com
	
	// speach 
	
	define("SPEACH-API-KEY", "<YOUR-iSPEECHAPI-KEY>"); // can be remained by ispeach.com
		
?>