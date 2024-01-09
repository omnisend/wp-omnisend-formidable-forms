<?php
/**
 * Form fields mapper
 *
 * @package OmnisendFormidableFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\FormidableFormsAddon\Mapper;

use Omnisend\FormidableFormsAddon\Actions\OmnisendAddOnAction;
use Omnisend\FormidableFormsAddon\Provider\OmnisendActionSettingsProvider;
use FrmField;

/**
 * Class FormFieldsMapper
 */
class FormFieldsMapper {
	private const IGNORE_FIELDS   = array(
		'html',
		'credit_card',
		'password',
	);
	private const ADDRESS_MAPPING = array(
		OmnisendAddOnAction::ADDRESS     => 'line1',
		OmnisendAddOnAction::CITY        => 'city',
		OmnisendAddOnAction::STATE       => 'state',
		OmnisendAddOnAction::COUNTRY     => 'country',
		OmnisendAddOnAction::POSTAL_CODE => 'zip',
	);
	private const DEFAULT_VALUES  = array(
		OmnisendAddOnAction::EMAIL        => '',
		OmnisendAddOnAction::PHONE_NUMBER => '',
	);

	/**
	 * Get field mappings.
	 *
	 * @param int   $form_id Form id.
	 * @param array $form_fields Form fields.
	 *
	 * @return array Field mappings.
	 */
	public function get_field_mappings( int $form_id, array $form_fields ): array {
		$consent_fields = array( OmnisendAddOnAction::EMAIL_CONSENT, OmnisendAddOnAction::PHONE_CONSENT );
		$values         = self::DEFAULT_VALUES;

		foreach ( OmnisendAddOnAction::OMNISEND_FIELDS as $key => $label ) {
			$field_key = OmnisendActionSettingsProvider::get_setting_key_by_form_id( $key, $form_id );
			$field_key = get_option( $field_key, null );

			if ( null === $field_key || '-' === $field_key ) {
				continue;
			}

			if ( ! isset( $form_fields[ $field_key ] ) ) {
				continue;
			}

			if ( in_array( $key, $consent_fields, true ) ) {
				if ( is_array( $form_fields[ $field_key ] ) ) {
					$values[ $key ] = 'subscribed';
				} else {
					$values[ $key ] = 'nonSubscribed';
				}

				unset( $form_fields[ $field_key ] );

				continue;
			}

			if ( OmnisendAddOnAction::BIRTHDAY === $key &&
				! empty( $form_fields[ $field_key ] ) &&
				strtotime( $form_fields[ $field_key ] )
			) {
				$values[ $key ] = gmdate( 'Y-m-d', strtotime( $form_fields[ $field_key ] ) );
				unset( $form_fields[ $field_key ] );
				continue;
			}

			if ( OmnisendAddOnAction::FIRST_NAME === $key && is_array( $form_fields[ $field_key ] ) ) {
				if ( array_key_exists( 'first', $form_fields[ $field_key ] ) ) {
					$values[ OmnisendAddOnAction::FIRST_NAME ] = $form_fields[ $field_key ]['first'];
				}

				if ( array_key_exists( 'last', $form_fields[ $field_key ] ) ) {
					$values[ OmnisendAddOnAction::LAST_NAME ] = $form_fields[ $field_key ]['last'];
				}

				unset( $form_fields[ $field_key ] );
				continue;
			}

			if ( array_key_exists( $key, self::ADDRESS_MAPPING ) && is_array( $form_fields[ $field_key ] ) ) {
				if ( array_key_exists( self::ADDRESS_MAPPING[ $key ], $form_fields[ $field_key ] ) ) {
					$values[ $key ] = $form_fields[ $field_key ][ self::ADDRESS_MAPPING[ $key ] ];
					unset( $form_fields[ $field_key ][ self::ADDRESS_MAPPING[ $key ] ] );
					continue;
				}

				continue;
			}

			$values[ $key ] = $form_fields[ $field_key ];
			unset( $form_fields[ $field_key ] );
		}

		$welcome_config = get_option( OmnisendActionSettingsProvider::get_enabled_welcome_by_form_id( $form_id ) );

		if (
			isset( $welcome_config ) &&
			'1' === $welcome_config
		) {
			$values['sendWelcomeEmail'] = true;
		}

		if ( is_array( $form_fields ) && ! empty( $form_fields ) ) {
			$values['customFields'] = $this->map_custom_properties( $form_fields, $form_id );
		}

		return $values;
	}

	/**
	 * Map custom properties.
	 *
	 * @param array $form_fields Form fields.
	 * @param int   $form_id Form id.
	 *
	 * @return array Custom properties.
	 */
	private function map_custom_properties( array $form_fields, int $form_id ): array {
		$custom_properties = array();
		$prefix            = 'formidable_forms_';

		foreach ( $form_fields as $key => $field ) {
			$field_label = FrmField::getOne( $key, $form_id );

			if ( ! $field_label ) {
				continue;
			}

			$field_type  = $field_label->type;
			$field_label = $field_label->name;

			if ( in_array( $field_type, self::IGNORE_FIELDS, true ) ) {
				continue;
			}

			$safe_label = str_replace( ' ', '_', $field_label );
			$safe_label = preg_replace( '/[^A-Za-z0-9_]/', '', $safe_label );
			$safe_label = strtolower( $safe_label );

			if ( ! is_array( $field ) ) {
				$custom_properties[ $prefix . $safe_label ] = $field;
				continue;
			}

			if ( empty( $field ) ) {
				continue;
			}

			$selected_labels = array();

			foreach ( $field as $option ) {
				if ( ! is_array( $option ) ) {
					$selected_labels[] = $option;
				}
			}

			$custom_properties[ $prefix . $safe_label ] = $selected_labels;

		}

		return $custom_properties;
	}
}
