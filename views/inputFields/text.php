<input type="text" class="regular-text" name="<?php echo $fieldName; ?>" value="<?php echo (isset($setting) ? esc_attr($setting) : '') ?>">

<?php if (!empty($description)): ?>
	<p class="description">
		<?php echo $description; ?>
	</p>
<?php endif; ?>

