<?php

require_once(CENTINELPATH . '/app/Api/CentinelApiMySql.php');
require_once(CENTINELPATH . '/app/Admin/CentinelApiHelpers.php');

class CentinelApiDatabase
{
	public static function dump()
	{
		$dumpOptions = self::getDumpOptions();

		return self::dumpMySql($dumpOptions);
	}

	protected static function dumpMySql($dumpOptions)
	{
		$helpers = new CentinelApiHelpers();

		$randomString = $helpers->randomString();
		$filename = 'databasedump_' . $randomString . '.sql';
		$fullPath = self::getDumpPath($filename);

		/** @var Spatie\DbDumper\Databases\MySql $dumper */
		$dumper = self::getMySqlDumper();
		$dumper = self::setDumpOptions($dumper, $dumpOptions);

		$dumper->dumpToFile($fullPath);

		return $filename;
	}

	protected static function setDumpOptions($dumper, $dumpOptions)
	{
		/** @var \Spatie\DbDumper\Databases\MySql $dumper */

		if (DB_NAME && method_exists($dumper, 'setDbName')) {
			$dumper = $dumper->setDbName(DB_NAME);
		}

		if (DB_USER && method_exists($dumper, 'setUserName')) {
			$dumper = $dumper->setUserName(DB_USER);
		}

		if (DB_PASSWORD && method_exists($dumper, 'setPassword')) {
			$dumper = $dumper->setPassword(DB_PASSWORD);
		}

		if (DB_HOST && method_exists($dumper, 'setHost')) {
			$dumper = $dumper->setHost(DB_HOST);
		}

		if (isset($dumpOptions['dump_binary_path']) && method_exists($dumper, 'setDumpBinaryPath')) {
			$dumper = $dumper->setDumpBinaryPath($dumpOptions['dump_binary_path']);
		}

		if (isset($dumpOptions['timeout']) && method_exists($dumper, 'setTimeout')) {
			$dumper = $dumper->setTimeout($dumpOptions['timeout']);
		}

		if (isset($dumpOptions['includeTables']) && method_exists($dumper, 'includeTables')) {
			$dumper = $dumper->includeTables($dumpOptions['includeTables']);
		}

		if (isset($dumpOptions['excludeTables']) && method_exists($dumper, 'excludeTables')) {
			$dumper = $dumper->excludeTables($dumpOptions['excludeTables']);
		}

		if (isset($dumpOptions['dontSkipComments']) && $dumpOptions['dontSkipComments'] && method_exists($dumper, 'dontSkipComments')) {
			$dumper = $dumper->dontSkipComments();
		}

		if (isset($dumpOptions['dontUseExtendedInserts']) && $dumpOptions['dontUseExtendedInserts'] && method_exists($dumper, 'dontUseExtendedInserts')) {
			$dumper = $dumper->dontUseExtendedInserts();
		}

		if (isset($dumpOptions['useSingleTransaction']) && $dumpOptions['useSingleTransaction'] && method_exists($dumper, 'useSingleTransaction')) {
			$dumper = $dumper->useSingleTransaction();
		}

		if (isset($dumpOptions['setDefaultCharacterSet']) && method_exists($dumper, 'setDefaultCharacterSet')) {
			$dumper = $dumper->setDefaultCharacterSet($dumpOptions['setDefaultCharacterSet']);
		}

		return $dumper;
	}

	protected static function getDumpOptions()
	{
		$includeTables = trim(get_option('centinel_api_include_tables'));
		$excludeTables = trim(get_option('centinel_api_exclude_tables'));

		if ($includeTables) {
			$tables = explode(',', $includeTables);
			$tableArray = [];

			foreach ($tables as $table) {
				$tableArray[] = trim($table);
			}

			$includeTables = $tableArray;
		} else {
			$includeTables = null;
		}

		if ($excludeTables) {
			$tables = explode(',', $excludeTables);
			$tableArray = [];

			foreach ($tables as $table) {
				$tableArray[] = trim($table);
			}

			$excludeTables = $tableArray;
		} else {
			$excludeTables = null;
		}

		return [
			'timeout' => get_option('centinel_api_timeout') ?: null,
			'includeTables' => $includeTables,
			'excludeTables' => $excludeTables,
			'dontSkipComments' => get_option('centinel_api_dont_skip_comments'),
			'dontUseExtendedInserts' => get_option('centinel_api_dont_use_extended_inserts'),
			'useSingleTransaction' => get_option('centinel_api_use_single_transaction'),
			'setDefaultCharacterSet' => get_option('centinel_api_default_character_set') ?: null,
			'dump_binary_path' => get_option('centinel_api_dump_binary_path') ?: null,
		];
	}

	public static function getDumpPath($filename = null)
	{
		$dumpFolder = get_option('centinel_api_dump_folder');
		$path = ABSPATH . '/' . $dumpFolder;

		if ($filename) {
			$path .= '/' . $filename;
		}

		return $path;
	}

	protected static function getMySqlDumper()
	{
		// If Sqlite class exists, it means we're using
		// version of DB Dumper where MySql has been fixed
		// so we can return the default MySql dumper

		if (class_exists('Spatie\DbDumper\Databases\Sqlite')) {
			return \Spatie\DbDumper\Databases\MySql::create();
		}

		return CentinelApiMySql::create();
	}
}