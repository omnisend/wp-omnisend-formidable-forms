<?php
/**
 * Plugin Name: Omnisend for Formidable Forms Add-On
 * Description: A Formidable forms add-on to sync contacts with Omnisend. In collaboration with Omnisnnd for WooCommerce plugin it enables better customer tracking
 * Version: 1.2.1
 * Requires PHP: 7.4
 * Author: Omnisend
 * Author URI: https://www.omnisend.com
 * Developer: Omnisend
 * Developer URI: https://developers.omnisend.com
 * Text Domain: omnisend-for-formidable-forms-add-on
 * ------------------------------------------------------------------------
 * Copyright 2024 Omnisend
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package OmnisendFormidableFormsPlugin
 */

use Omnisend\FormidableFormsAddon\Actions\OmnisendAddOnAction;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OMNISEND_FORMIDABLE_ADDON_NAME', 'Omnisend for Formidable Forms Add-On' );
define( 'OMNISEND_FORMIDABLE_ADDON_VERSION', '1.2.1' );

spl_autoload_register( array( 'Omnisend_FormidableFormsAddOn_Bootstrap', 'autoloader' ) );
add_action( 'plugins_loaded', array( 'Omnisend_FormidableFormsAddOn_Bootstrap', 'check_plugin_requirements' ) );
add_action( 'admin_enqueue_scripts', array( 'Omnisend_FormidableFormsAddOn_Bootstrap', 'load_custom_wp_admin_style' ) );
add_action( 'admin_init', array( 'Omnisend_FormidableFormsAddOn_Bootstrap', 'add_privacy_policy_content' ) );

/**
 * Class Omnisend_FormidableFormsAddOn_Bootstrap
 */
class Omnisend_FormidableFormsAddOn_Bootstrap {

	/**
	 * Register actions for the Omnisend Formidable Forms Add-On.
	 *
	 * @param array $actions The array of actions.
	 *
	 * @return array The modified array of actions.
	 */
	public static function register_actions( $actions ) {
		new OmnisendAddOnAction();

		return $actions;
	}

	/**
	 * Suggests privacy policy text for site administrators, as recommended by the
	 * WordPress privacy policy content API for plugins that collect user data.
	 */
	public static function add_privacy_policy_content() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content =
			'<p>' . esc_html__( 'When you submit a Formidable Forms form on this site, the Omnisend for Formidable Forms Add-On sends the personal data you provide to Omnisend for email and SMS marketing purposes. Depending on how the form is configured, this may include your email address, phone number, first and last name, birthday, address, city, state, country, postal code, your consent choices, and any other form fields you fill in.', 'omnisend-for-formidable-forms-add-on' ) . '</p>' .
			'<p>' . esc_html__( 'This data is transmitted to and stored by Omnisend, a third-party service, and is retained there according to Omnisend’s data retention practices for as long as your contact record exists. The plugin itself does not store this data separately in your WordPress database beyond the standard Formidable Forms entries.', 'omnisend-for-formidable-forms-add-on' ) . '</p>' .
			'<p>' . esc_html__( 'To enable web tracking, the add-on may load an Omnisend tracking snippet that identifies you by your submitted email address or phone number and sets cookies in your browser to track your activity on the site.', 'omnisend-for-formidable-forms-add-on' ) . '</p>' .
			'<p>' . sprintf(
				/* translators: 1: Omnisend Privacy Policy URL, 2: Omnisend Terms of Use URL */
				esc_html__( 'You have the right to request access to, export of, or deletion of your personal data. For details on how Omnisend processes personal data and how to exercise these rights, see Omnisend’s Privacy Policy at %1$s and Terms of Use at %2$s.', 'omnisend-for-formidable-forms-add-on' ),
				'<a href="https://www.omnisend.com/privacy/" target="_blank">https://www.omnisend.com/privacy/</a>',
				'<a href="https://www.omnisend.com/terms" target="_blank">https://www.omnisend.com/terms</a>'
			) . '</p>';

		wp_add_privacy_policy_content( OMNISEND_FORMIDABLE_ADDON_NAME, wp_kses_post( $content ) );
	}

	/**
	 * Autoloader function to load classes dynamically.
	 *
	 * @param string $class_name The name of the class to load.
	 */
	public static function autoloader( $class_name ) {
		$namespace = 'Omnisend\FormidableFormsAddon';

		if ( strpos( $class_name, $namespace ) !== 0 ) {
			return;
		}

		$class       = str_replace( $namespace . '\\', '', $class_name );
		$class_parts = explode( '\\', $class );
		$class_file  = 'class-' . strtolower( array_pop( $class_parts ) ) . '.php';

		$directory = plugin_dir_path( __FILE__ );
		$path      = $directory . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $class_parts ) . DIRECTORY_SEPARATOR . $class_file;

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}

	/**
	 * Check plugin requirements.
	 */
	public static function check_plugin_requirements() {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		$formidable_addon_plugin = 'omnisend-for-formidable-forms-add-on/class-omnisend-formidableformsaddon-bootstrap.php';

		$omnisend_plugin = 'omnisend/class-omnisend-core-bootstrap.php';

		if ( ! file_exists( WP_PLUGIN_DIR . '/' . $omnisend_plugin ) || ! is_plugin_active( $omnisend_plugin ) ) {
			deactivate_plugins( $formidable_addon_plugin );
			add_action( 'admin_notices', array( 'Omnisend_FormidableFormsAddOn_Bootstrap', 'omnisend_notice' ) );

			return;
		}

		$api_key = get_option( 'omni_send_core_api_key', null );

		if ( is_null( $api_key ) ) {
			deactivate_plugins( $formidable_addon_plugin );
			add_action( 'admin_notices', array( 'Omnisend_FormidableFormsAddOn_Bootstrap', 'omnisend_api_key_notice' ) );

			return;
		}

		$formidable_forms_plugin = 'formidable/formidable.php';

		if ( ! file_exists( WP_PLUGIN_DIR . '/' . $formidable_forms_plugin ) || ! is_plugin_active( $formidable_forms_plugin ) ) {
			deactivate_plugins( $formidable_addon_plugin );
			add_action( 'admin_notices', array( 'Omnisend_FormidableFormsAddOn_Bootstrap', 'formidable_forms_notice' ) );
		}

		add_action( 'frm_registered_form_actions', array( 'Omnisend_FormidableFormsAddOn_Bootstrap', 'register_actions' ), 10, 1 );
	}

	/**
	 * Display a notice for the missing Omnisend API key.
	 */
	public static function omnisend_api_key_notice() {
		echo '<div class="error"><p>' . esc_html__( 'Your Omnisend is not configured properly. Please configure it firstly', 'omnisend-for-formidable-forms-add-on' ) . '</p></div>';
	}

	/**
	 * Display a notice for the missing Omnisend Plugin.
	 */
	public static function omnisend_notice() {
		echo '<div class="error"><p>' . esc_html__( 'Plugin Omnisend is deactivated. Please install and activate ', 'omnisend-for-formidable-forms-add-on' ) . '<a href="https://wordpress.org/plugins/omnisend/">' . esc_html__( 'Omnisend plugin.', 'omnisend-for-formidable-forms-add-on' ) . '</a></p></div>';
	}

	/**
	 * Display a notice for the missing Formidable Forms plugin.
	 */
	public static function formidable_forms_notice() {
		echo '<div class="error"><p>' . esc_html__( 'Plugin Omnisend for Formidable Forms Add-On is deactivated. Please install and activate Formidable forms plugin.', 'omnisend-for-formidable-forms-add-on' ) . '</p></div>';
	}

	/**
	 * Loading styles in admin.
	 */
	public static function load_custom_wp_admin_style() {
		wp_register_style( 'omnisend-formidable-forms-addon', plugins_url( 'css/omnisend-formidableforms-addon.css', __FILE__ ), array(), OMNISEND_FORMIDABLE_ADDON_VERSION );
		wp_enqueue_style( 'omnisend-formidable-forms-addon' );
	}
}
