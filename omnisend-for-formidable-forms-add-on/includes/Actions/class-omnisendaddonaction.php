<?php
/**
 * Omnisend Addon Action
 *
 * @package OmnisendFormidableFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\FormidableFormsAddon\Actions;

use FrmFormAction;
use FrmForm;
use FrmEntry;
use Omnisend\FormidableFormsAddon\Provider\OmnisendActionSettingsProvider;
use Omnisend\FormidableFormsAddon\Service\OmnisendApiService;
use Omnisend\FormidableFormsAddon\Service\TrackerService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Omnisend Addon
 */
class OmnisendAddOnAction extends FrmFormAction {

	const EMAIL           = 'email';
	const PHONE_NUMBER    = 'phone_number';
	const ADDRESS         = 'address';
	const CITY            = 'city';
	const STATE           = 'state';
	const COUNTRY         = 'country';
	const FIRST_NAME      = 'first_name';
	const LAST_NAME       = 'last_name';
	const BIRTHDAY        = 'birthday';
	const POSTAL_CODE     = 'postal_code';
	const EMAIL_CONSENT   = 'email_consent';
	const PHONE_CONSENT   = 'phone_consent';
	const OMNISEND_FIELDS = array(
		self::EMAIL         => 'Email',
		self::PHONE_NUMBER  => 'Phone Number',
		self::ADDRESS       => 'Address',
		self::CITY          => 'City',
		self::STATE         => 'State',
		self::COUNTRY       => 'Country',
		self::FIRST_NAME    => 'First Name & Last Name',
		self::BIRTHDAY      => 'Birthday',
		self::POSTAL_CODE   => 'Postal Code',
		self::EMAIL_CONSENT => 'Email Consent',
		self::PHONE_CONSENT => 'Phone Consent',
	);

	/**
	 * Omnisend service
	 *
	 * @var OmnisendApiService
	 */
	private $omnisend_service;

	/**
	 * Tracker service
	 *
	 * @var TrackerService
	 */
	private $tracker_service;

	/**
	 * Snippet path
	 *
	 * @var string
	 */
	private $snippet_path;

	/**
	 * Settings Provider
	 *
	 * @var OmnisendActionSettingsProvider
	 */
	private $settings;

	/**
	 * Creating a Action
	 */
	public function __construct() {
		$this->omnisend_service = new OmnisendApiService();
		$this->tracker_service  = new TrackerService();
		$this->settings         = new OmnisendActionSettingsProvider();
		$this->snippet_path     = plugins_url( '/../../js/snippet.js', __FILE__ );

		add_action( 'frm_after_create_entry', array( $this, 'process' ), 10, 2 );
	}

	/**
	 * Process sending contact to omnisend
	 *
	 * @param int $entry_id The settings for the action.
	 * @param int $form_id The ID of the form.
	 *
	 * @return void
	 */
	public function process( int $entry_id, int $form_id ): void {
		$args = FrmEntry::getOne( $entry_id, true );

		if ( ! $args || ! property_exists( $args, 'metas' ) ) {
			return;
		}

		$is_enabled = get_option( $this->settings->get_enabled_code_by_form_id( $form_id ) );
		$args       = $args->metas;

		if ( ! isset( $is_enabled ) || '1' !== $is_enabled || ! isset( $args ) ) {
			return;
		}

		$form_name = FrmForm::getOne( $form_id )->name;

		/**
		 * Response array for tracker.
		 *
		 * @var OmnisendResponse $response
		 */
		$response = $this->omnisend_service->create_omnisend_contact( $form_name, $form_id, $args );

		if ( empty( $response ) ) {
			return;
		}

		$this->tracker_service->enable_web_tracking(
			$response[ self::EMAIL ],
			$response[ self::PHONE_NUMBER ] ?? '',
			$this->snippet_path
		);
	}
}
