<?php
/**
 * Plugin Name: Omnisend for Formidable Forms Add-On
 * Description: A Formidable forms add-on to sync contacts with Omnisend. In collaboration with Omnisnnd for WooCommerce plugin it enables better customer tracking
 * Version: 1.1.6
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
define( 'OMNISEND_FORMIDABLE_ADDON_VERSION', '1.1.6' );

spl_autoload_register( array( 'Omnisend_FormidableFormsAddOn_Bootstrap', 'autoloader' ) );
add_action( 'plugins_loaded', array( 'Omnisend_FormidableFormsAddOn_Bootstrap', 'check_plugin_requirements' ) );
add_action( 'admin_enqueue_scripts', array( 'Omnisend_FormidableFormsAddOn_Bootstrap', 'load_custom_wp_admin_style' ) );

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
