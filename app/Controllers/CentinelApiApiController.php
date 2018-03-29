<?php

if (version_compare(PHP_VERSION, '7.0', '<')) {
	require_once(CENTINELPATH . '/vendor/dbDumper151/autoload.php');
} else {
	require_once(CENTINELPATH . '/vendor/dbDumper290/autoload.php');
}

require_once(CENTINELPATH . '/app/Api/CentinelApiRouteManager.php');
require_once(CENTINELPATH . '/app/Admin/CentinelApiHelpers.php');
require_once(CENTINELPATH . '/app/Middleware/CentinelApiAuthorizeRequest.php');

class CentinelApiApiController
{
	protected $helpers;
	protected $middleware;

	public function __construct()
	{
		$this->helpers = new CentinelApiHelpers();
		$this->middleware = new CentinelApiAuthorizeRequest();
	}

	public function setupApi()
	{
		$routeManager = new CentinelApiRouteManager();
		$routeManager->registerRoutes($this);
	}

	public function createLog()
	{
		if (!$this->middleware->authorize('centinelApiLogCreate')) {
			http_response_code(401);
			exit;
		}

		$data = $this->getDefaultDataSet();

		$filePath = ABSPATH . '/wp-content/debug.log';

		try {
			if (file_exists($filePath)) {
				$logContents = file_get_contents($filePath);

				if (!trim($logContents)) {
					$data['success'] = true;

					return $data;
				}

				$filesize = filesize($filePath);
				$foldersData = $this->createLogFolders();
				$newFilePath = 'logs/y' . $foldersData['year'] . '/m' . $foldersData['month'] . '/' . (date('Y-m-d__H_i_s')) . '.log';

				file_put_contents(ABSPATH . '/wp-content/' . $newFilePath, $logContents);
				file_put_contents($filePath, '');

				$data['success'] = true;
				$data['filesize'] = $filesize;
				$data['filePath'] = $newFilePath;
			} else {
				$data['message'] = "Log file doesn't exist";
			}
		} catch (\Exception $e) {
			$this->helpers->writeLog($e);
			$data['message'] = "Error while creating the log file: " . $e->getMessage();
		}

		return $data;
	}

	public function downloadLog()
	{
		if (!$this->middleware->authorize('centinelApiLogDownload')) {
			http_response_code(401);
			exit;
		}

		$filePath = isset($_POST['filePath']) ? $_POST['filePath'] : null;
		$fullFilePath = ABSPATH . '/wp-content/' . $filePath;

		if (!$filePath || !file_exists($fullFilePath)) {
			http_response_code(422);
			exit;
		}

		http_response_code(200);
		header("Content-Description: File Transfer");
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename='" . $fullFilePath . "'");

		readfile($fullFilePath);
		exit();
	}

	protected function createLogFolders()
	{
		$year = date("Y");
		$month = date("m");

		$folders = $this->getLogFolderPaths($year, $month);

		foreach ($folders as $folder) {
			if (!is_dir($folder)) {
				mkdir($folder);
			}
		}

		return [
			'year' => $year,
			'month' => $month
		];
	}

	protected function getLogFolderPaths($year, $month)
	{
		return [
			ABSPATH . '/wp-content/logs',
			ABSPATH . '/wp-content/logs/y' . $year,
			ABSPATH . '/wp-content/logs/y' . $year . '/m' . $month
		];
	}

	protected function getPlatform()
	{
		return 'wordpress';
	}

	protected function getPlatformVersion()
	{
		global $wp_version;

		return $wp_version;
	}

	protected function getDefaultDataSet()
	{
		return [
			'success' => false,
			'filesize' => 0,
			'filePath' => null,
			'message' => null,
			'platform' => $this->getPlatform(),
			'platformVersion' => $this->getPlatformVersion()
		];
	}



	public function test()
	{
		$dumper = \Spatie\DbDumper\Databases\MySql::create();
		$binaryPath = get_option('centinel_api_dump_binary_path');

		$dumper->setDbName(DB_NAME)
			->setUserName(DB_USER)
			->setPassword(DB_PASSWORD)
			->setHost(DB_HOST)
			->setDumpBinaryPath($binaryPath)
			->dumpToFile(ABSPATH . '/database.sql');
	}
}