<?php

class CentinelApiHelpers
{
	function randomString($length = 32)
	{
		$string = '';

		while (($len = strlen($string)) < $length) {
			$size = $length - $len;

			$bytes = random_bytes($size);

			$string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
		}

		return $string;
	}

	public function writeLog($content)
	{
		if (WP_DEBUG == true) {
            if (is_array($content) || is_object($content)) {
                error_log(print_r($content, true));
            } else {
                error_log($content);
            }
        }
	}
}