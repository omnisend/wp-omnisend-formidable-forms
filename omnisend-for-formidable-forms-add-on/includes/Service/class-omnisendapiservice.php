<?php
/**
 * Omnisend Api service
 *
 * @package OmnisendFormidableFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\FormidableFormsAddon\Service;

use Omnisend\FormidableFormsAddon\Actions\OmnisendAddOnAction;
use Omnisend\FormidableFormsAddon\Builder\RequestBodyBuilder;
use Omnisend\FormidableFormsAddon\Client\OmnisendApiClient;
use Omnisend\FormidableFormsAddon\Factory\OmnisendResponseFactory;
use Omnisend\FormidableFormsAddon\Mapper\FormFieldsMapper;
use Omnisend\FormidableFormsAddon\OmnisendResponse;
use Omnisend\FormidableFormsAddon\Validator\ResponseValidator;

/**
 * Omnisend API Service.
 */
class OmnisendApiService {

	/**
	 * Omnisend API client.
	 *
	 * @var OmnisendApiClient
	 */
	private $client;

	/**
	 * Form fields mapper.
	 *
	 * @var FormFieldsMapper
	 */
	private $fields_mapper;

	/**
	 * Request body builder.
	 *
	 * @var RequestBodyBuilder
	 */
	private $body_builder;

	/**
	 * Response factory
	 *
	 * @var OmnisendResponseFactory
	 */
	private $response_factory;

	/**
	 *  Response validator.
	 *
	 * @var ResponseValidator
	 */
	private $response_validator;

	/**
	 * OmnisendApiService class constructor.
	 */
	public function __construct() {
		$this->fields_mapper      = new FormFieldsMapper();
		$this->client             = new OmnisendApiClient();
		$this->body_builder       = new RequestBodyBuilder();
		$this->response_factory   = new OmnisendResponseFactory();
		$this->response_validator = new ResponseValidator();
	}

	/**
	 * Creates an Omnisend contact.
	 *
	 * @param string $form_name The form name.
	 * @param int    $form_id The action settings.
	 * @param array  $form_data The form data.
	 *
	 * @return OmnisendResponse The Omnisend response.
	 */
	public function create_omnisend_contact( string $form_name, int $form_id, array $form_data ): OmnisendResponse {
		$mapped_fields_values = $this->fields_mapper->get_field_mappings( $form_id, $form_data );
		$body                 = $this->body_builder->get_body( $mapped_fields_values, $form_name );

		if ( empty( $body ) ) {
			return $this->response_factory->create( false );
		}

		$response = $this->client->create_omnisend_contact( $body );

		if ( ! $this->response_validator->validate_response( $response ) ) {
			return $this->response_factory->create( false );
		}

		return $this->response_factory->create(
			true,
			$mapped_fields_values[ OmnisendAddOnAction::EMAIL ],
			$mapped_fields_values[ OmnisendAddOnAction::PHONE_NUMBER ]
		);
	}
}
