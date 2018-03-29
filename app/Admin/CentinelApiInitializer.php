<?php

require_once(CENTINELPATH . '/app/Admin/CentinelApiHelpers.php');

class CentinelApiInitializer
{
	protected $helpers;
	protected $requestHandler;

	public function __construct()
	{
		$this->helpers = new CentinelApiHelpers();
		$this->requestHandler = new CentinelApiAdminRequestHandler();
	}

	public function init()
	{
		$pluginIndex = CENTINELPATH . '/centinel-api.php';

		register_activation_hook($pluginIndex, [$this, 'addOptions']);
		register_uninstall_hook($pluginIndex, 'CentinelApiInitializer::removeOptions');

		add_action('admin_post_centinel_api_zip_submit', function() {
			$this->requestHandler->checkZipAvailability();
		});

		add_action('admin_post_centinel_api_submit', function() {
			$this->requestHandler->updateSettings();
		});
	}

	public function addOptions()
	{
		$optionList = $this->optionList();

		foreach ($optionList as $optionName => $optionValue) {
			add_option($optionName, $optionValue);
		}
	}

	public static function removeOptions()
	{
		$optionList = self::statisOptionList();

		foreach ($optionList as $optionName => $optionValue) {
			delete_option($optionName);
		}
	}

	protected function optionList()
	{
		$privateKey = $this->helpers->randomString();
		$encryptionKey = $this->helpers->randomString();
		$routePrefix = rest_get_url_prefix() . '/' . $this->helpers->randomString();
		$zipPassword = $this->helpers->randomString();

		$optionList = self::statisOptionList();
		$optionList['centinel_api_private_key'] = $privateKey;
		$optionList['centinel_api_encryption_key'] = $encryptionKey;
		$optionList['centinel_api_route_prefix'] = $routePrefix;
		$optionList['centinel_api_zip_password'] = $zipPassword;

		return $optionList;
	}

	public static function statisOptionList()
	{
		return [
			'centinel_api_private_key' => null,
			'centinel_api_encryption_key' => null,
			'centinel_api_route_prefix' => null,
			'centinel_api_log_routes_enabled' => 1,
			'centinel_api_database_routes_enabled' => 1,
			'centinel_api_disable_time_based_authorization' => 0,
			'centinel_api_zip_password' => null,

			'centinel_api_include_tables' => '',
			'centinel_api_exclude_tables' => '',
			'centinel_api_dont_skip_comments' => 0,
			'centinel_api_dont_use_extended_inserts' => 0,
			'centinel_api_use_single_transaction' => 0,
			'centinel_api_default_character_set' => '',
			'centinel_api_dump_binary_path' => '',
		];
	}
}