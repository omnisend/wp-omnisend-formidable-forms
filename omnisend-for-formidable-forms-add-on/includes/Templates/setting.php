<?php
/**
 * Setting template for Omnisend settings
 *
 * @package Omnisend\FormidableFormsAddon\Provider\OmnisendActionSettingsProvider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class = 'omnisend-formidable-forms-row'>
	<label for = '<?php echo esc_attr( $key ); ?>'>
		<?php echo esc_html( $name ); ?>
	</label>
	
	<select name = '<?php echo esc_attr( $key ); ?>'>
		<option value = '-'>-</option>
		<?php foreach ( $sorted_fields as $field ) : ?>
			<option value = "<?php echo esc_attr( $field['id'] ); ?>" <?php echo ( $selected === $field['id'] ) ? 'selected' : ''; ?>>
				<?php echo esc_html( $field['name'] ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</div>
