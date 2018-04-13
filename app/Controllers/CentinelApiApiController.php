<?php

if (version_compare(PHP_VERSION, '7.0', '<')) {
	require_once(CENTINELPATH . '/vendor/dbDumper151/autoload.php');
} else {
	require_once(CENTINELPATH . '/vendor/dbDumper290/autoload.php');
}

require_once(CENTINELPATH . '/app/Api/CentinelApiRouteManager.php');
require_once(CENTINELPATH . '/app/Admin/CentinelApiHelpers.php');
require_once(CENTINELPATH . '/app/Middleware/CentinelApiAuthorizeRequest.php');
require_once(CENTINELPATH . '/app/Api/CentinelApiDatabase.php');
require_once(CENTINELPATH . '/app/Api/CentinelApiZipper.php');

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
				$randomString = $this->helpers->randomString();
				$newFilePath = 'logs/y' . $foldersData['year'] . '/m' . $foldersData['month'] . '/' . (date('Y-m-d__H_i_s')) . '_' . $randomString . '.log';

				file_put_contents(ABSPATH . '/wp-content/' . $newFilePath, $logContents);
				file_put_contents($filePath, '');

				$data['success'] = true;
				$data['filesize'] = $filesize;
				$data['filePath'] = $newFilePath;
			} else {
				$data['message'] = "Log file doesn't exist";
			}
		} catch (\Exception $e) {
			$this->helpers->writeLog($e->getMessage());
			$data['message'] = "Error while creating the log file: " . $e->getMessage();
		}

		http_response_code(200);
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

	public function dumpDatabase()
	{
		if (!$this->middleware->authorize('centinelApiDatabaseDump')) {
			http_response_code(401);
			exit;
		}

		$data = $this->getDefaultDataSet();

		try {
			$this->createDbDumpFolder();
			$this->emptyDbDumpFolder();

			$filename = CentinelApiDatabase::dump();
			$fullPath = CentinelApiDatabase::getDumpPath($filename);
			$zipFilename = $this->zipDatabase($fullPath);
			$fullPath = $zipFilename ? CentinelApiDatabase::getDumpPath($zipFilename) : $fullPath;
			$filesize = filesize($fullPath);

			$data['success'] = true;
			$data['filesize'] = $filesize;
			$data['filePath'] = $zipFilename ?: $filename;
		} catch (\Exception $e) {
			$this->emptyDbDumpFolder();
			$this->deleteDbDumpFolder();
			$this->helpers->writeLog($e->getMessage());
			$data['message'] = "Error while dumping database: " . $e->getMessage();
		}

		http_response_code(200);
		return $data;
	}

	public function downloadDatabase()
	{
		if (!$this->middleware->authorize('centinelApiDatabaseDownload')) {
			http_response_code(401);
			exit;
		}

		$filename = isset($_POST['filePath']) ? $_POST['filePath'] : null;
		$fullFilePath = CentinelApiDatabase::getDumpPath($filename);

		if (!$filename || !file_exists($fullFilePath)) {
			http_response_code(422);
			exit;
		}

		ignore_user_abort(true);

		http_response_code(200);
		header("Content-Description: File Transfer");
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename='" . $fullFilePath . "'");

		readfile($fullFilePath);

		unlink($fullFilePath);
		$this->deleteDbDumpFolder();

		exit();
	}

	protected function zipDatabase($filePath)
	{
		$randomString = $this->helpers->randomString();
		$zipFilename = 'databasedump_' . $randomString . '.zip';
		$zipPath = CentinelApiDatabase::getDumpPath($zipFilename);

		// Try native zip
		CentinelApiZipper::createNativeZip($filePath, $zipPath);

		// Try 7-zip
		if (!file_exists($zipPath)) {
			CentinelApiZipper::create7zip($filePath, $zipPath);
		}

		// Try regular zip
		if (!file_exists($zipPath)) {
			CentinelApiZipper::createRegularZip($filePath, $zipPath);
		}

		// If Zip file was created successfully
		// return Zip filename
		if (file_exists($zipPath)) {
			if (file_exists($filePath)) {
				unlink($filePath);
			}

			return $zipFilename;
		}

		return null;
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

			$indexPhp = $folder . '/index.php';

			if (!file_exists($indexPhp)) {
				file_put_contents($indexPhp, '<?php');
			}
		}

		return [
			'year' => $year,
			'month' => $month
		];
	}

	protected function createDbDumpFolder()
	{
		$folder = CentinelApiDatabase::getDumpPath();

		if (!is_dir($folder)) {
			mkdir($folder);
		}

		$indexPhp = $folder . '/index.php';

		if (!file_exists($indexPhp)) {
			file_put_contents($indexPhp, '<?php');
		}
	}

	protected function emptyDbDumpFolder()
	{
		$folder = CentinelApiDatabase::getDumpPath();

		foreach (new \DirectoryIterator($folder) as $fileInfo) {
			if (!$fileInfo->isDot() && $fileInfo->getFilename() != 'index.php') {
				unlink($fileInfo->getPath() . '/' . $fileInfo->getFilename());
			}
		}
	}

	protected function deleteDbDumpFolder()
	{
		$folder = CentinelApiDatabase::getDumpPath();
		$indexPhp = $folder . '/index.php';

		if (file_exists($indexPhp)) {
			unlink($indexPhp);
		}

		if (is_dir($folder)) {
			rmdir($folder);
		}
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
}