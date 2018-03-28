<?php

require_once(CENTINELPATH . '/app/Admin/CentinelApiInitializer.php');
require_once(CENTINELPATH . '/app/Admin/CentinelApiViewManager.php');
require_once(CENTINELPATH . '/app/Admin/CentinelApiAdminRequestHandler.php');

class CentinelApiAdminController
{
	protected $initializer;

	protected $viewManager;

	public function __construct()
	{
		$this->initializer = new CentinelApiInitializer();
		$this->viewManager = new CentinelApiViewManager();
	}

	public function renderAdmin()
	{
		if (!is_admin()) {
			return;
		}

		$this->initializer->init();
		$this->viewManager->renderSettings();
	}
}