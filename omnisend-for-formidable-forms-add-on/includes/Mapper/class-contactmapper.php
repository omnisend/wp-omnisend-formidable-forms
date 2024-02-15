<?php
/**
 * Contact mapper
 *
 * @package OmnisendFormidableFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\FormidableFormsAddon\Mapper;

use Omnisend\FormidableFormsAddon\Actions\OmnisendAddOnAction;
use Omnisend\SDK\V1\Contact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ContactMapper
 */
class ContactMapper {
	private const CUSTOM_PREFIX  = 'formidable_forms';
	private const CONSENT_PREFIX = 'formidable-forms';
	private const CUSTOM_FIELDS  = 'customFields';
	private const DATE_FORMAT    = 'Y-m-d\Th:i:s\Z';

	/**
	 * Get Contact object
	 *
	 * @param array  $mapped_fields
	 * @param string $form_name
	 *
	 * @return Contact object
	 */
	public function get_omnisend_contact( array $mapped_fields, string $form_name ): Contact {
		$contact = new Contact();
		$contact->set_email( $mapped_fields[ OmnisendAddOnAction::EMAIL ] );
		$contact->set_phone( $mapped_fields[ OmnisendAddOnAction::PHONE_NUMBER ] );
		$contact->set_first_name( $mapped_fields[ OmnisendAddOnAction::FIRST_NAME ] ?? '' );
		$contact->set_last_name( $mapped_fields[ OmnisendAddOnAction::LAST_NAME ] ?? '' );
		$contact->set_birthday( $mapped_fields[ OmnisendAddOnAction::BIRTHDAY ] ?? '' );
		$contact->set_postal_code( $mapped_fields[ OmnisendAddOnAction::POSTAL_CODE ] ?? '' );
		$contact->set_address( $mapped_fields[ OmnisendAddOnAction::ADDRESS ] ?? '' );
		$contact->set_state( $mapped_fields[ OmnisendAddOnAction::STATE ] ?? '' );
		$contact->set_country( $mapped_fields[ OmnisendAddOnAction::COUNTRY ] ?? '' );
		$contact->set_city( $mapped_fields[ OmnisendAddOnAction::CITY ] ?? '' );

		if ( isset( $mapped_fields['sendWelcomeEmail'] ) ) {
			$contact->set_welcome_email( true );
		}

		if ( isset( $mapped_fields[ OmnisendAddOnAction::EMAIL_CONSENT ] ) ) {
			$contact->set_email_consent( self::CONSENT_PREFIX );
			$contact->set_email_opt_in( gmdate( self::DATE_FORMAT ) );
		}

		if ( isset( $mapped_fields[ OmnisendAddOnAction::PHONE_CONSENT ] ) ) {
			$contact->set_phone_consent( self::CONSENT_PREFIX );
			$contact->set_phone_opt_in( gmdate( self::DATE_FORMAT ) );
		}

		if ( array_key_exists( self::CUSTOM_FIELDS, $mapped_fields ) ) {
			foreach ( $mapped_fields[ self::CUSTOM_FIELDS ] as $key => $value ) {
				$contact->add_custom_property( $key, $value );
			}
		}

		$contact->add_tag( self::CUSTOM_PREFIX );
		$contact->add_tag( self::CUSTOM_PREFIX . ' ' . $form_name );

		return $contact;
	}
}
