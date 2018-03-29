<?php

require_once(CENTINELPATH . '/app/Admin/CentinelApiZipChecker.php');

class CentinelApiAdminRequestHandler
{
	protected $validated = false;

	public function checkZipAvailability()
	{
		$this->authorize();

		try {
			$zipCheck = new CentinelApiZipChecker();
			$data = $zipCheck->check();

			if ($data['notice'] == 'success') {
				CentinelApiMessageManager::setSuccess($data['message']);
			} else {
				CentinelApiMessageManager::setError($data['message']);
			}
		} catch (Exception $e) {
			$message = "An error has occurred while checking for Zip availability: " . $e->getMessage();
			CentinelApiMessageManager::setError($message);
		}

		header('Location: ' . admin_url('options-general.php?page=centinel-api'));
		exit();
	}

	public function updateSettings()
	{
		$this->authorize();

		if (!empty($_POST)) {
			$input = $this->input();
			$messages = $this->validateRequest($input);

			if ($this->validated) {
				$this->updateOptions($input);

				CentinelApiMessageManager::setSuccess('Options updated.');
			} else {
				CentinelApiMessageManager::setError(implode('<br>', $messages));
			}
		}

		header('Location: ' . admin_url('options-general.php?page=centinel-api'));
		exit();
	}

	protected function authorize()
	{
		$user = wp_get_current_user();
		if (!in_array('administrator', (array) $user->roles)) {
			throw new Exception("User does not have privileges to access Centinel API settings.");
		}

		if (!is_admin()) {
			throw new Exception("User does not have privileges to access Centinel API settings.");
		}
	}

	protected function updateOptions($input)
	{
		foreach ($input as $option => $value) {
			update_option($option, $value);
		}
	}

	protected function validateRequest($input)
	{
		$messages = [];

		if (empty($input['centinel_api_private_key'])) {
			$messages[] = 'Private Key cannot be empty.';
		}

		if (strlen($input['centinel_api_encryption_key']) != 32) {
			$messages[] = 'Encryption Key cannot be empty and must be exactly 32 characters in length.';
		}

		$apiPrefix = rest_get_url_prefix();

		if (
			strpos($input['centinel_api_route_prefix'], $apiPrefix . '/') !== 0 ||
			$input['centinel_api_route_prefix'] == $apiPrefix . '/'
		) {
			$messages[] = 'Route Prefix must be in the following format: <strong>' . $apiPrefix . '/</strong>random_string';
		}

		if (empty($input['centinel_api_zip_password'])) {
			$messages[] = 'Zip Password cannot be empty.';
		}

		if (empty($input['centinel_api_dump_folder'])) {
			$messages[] = 'Dump Folder cannot be empty.';
		}

		if (!empty($input['centinel_api_timeout']) && !preg_match('/^[1-9][0-9]*$/', $input['centinel_api_timeout'])) {
			$messages[] = 'Timeout must be a number.';
		}

		if (!$messages) {
			$this->validated = true;
		}

		return $messages;
	}

	protected function input()
	{
		return [
			'centinel_api_private_key' => isset($_POST['centinel_api_private_key']) ? trim($_POST['centinel_api_private_key']) : '',
			'centinel_api_encryption_key' => isset($_POST['centinel_api_encryption_key']) ? trim($_POST['centinel_api_encryption_key']) : '',
			'centinel_api_route_prefix' => isset($_POST['centinel_api_route_prefix']) ? trim($_POST['centinel_api_route_prefix']) : '',
			'centinel_api_log_routes_enabled' => !empty($_POST['centinel_api_log_routes_enabled']) ? 1 : 0,
			'centinel_api_database_routes_enabled' => !empty($_POST['centinel_api_database_routes_enabled']) ? 1 : 0,
			'centinel_api_disable_time_based_authorization' => !empty($_POST['centinel_api_disable_time_based_authorization']) ? 1 : 0,
			'centinel_api_zip_password' => isset($_POST['centinel_api_zip_password']) ? trim($_POST['centinel_api_zip_password']) : '',
			'centinel_api_dump_folder' => isset($_POST['centinel_api_dump_folder']) ? trim($_POST['centinel_api_dump_folder']) : '',

			'centinel_api_timeout' => isset($_POST['centinel_api_timeout']) ? trim($_POST['centinel_api_timeout']) : '',
			'centinel_api_include_tables' => isset($_POST['centinel_api_include_tables']) ? trim($_POST['centinel_api_include_tables']) : '',
			'centinel_api_exclude_tables' => isset($_POST['centinel_api_exclude_tables']) ? trim($_POST['centinel_api_exclude_tables']) : '',
			'centinel_api_dont_skip_comments' => !empty($_POST['centinel_api_dont_skip_comments']) ? 1 : 0,
			'centinel_api_dont_use_extended_inserts' => !empty($_POST['centinel_api_dont_use_extended_inserts']) ? 1 : 0,
			'centinel_api_use_single_transaction' => !empty($_POST['centinel_api_use_single_transaction']) ? 1 : 0,
			'centinel_api_default_character_set' => isset($_POST['centinel_api_default_character_set']) ? trim($_POST['centinel_api_default_character_set']) : '',
			'centinel_api_dump_binary_path' =>
				isset($_POST['centinel_api_dump_binary_path']) ?
				str_replace("\\\\", "\\", trim($_POST['centinel_api_dump_binary_path'])) :
				'',
		];
	}
}