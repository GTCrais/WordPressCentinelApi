<?php
/*
Plugin Name: WordPress Centinel API
Plugin URI: https://github.com
Description: Centinel API provides protected API for downloading the application log file, and dumping and downloading the database. It's designed to work in combination with <a href="https://centinel.online/"><strong>Centinel</strong></a> - centralized application management system.
Version: 1.0.0
Author: Tomislav Modrić
Author URI: https//gtcrais.net
License: MIT
License URI: https://opensource.org/licenses/MIT
*/

define('CENTINELPATH', __DIR__);

require_once(CENTINELPATH . '/app/Admin/CentinelApiMessageManager.php');
require_once(CENTINELPATH . '/app/Controllers/CentinelApiAdminController.php');

$centinelApiAdminController = new CentinelApiAdminController();
$centinelApiAdminController->renderAdmin();

add_action('rest_api_init', function () {
	register_rest_route('test', '/create-log', [
		'methods' => 'GET',
		'callback' => 'my_awesome_func',
	]);
});