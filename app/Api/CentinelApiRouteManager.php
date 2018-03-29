<?php

require_once(CENTINELPATH . '/app/Admin/CentinelApiHelpers.php');

class CentinelApiRouteManager
{
	protected $helpers;

	public function __construct()
	{
		$this->helpers = new CentinelApiHelpers();
	}

	public function registerRoutes(CentinelApiApiController $apiController)
	{
		$systemApiPrefix = rest_get_url_prefix();
		$pluginApiPrefix = get_option('centinel_api_route_prefix');
		$routePrefix = substr($pluginApiPrefix, strlen($systemApiPrefix) + 1);

		register_rest_route($routePrefix, '/create-log', [
			[
				'methods' => 'POST',
				'callback' => [$apiController, 'createLog']
			]
		]);

		register_rest_route($routePrefix, '/download-log', [
			[
				'methods' => 'POST',
				'callback' => [$apiController, 'downloadLog']
			]
		]);
	}
}