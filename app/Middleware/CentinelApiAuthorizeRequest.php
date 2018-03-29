<?php

require_once(CENTINELPATH . '/app/Admin/CentinelApiHelpers.php');

class CentinelApiAuthorizeRequest
{
	protected $helpers;

	public function __construct()
	{
		$this->helpers = new CentinelApiHelpers();
	}

	public function authorize($routeName)
	{
		$logRoutesEnabled = get_option('centinel_api_log_routes_enabled');
		$databaseRoutesEnabled = get_option('centinel_api_database_routes_enabled');

		$enabledRoutes = [];

		if ($logRoutesEnabled) {
			$enabledRoutes[] = 'LogRoutes';
		}

		if ($databaseRoutesEnabled) {
			$enabledRoutes[] = 'DatabaseRoutes';
		}

		$encryptedString = isset($_POST['string']) ? $_POST['string'] : null;
		$hash = isset($_POST['hash']) ? $_POST['hash'] : null;

		if (!$encryptedString || !$hash) {
			$this->helpers->writeLog('WordPress Centinel API: \'string\' or \'hash\' fields not set');

			return false;
		}

		$routeType = '';

		if (substr($routeName, 0, strlen('centinelApiLog')) == 'centinelApiLog') {
			$routeType = 'LogRoutes';
		} else if (substr($routeName, 0, strlen('centinelApiDatabase')) == 'centinelApiDatabase') {
			$routeType = 'DatabaseRoutes';
		}

		if (!in_array($routeType, $enabledRoutes)) {
			$this->helpers->writeLog('WordPress Centinel API: Route ' . $routeName . ' disabled');

			return false;
		}

		$privateKey = get_option('centinel_api_private_key');

		if (hash_hmac('sha256', $encryptedString, $privateKey) != $hash) {
			$this->helpers->writeLog('WordPress Centinel API: Hash doesn\'t match');

			return false;
		}

		$encryptionKey = get_option('centinel_api_encryption_key');

		try {
			$payload = json_decode(base64_decode($encryptedString), true);
			$value = $payload['value'];
			$iv = base64_decode($payload['iv']);
			$decryptedString = openssl_decrypt($value, 'AES-256-CBC', $encryptionKey, 0, $iv);
		} catch (\Exception $e) {
			$this->helpers->writeLog('WordPress Centinel API: Error while decrypting string - ' . $e->getMessage());

			return false;
		}

		$decryptedSegments = explode('|', $decryptedString);

		if (count($decryptedSegments) != 3) {
			$this->helpers->writeLog('WordPress Centinel API: Invalid decrypted string');

			return false;
		}

		$dateTime = $decryptedSegments[1];

		if (!$dateTime) {
			$this->helpers->writeLog('WordPress Centinel API: DateTime not present in the decrypted string');

			return false;
		}

		$dateTimeZone = new \DateTimeZone('UTC');

		try {
			$receivedDateTime = new \DateTime($dateTime, $dateTimeZone);
		} catch (\Exception $e) {
			$this->helpers->writeLog('WordPress Centinel API: Received DateTime invalid');

			return false;
		}

		$now = new \DateTime('now', $dateTimeZone);
		$diffInSeconds = abs($receivedDateTime->getTimestamp() - $now->getTimestamp());

		$timeBasedAuthorizationDisabled = get_option('centinel_api_disable_time_based_authorization');

		if (!$timeBasedAuthorizationDisabled && $diffInSeconds > 45) {
			$this->helpers->writeLog('WordPress Centinel API: request time mismatch (1)');

			return false;
		}

		$cacheKey = $routeName . 'AccessTime';
		$lastRouteAccessTime = get_transient($cacheKey);

		if ($lastRouteAccessTime) {
			if ($lastRouteAccessTime == $receivedDateTime) {
				$this->helpers->writeLog(
					'WordPress Centinel API: Access to route ' . $routeName . ' attempted using a non-unique \'dateTime\' parameter. ' .
					'While this is likely not a security breach, changing your application private and encryption keys is recommended.'
				);

				return false;
			}

			if (!$timeBasedAuthorizationDisabled) {
				$lastRouteAccessTime = new \DateTime($lastRouteAccessTime, $dateTimeZone);
				$diffInSeconds = abs($receivedDateTime->getTimestamp() - $lastRouteAccessTime->getTimestamp());

				if ($diffInSeconds < 90) {
					$this->helpers->writeLog('WordPress Centinel API: Too many API calls for route ' . $routeName);

					return false;
				}

				if ($receivedDateTime->getTimestamp() < $lastRouteAccessTime->getTimestamp()) {
					$this->helpers->writeLog('WordPress Centinel API: request time mismatch (2)');

					return false;
				}
			}
		}

		set_transient($cacheKey, $dateTime, 10*60);

		return true;
	}
}
