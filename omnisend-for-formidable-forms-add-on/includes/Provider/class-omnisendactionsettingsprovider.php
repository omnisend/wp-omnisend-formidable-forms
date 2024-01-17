<?php
/**
 * Omnisend Action setting provider
 *
 * @package OmnisendFormidableFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\FormidableFormsAddon\Provider;

use Omnisend\FormidableFormsAddon\Actions\OmnisendAddOnAction;
use Omnisend\FormidableFormsAddon\Mapper\SettingsMapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class OmnisendActionSettingsProvider
 */
class OmnisendActionSettingsProvider {
	public const SETTING_PREFIX     = 'omnisend_formidable_addon_';
	public const ENABLE_PREFIX      = '_enabled';
	public const SEND_WELCOME_EMAIL = '_send_welcome_email_checkbox';

	/**
	 * Settings mapper
	 *
	 * @var SettingsMapper
	 */
	protected $settings_mapper;

	/**
	 * Uses hooks.
	 */
	public function __construct() {
		$this->settings_mapper = new SettingsMapper();
		add_filter( 'frm_add_form_settings_section', array( $this, 'create_settings' ), 10, 1 );
		add_filter( 'frm_form_options_before_update', array( $this, 'save_settings' ), 20, 2 );
	}

	/**
	 * Create omnisend tab
	 *
	 * @param array $sections tabs.
	 *
	 * @return array sections
	 */
	public function create_settings( $sections ): array {
		$sections[] = array(
			'name'     => 'Omnisend',
			'anchor'   => 'omnisend_settings',
			'function' => 'show_settings',
			'class'    => $this,
		);

		return $sections;
	}

	/**
	 * Show tab data.
	 *
	 * @param array $values setting fields.
	 *
	 * @return void
	 */
	public function show_settings( $values ): void {
		$fields = array();

		require __DIR__ . '/../Templates/header.php';

		foreach ( OmnisendAddOnAction::OMNISEND_FIELDS as $key => $name ) {
			$sorted_fields = $this->settings_mapper->get_data( $values['fields'], $key );
			$key           = $this::get_setting_key_by_form_id( $key, $values['id'] );
			$selected      = get_option( $key );
			require __DIR__ . '/../Templates/setting.php';
		}
	}

	/**
	 * Setting save
	 *
	 * @param array $options options.
	 * @param array $values values.
	 *
	 * @return array options
	 */
	public function save_settings( $options, $values ): array {
		foreach ( $values as $key => $val ) {
			if ( false !== strpos( $key, self::SETTING_PREFIX ) ) {
				update_option( $key, $val );
			}
		}

		return $options;
	}

	/**
	 * Get enabled setting key
	 *
	 * @param int $form_id form id.
	 *
	 * @return string key.
	 */
	public function get_enabled_code_by_form_id( int $form_id ): string {
		return self::SETTING_PREFIX . $form_id . self::ENABLE_PREFIX;
	}

	/**
	 * Get field setting key
	 *
	 * @param int $field_key field key.
	 * @param int $form_id form id.
	 *
	 * @return string field omnisend key.
	 */
	public static function get_setting_key_by_form_id( $field_key, $form_id ): string {
		return self::SETTING_PREFIX . $field_key . $form_id;
	}

	/**
	 * Get send welcome setting key
	 *
	 * @param int $form_id form id.
	 *
	 * @return string key.
	 */
	public static function get_enabled_welcome_by_form_id( int $form_id ): string {
		return self::SETTING_PREFIX . $form_id . self::SEND_WELCOME_EMAIL;
	}
}
