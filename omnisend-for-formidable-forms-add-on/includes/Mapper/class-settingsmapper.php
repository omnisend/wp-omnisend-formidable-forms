<?php
/**
 * Omnisend setting mapper
 *
 * @package OmnisendFormidableFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\FormidableFormsAddon\Mapper;

use Omnisend\FormidableFormsAddon\Actions\OmnisendAddOnAction;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SettingsMapper
 */
class SettingsMapper {
	private const MAPPING_FIELDS = array(
		OmnisendAddOnAction::EMAIL         => array( 'email' ),
		OmnisendAddOnAction::PHONE_NUMBER  => array( 'phone' ),
		OmnisendAddOnAction::EMAIL_CONSENT => array( 'checkbox' ),
		OmnisendAddOnAction::PHONE_CONSENT => array( 'checkbox' ),
		OmnisendAddOnAction::FIRST_NAME    => array( 'name' ),
		OmnisendAddOnAction::BIRTHDAY      => array( 'text', 'date' ),
		OmnisendAddOnAction::ADDRESS       => array( 'text', 'address' ),
		OmnisendAddOnAction::CITY          => array( 'text', 'address' ),
		OmnisendAddOnAction::STATE         => array( 'text', 'address' ),
		OmnisendAddOnAction::COUNTRY       => array( 'text', 'address' ),
		OmnisendAddOnAction::POSTAL_CODE   => array( 'text', 'address' ),
	);

	/**
	 * Get field type mappings
	 *
	 * @param string $key field key.
	 *
	 * @return array field mappings.
	 */
	private function get_setting_mapping( string $key ): array {
		if ( array_key_exists( $key, self::MAPPING_FIELDS ) ) {
			return self::MAPPING_FIELDS[ $key ];
		}

		return array();
	}

	/**
	 * Get fields to print
	 *
	 * @param  array  $form_fields form fields.
	 * @param  string $key omnisend field.
	 *
	 * @return array mappings
	 */
	public function get_data( array $form_fields, string $key ): array {
		$mapped = $this->get_setting_mapping( $key );

		if ( empty( $mapped ) ) {
			return $form_fields;
		}

		$mappings = array();

		foreach ( $form_fields as $field ) {
			if ( in_array( $field['type'], $mapped, true ) ) {
				$mappings[] = $field;
			}
		}

		return $mappings;
	}
}
