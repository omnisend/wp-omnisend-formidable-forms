<?php
/**
 * Omnisend Api service
 *
 * @package OmnisendFormidableFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\FormidableFormsAddon\Service;

use Omnisend\FormidableFormsAddon\Actions\OmnisendAddOnAction;
use Omnisend\FormidableFormsAddon\Mapper\FormFieldsMapper;
use Omnisend\FormidableFormsAddon\Mapper\ContactMapper;
use Omnisend\FormidableFormsAddon\Validator\ResponseValidator;
use Omnisend\SDK\V1\Omnisend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Omnisend API Service.
 */
class OmnisendApiService {
	/**
	 * Form fields mapper.
	 *
	 * @var FormFieldsMapper
	 */
	private $fields_mapper;

	/**
	 * Contact mapper.
	 *
	 * @var ContactMapper
	 */
	private $contact_mapper;

	/**
	 * Response validator
	 *
	 * @var ResponseValidator
	 */
	private $response_validator;

	/**
	 * Omnisend client
	 *
	 * @var Omnisend
	 */
	private $client;

	/**
	 * OmnisendApiService class constructor.
	 */
	public function __construct() {
		$this->fields_mapper      = new FormFieldsMapper();
		$this->contact_mapper     = new ContactMapper();
		$this->response_validator = new ResponseValidator();
		$this->client             = Omnisend::get_client(
			OMNISEND_FORMIDABLE_ADDON_NAME,
			OMNISEND_FORMIDABLE_ADDON_VERSION
		);
	}

	/**
	 * Creates an Omnisend contact.
	 *
	 * @param string $form_name The form name.
	 * @param int    $form_id The action settings.
	 * @param array  $form_data The form data.
	 *
	 * @return array Tracker data.
	 */
	public function create_omnisend_contact( string $form_name, int $form_id, array $form_data ): array {
		$mapper_fields_values = $this->fields_mapper->get_field_mappings( $form_id, $form_data );

		if ( $mapper_fields_values[ OmnisendAddOnAction::EMAIL ] == null ) {
			error_log('Omnisend error: email not mapped/submitted'); // phpcs:ignore

			return array();
		}

		$contact  = $this->contact_mapper->get_omnisend_contact( $mapper_fields_values, $form_name );
		$response = $this->client->create_contact( $contact, $form_name );

		if ( ! $this->response_validator->is_valid( $response ) ) {
			return array();
		}

		return array(
			OmnisendAddOnAction::EMAIL        => $mapper_fields_values[ OmnisendAddOnAction::EMAIL ],
			OmnisendAddOnAction::PHONE_NUMBER => $mapper_fields_values[ OmnisendAddOnAction::PHONE_NUMBER ],
		);
	}
}
