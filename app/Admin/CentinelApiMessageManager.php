<?php


class CentinelApiMessageManager
{
	public static function getSuccess()
	{
		$message = get_transient('centinelApiSuccessMsg');
		self::flush('centinelApiSuccessMsg');

		return $message;
	}

	public static function setSuccess($message)
	{
		set_transient('centinelApiSuccessMsg', $message);
	}

	public static function getError()
	{
		$message = get_transient('centinelApiErrorMsg');
		self::flush('centinelApiErrorMsg');

		return $message;
	}

	public static function setError($message)
	{
		set_transient('centinelApiErrorMsg', $message);
	}

	public static function flush($messageType = 'all')
	{
		if ($messageType == 'all') {
			delete_transient('centinelApiSuccessMsg');
			delete_transient('centinelApiErrorMsg');
		} else {
			delete_transient($messageType);
		}
	}
}