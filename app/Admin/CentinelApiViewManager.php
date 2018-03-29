<?php

class CentinelApiViewManager
{
	public function renderSettings()
	{
		$this->registerMenuAndSettings();
	}

	protected function registerMenuAndSettings()
	{
		add_action('admin_menu', function() {
			$this->centinelApiMenu();
		});

		add_action('admin_init', function() {
			$this->centinelApiInitSettings();
		});
	}

	protected function centinelApiMenu()
	{
		add_options_page(
			'Centinel API Settings',
			'Centinel API Settings',
			'administrator',
			'centinel-api',
			function() {
				$this->renderContent();
			}
		);
	}

	protected function centinelApiInitSettings()
	{
		$this->registerSections();
		$this->registerGeneralSettings();
		$this->registerMySqlSettings();
	}

	protected function registerMySqlSettings()
	{
		add_settings_field(
			'centinel_api_include_tables',
			'Include Tables',
			function($args) {
				$fieldName = 'centinel_api_include_tables';
				$setting = get_option($fieldName);
				$description = 'prefix_table1,prefix_table2,prefix_table3,...';

				include(CENTINELPATH . '/views/inputFields/text.php');
			},
			'centinel-api',
			'centinel-api-mysql'
		);

		add_settings_field(
			'centinel_api_exclude_tables',
			'Exclude Tables',
			function($args) {
				$fieldName = 'centinel_api_exclude_tables';
				$setting = get_option($fieldName);
				$description = 'prefix_table1,prefix_table2,prefix_table3,...';

				include(CENTINELPATH . '/views/inputFields/text.php');
			},
			'centinel-api',
			'centinel-api-mysql'
		);

		add_settings_field(
			'centinel_api_dont_skip_comments',
			'Don\'t Skip Comments',
			function($args) {
				$fieldName = 'centinel_api_dont_skip_comments';
				$setting = get_option($fieldName);

				include(CENTINELPATH . '/views/inputFields/checkbox.php');
			},
			'centinel-api',
			'centinel-api-mysql'
		);

		add_settings_field(
			'centinel_api_dont_use_extended_inserts',
			'Don\'t Use Extended Inserts',
			function($args) {
				$fieldName = 'centinel_api_dont_use_extended_inserts';
				$setting = get_option($fieldName);

				include(CENTINELPATH . '/views/inputFields/checkbox.php');
			},
			'centinel-api',
			'centinel-api-mysql'
		);

		add_settings_field(
			'centinel_api_use_single_transaction',
			'Use Single Transactions',
			function($args) {
				$fieldName = 'centinel_api_use_single_transaction';
				$setting = get_option($fieldName);

				include(CENTINELPATH . '/views/inputFields/checkbox.php');
			},
			'centinel-api',
			'centinel-api-mysql'
		);

		add_settings_field(
			'centinel_api_default_character_set',
			'Default Character Set',
			function($args) {
				$fieldName = 'centinel_api_default_character_set';
				$setting = get_option($fieldName);

				include(CENTINELPATH . '/views/inputFields/text.php');
			},
			'centinel-api',
			'centinel-api-mysql'
		);

		add_settings_field(
			'centinel_api_dump_binary_path',
			'Dump Binary Path',
			function($args) {
				$fieldName = 'centinel_api_dump_binary_path';
				$setting = get_option($fieldName);

				include(CENTINELPATH . '/views/inputFields/text.php');
			},
			'centinel-api',
			'centinel-api-mysql'
		);
	}

	protected function registerGeneralSettings()
	{
		add_settings_field(
			'centinel_api_private_key',
			'Private Key',
			function($args) {
				$fieldName = 'centinel_api_private_key';
				$setting = get_option($fieldName);

				include(CENTINELPATH . '/views/inputFields/text.php');
			},
			'centinel-api',
			'centinel-api-general'
		);

		add_settings_field(
			'centinel_api_encryption_key',
			'Encryption Key',
			function($args) {
				$fieldName = 'centinel_api_encryption_key';
				$setting = get_option($fieldName);
				$description = 'Encryption key must be exactly 32 characters in length!';

				include(CENTINELPATH . '/views/inputFields/text.php');
			},
			'centinel-api',
			'centinel-api-general'
		);

		add_settings_field(
			'centinel_api_route_prefix',
			'Route Prefix',
			function($args) {
				$fieldName = 'centinel_api_route_prefix';
				$setting = get_option($fieldName);
				$description = 'Route prefix must begin with <strong>' . rest_get_url_prefix() . '/</strong> !';

				include(CENTINELPATH . '/views/inputFields/text.php');
			},
			'centinel-api',
			'centinel-api-general'
		);

		add_settings_field(
			'centinel_api_log_routes_enabled',
			'Log Routes Enabled',
			function($args) {
				$fieldName = 'centinel_api_log_routes_enabled';
				$setting = get_option($fieldName);

				include(CENTINELPATH . '/views/inputFields/checkbox.php');
			},
			'centinel-api',
			'centinel-api-general'
		);

		add_settings_field(
			'centinel_api_database_routes_enabled',
			'Database Routes Enabled',
			function($args) {
				$fieldName = 'centinel_api_database_routes_enabled';
				$setting = get_option($fieldName);

				include(CENTINELPATH . '/views/inputFields/checkbox.php');
			},
			'centinel-api',
			'centinel-api-general'
		);

		add_settings_field(
			'centinel_api_disable_time_based_authorization',
			'Disable Time Based Authorization',
			function($args) {
				$fieldName = 'centinel_api_disable_time_based_authorization';
				$setting = get_option($fieldName);
				$description = '
					Check this option if you\'re getting "Request time mismatch"<br>
					or "Too many API calls" error. It means your server\'s and<br>
					Centinel\'s datetime are out of sync.
				';

				include(CENTINELPATH . '/views/inputFields/checkbox.php');
			},
			'centinel-api',
			'centinel-api-general'
		);

		add_settings_field(
			'centinel_api_zip_password',
			'Zip Password',
			function($args) {
				$fieldName = 'centinel_api_zip_password';
				$setting = get_option($fieldName);

				include(CENTINELPATH . '/views/inputFields/text.php');
			},
			'centinel-api',
			'centinel-api-general'
		);
	}

	protected function registerSections()
	{
		add_settings_section(
			'centinel-api-general',
			'General Settings',
			function () {},
			'centinel-api'
		);

		add_settings_section(
			'centinel-api-mysql',
			'MySQL Settings',
			function () {
				echo "
					<p>
						All MySQL settings are optional.<br>
						Check <a href='https://github.com/spatie/db-dumper' target='_blank'>Spatie DB Dumper</a> for details
						or just leave them empty.
					</p>
				";
			},
			'centinel-api'
		);
	}

	protected function renderContent()
	{
		include_once(CENTINELPATH . '/views/settings.php');
	}
}