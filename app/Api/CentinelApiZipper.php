<?php

class CentinelApiZipper
{
	public static function createRegularZip($filePath, $zipPath)
	{
		shell_exec('zip -j -P ' . self::getZipPassword() . ' ' . $zipPath . ' ' . $filePath);
	}

	public static function create7zip($filePath, $zipPath)
	{
		shell_exec('7za a -p' . self::getZipPassword() . ' -mem=AES256 -mx=0 -tzip ' . $zipPath . ' ' . $filePath);
	}

	protected static function getZipPassword()
	{
		return get_option('centinel_api_zip_password');
	}
}