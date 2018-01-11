#!/usr/bin/php
<?php
require 'ZabbixApi.php';

function getHosts($api) {
	
	$method = 'host.get';
	$params = array(
		'output'            => ["host"],
		'filter'            => array(
			'status'    => 0
		)
	);

	$response = $api->executeRequest($method, $params);
	return $response['result'];
}

/* Load settings. */
$configs = parse_ini_file("api.conf");

/* setting */
$zbx_host = $configs["zbx_host"];
$zbx_user = $configs["zbx_user"];
$zbx_pass = $configs["zbx_pass"];

/* Call API */
$api = new ZabbixApi($zbx_host);
try {
	$api->login($zbx_user, $zbx_pass);

	$hosts = getHosts($api);
	$api->logout();
} catch(ZabbixException $e) {
	echo "ERROR: " . $e->getMessage() . "\n";
	exit;
}

echo json_encode($hosts, JSON_PRETTY_PRINT);
?>

