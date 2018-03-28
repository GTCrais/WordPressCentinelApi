<div class="wrap">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

	<div>
		<p class="description">
			Check <a href='#' target='_blank'>Centinel API GitHub page</a> for details.
		</p>
		<br>
	</div>

	<?php if ($message = CentinelApiMessageManager::getSuccess()): ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo $message ?></p>
		</div>
	<?php elseif ($message = CentinelApiMessageManager::getError()): ?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo $message ?></p>
		</div>
	<?php endif; ?>

	<form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
		<input type='hidden' name='action' value='centinel_api_zip_submit' />
		<input type='hidden' name='option_page' value='<?php echo esc_attr('centinel_api_zip') ?>' />

		<h2>Check Zip Availability</h2>

		<?php
		wp_nonce_field("centinel_api_zip");
		submit_button('Check');
		?>
	</form>

	<form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
		<input type='hidden' name='action' value='centinel_api_submit' />
		<input type='hidden' name='option_page' value='<?php echo esc_attr('centinel_api_options') ?>' />

		<?php
			wp_nonce_field("centinel_api_options-options");
			do_settings_sections('centinel-api');
			submit_button('Save Settings');
		?>
	</form>
</div>