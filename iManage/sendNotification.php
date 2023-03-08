<?php

//	07-03-19
//	Added by Sakib to insert Onesignal playerID into cms_login's device_token.

require_once('model/DB_class.php');

function sendMessage($players,$notificationFrom){
	$content = array(
		"en" => $notificationFrom
	);

	$fields = array(
		'app_id' => "aae0f2d5-28b8-4ccd-ae18-a18eb092d1ee",
		'include_player_ids' => $players,
		'data' => array("foo" => "bar"),
		'contents' => $content
	);

	$fields = json_encode($fields);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
	'Authorization: Basic ZTIyMzE4YTYtMmE4Mi00NDhlLWJiNDQtMmQwNGRkYzVhZGYw'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}

?>
