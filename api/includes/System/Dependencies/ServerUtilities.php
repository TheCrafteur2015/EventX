<?php

function isConnected(): bool {
	$connected = @fsockopen("www.google.com", 80);

	if ($connected) {
		fclose($connected);
		return true;
	} else
		return false;
}

function getIpAddress() {
	if (isConnected()) {
		$ip = gethostbynamel(gethostname());
		return $ip[count($ip) - 1];
	}
	return "127.0.0.1";
}
