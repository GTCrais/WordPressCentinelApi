<input type="hidden" name="<?php echo $fieldName ?>" value="0">
<input type="checkbox" name="<?php echo $fieldName ?>" value="1" <?php echo (!empty($setting) ? 'checked="checked"' : '') ?>>

<?php if (!empty($description)): ?>
<p class="description">
	<?php echo $description; ?>
</p>
<?php endif; ?>
