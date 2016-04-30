<?php


	# JWT token request

	# We need to use the time of the request
	$now = time();

	# header
	$jwt_header = base64_encode(json_encode(array(
		'alg' => 'RS256',
		'typ' => 'JWT'
	)));

	# Request data
	$jwt_claim = base64_encode(json_encode(array(
		'iss' => 'XXXXX@YYYYY.iam.gserviceaccount.com', # server to server mail
		'scope' => 'https://www.googleapis.com/auth/prediction', #scope of the token
		'aud' => 'https://accounts.google.com/o/oauth2/token', # auth url
		'exp' => $now + 3600,
		'iat' => $now
	)));

	# For signing the request we need to use the apiSigner.php from the php sdk and the .p12 key generated in the server to server oauth
	require_once 'apiSigner.php';
	$p12 = new apiP12Signer('xxxx.p12', 'notasecret');
	$jwt_signature = base64_encode($p12->sign($jwt_header . '.' . $jwt_claim));


	// Auth request
	$c = curl_init();
	curl_setopt_array($c, array(
		CURLOPT_HEADER => false,
		CURLOPT_POST => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POSTFIELDS => array(
			'grant_type' => 'assertion',
			'assertion_type' => 'http://oauth.net/grant_type/jwt/1.0/bearer',
			'assertion' => implode('.', array($jwt_header,$jwt_claim,$jwt_signature))
		),
		CURLOPT_URL => 'https://accounts.google.com/o/oauth2/token'
	));


	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);

	$x = curl_exec($c); 

	# Here we get the auth token
	$token = json_decode($x, true);


	# We prepare the predict API request
	$predict = array_values(array(XX, YY, 'ZZZZZ',4));


	$data = array("input" => array("csvInstance"=> $predict));                                                                    
	$data_string = json_encode($data);


	# Prediction request
	$query = 'access_token=' . $token['access_token'];
	curl_setopt_array($c, array(
		CURLOPT_HTTPGET => true,
		CURLOPT_URL => 'https://www.googleapis.com/prediction/v1.6/projects/PROJECTID/trainedmodels/fraude-v1/predict?'. $query,
		CURLOPT_CUSTOMREQUEST=> "POST",
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_POSTFIELDS => $data_string,
		CURLOPT_RETURNTRANSFER => true,                                                                      
		CURLOPT_HTTPHEADER => array(                                                                          
		    'Content-Type: application/json',                                                                                
		    'Content-Length: ' . strlen($data_string)                                                                       
		)

	));

	$x = curl_exec($c); 

	curl_close($c);

	# request response
	echo $x;

?>


