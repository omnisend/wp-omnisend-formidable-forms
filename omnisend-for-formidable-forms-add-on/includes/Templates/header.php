<?php
	/**
	 * Header template for Omnisend settings
	 *
	 * @package Omnisend\FormidableFormsAddon\Provider\OmnisendActionSettingsProvider
	 */

	use Omnisend\FormidableFormsAddon\Provider\OmnisendActionSettingsProvider;

	$setting_code = $this->get_enabled_code_by_form_id( $values['id'] );
	$welcome_code = OmnisendActionSettingsProvider::get_enabled_welcome_by_form_id( $values['id'] );
?>

<div class = 'omnisend-formidable-header'>
	<div class = 'option-one'>
		<label class='switch general'>
			<input type='hidden' value='0' name='<?php echo esc_attr( $setting_code ); ?>'>
				<input type='checkbox' id = '<?php echo esc_attr( $setting_code ); ?>' value = '1' name = '<?php echo esc_attr( $setting_code ); ?>' <?php checked( '1', get_option( $setting_code ) ); ?>>
				<label for = '<?php echo esc_attr( $setting_code ); ?>' />
					<?php esc_html_e( 'Send form data to Omnisend', 'omnisend-formidable' ); ?>
				</label>
		</label>
		<span class = 'information'>
			<?php esc_html_e( 'Check this to see all data collected through your form in Omnisend', 'omnisend-formidable' ); ?>
		</span>
	</div>

	<div class = 'option-two'>
		<h3 class = 'information-header'><?php esc_html_e( 'Welcome email', 'omnisend-formidable' ); ?></h3>
		<span>
			<?php esc_html_e( 'Check this to automatically send your custom welcome email, created in omnisend, to subscribers, joining through Formidable Forms.', 'omnisend-formidable' ); ?>
		</span>
		<label class='switch mail'>
			<input type='hidden' value='0' name='<?php echo esc_attr( $welcome_code ); ?>'>
				<input type='checkbox' id = '<?php echo esc_attr( $welcome_code ); ?>' value = '1' name = '<?php echo esc_attr( $welcome_code ); ?>' <?php checked( '1', get_option( $welcome_code ) ); ?>>
			<label for = '<?php echo esc_attr( $welcome_code ); ?>' />
				<?php esc_html_e( 'Send a welcome email to new subscribers', 'omnisend-formidable' ); ?>
			</label>
		</label>
		<span class = 'information'>
			<?php esc_html_e( 'After checking this, don’t forget to design your welcome email in Omnisend.', 'omnisend-formidable' ); ?>
		</span>
		<a href = '' class = 'omnisend-url' target = '_blank'>
			<?php esc_html_e( 'Learn more about Welcome automation', 'omnisend-formidable' ); ?>
		</a>
	</div>

	<div class = 'additional-information'>
		<h3 class = 'information-header'>
			<?php esc_html_e( 'Field mapping', 'omnisend-formidable' ); ?>
		</h3>
		<span class = 'information'>
			<?php esc_html_e( "Field mapping lets you align your Formidable Forms fields with Omnisend. It's important to match them correctly, so the information collected through Formidable Forms goes into the right place in Omnisend.", 'omnisend-formidable' ); ?>
		</span>
		<span class = 'information-help'>
			<?php esc_html_e( 'Having trouble?', 'omnisend-formidable' ); ?>
			<a href = '' class = 'omnisend-url' target = '_blank'>
				<?php esc_html_e( 'Explore our help article', 'omnisend-formidable' ); ?>
			</a>	
		</span>
	</div>
</div>
