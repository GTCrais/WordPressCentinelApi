<?php

require_once(CENTINELPATH . '/app/Api/CentinelApiZipper.php');

class CentinelApiZipChecker
{
    public function check()
    {
		$messages = [];
		$data = [
			'message' => null,
			'notice' => 'success'
		];
		$filename = 'debug.log';
		$filePath = ABSPATH . '/wp-content/' . $filename;
		$zipPath = ABSPATH . '/wp-content/test.zip';

		if (!file_exists($filePath)) {
			file_put_contents($filePath, '');
		}

		CentinelApiZipper::createNativeZip($filePath, $zipPath);

		if (file_exists($zipPath)) {
			unlink($zipPath);

			$data['message'] = "You're using PHP version " . PHP_VERSION . " so native Zip encryption is available! Your database dumps will be zipped and encrypted with AES-256.";

			return $data;
		}

		$messages[] = "Native Zip encryption is not available.";

		CentinelApiZipper::create7zip($filePath, $zipPath);

		if (file_exists($zipPath)) {
			unlink($zipPath);

			$data['message'] = "7-zip is available! Your database dumps will be zipped using 7-Zip and encrypted with AES-256.";

			return $data;
		}

		$messages[] = "7-zip is not available.";

		CentinelApiZipper::createRegularZip($filePath, $zipPath);

		if (file_exists($zipPath)) {
			unlink($zipPath);

			$messages[] = "Zip is available! Your database dumps will be zipped using Zip library and protected using password from the Centinel API config file.";
			$messages[] = "It is your responsibility to read up on Zip password protection and decide if this level of security is satisfactory.";

			$data['message'] = implode("<br>", $messages);

			return $data;
		}

		$messages[] = "Zip library is not available.";
		$messages[] = "Your database dumps will be sent to Centinel without being zipped and password protected beforehand.";

		$data['message'] =  implode("<br>", $messages);
		$data['notice'] = 'error';

		return $data;
    }
}
