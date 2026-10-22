<?php
/**
 * Points Earned Email Class
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UPRF_Email_Points_Earned' ) ) :

	/**
	 * UPRF_Email_Points_Earned Class.
	 */
	class UPRF_Email_Points_Earned extends WC_Email {

		/**
		 * User ID.
		 *
		 * @var int
		 */
		public $user_id;

		/**
		 * Points earned.
		 *
		 * @var int
		 */
		public $points;

		/**
		 * Order object.
		 *
		 * @var WC_Order
		 */
		public $order;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'uprf_points_earned';
			$this->customer_email = true;
			$this->title          = __( 'Points Earned', 'unstoppable-points-loyalty' );
			$this->description    = __( 'Points earned emails are sent when customers earn reward points from a purchase.', 'unstoppable-points-loyalty' );
			$this->template_html  = 'emails/points-earned.php';
			$this->template_plain = 'emails/plain/points-earned.php';
			$this->template_base  = UPRF_PLUGIN_DIR . 'templates/';
			$this->placeholders   = array(
				'{customer_name}' => '',
				'{points}'        => '',
				'{points_label}'  => '',
				'{total_points}'  => '',
			);

			parent::__construct();
		}

		/**
		 * Get email subject.
		 *
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'You earned {points} {points_label}!', 'unstoppable-points-loyalty' );
		}

		/**
		 * Get email heading.
		 *
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Congratulations, {customer_name}!', 'unstoppable-points-loyalty' );
		}

		/**
		 * Trigger the email.
		 *
		 * @param int      $user_id User ID.
		 * @param int      $points  Points earned.
		 * @param WC_Order $order   Order object.
		 */
		public function trigger( $user_id, $points, $order ) {
			$this->setup_locale();

			$uprf_user = get_userdata( $user_id );
			if ( ! $uprf_user ) {
				return;
			}

			$this->user_id   = $user_id;
			$this->points    = $points;
			$this->order     = $order;
			$this->recipient = $uprf_user->user_email;

			$uprf_points_label  = UPRF()->get_points_label( $points );
			$uprf_total_points  = UPRF()->manager->get_user_points( $user_id );

			$this->placeholders['{customer_name}'] = $uprf_user->display_name;
			$this->placeholders['{points}']        = number_format_i18n( $points );
			$this->placeholders['{points_label}']  = $uprf_points_label;
			$this->placeholders['{total_points}']  = number_format_i18n( $uprf_total_points );

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Get content HTML.
		 *
		 * @return string
		 */
		public function get_content_html() {
			$uprf_user = get_userdata( $this->user_id );

			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			$user_name = $uprf_user ? $uprf_user->display_name : '';
			if ( empty( $user_name ) && $uprf_user ) {
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$user_name = $uprf_user->first_name;
			}

			$uprf_total_points = UPRF()->manager->get_user_points( $this->user_id );
			$uprf_points_value = UPRF()->manager->calculate_discount( $uprf_total_points );

			return wc_get_template_html(
				$this->template_html,
				array(
					'user_id'         => $this->user_id,
					'user_name'       => $user_name,
					'points_earned'   => $this->points,
					'current_balance' => $uprf_total_points,
					'points_value'    => wc_price( $uprf_points_value ),
					'order_id'        => $this->order ? $this->order->get_id() : 0,
					'order_number'    => $this->order ? $this->order->get_order_number() : '',
					'email_heading'   => $this->get_heading(),
					'sent_to_admin'   => false,
					'plain_text'      => false,
					'email'           => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			$uprf_user = get_userdata( $this->user_id );

			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			$user_name = $uprf_user ? $uprf_user->display_name : '';
			if ( empty( $user_name ) && $uprf_user ) {
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$user_name = $uprf_user->first_name;
			}

			$uprf_total_points = UPRF()->manager->get_user_points( $this->user_id );
			$uprf_points_value = UPRF()->manager->calculate_discount( $uprf_total_points );

			return wc_get_template_html(
				$this->template_plain,
				array(
					'user_id'         => $this->user_id,
					'user_name'       => $user_name,
					'points_earned'   => $this->points,
					'current_balance' => $uprf_total_points,
					'points_value'    => wc_price( $uprf_points_value ),
					'order_id'        => $this->order ? $this->order->get_id() : 0,
					'order_number'    => $this->order ? $this->order->get_order_number() : '',
					'email_heading'   => $this->get_heading(),
					'sent_to_admin'   => false,
					'plain_text'      => true,
					'email'           => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Initialise settings form fields.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'    => array(
					'title'   => __( 'Enable/Disable', 'unstoppable-points-loyalty' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'unstoppable-points-loyalty' ),
					'default' => 'yes',
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'unstoppable-points-loyalty' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => sprintf(
						/* translators: %s: placeholder list */
						__( 'Available placeholders: %s', 'unstoppable-points-loyalty' ),
						'<code>{customer_name}, {points}, {points_label}, {total_points}</code>'
					),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email heading', 'unstoppable-points-loyalty' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => sprintf(
						/* translators: %s: placeholder list */
						__( 'Available placeholders: %s', 'unstoppable-points-loyalty' ),
						'<code>{customer_name}, {points}, {points_label}, {total_points}</code>'
					),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'unstoppable-points-loyalty' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'unstoppable-points-loyalty' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
			);
		}
	}

endif;

return new UPRF_Email_Points_Earned();
