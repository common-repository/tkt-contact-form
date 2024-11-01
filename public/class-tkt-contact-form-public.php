<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.tukutoi.com/
 * @since      1.0.0
 *
 * @package    Tkt_Contact_Form
 * @subpackage Tkt_Contact_Form/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, registers styles and scripts.
 * Creates ShortCode for the Contact form and Handles Email Sending.
 *
 * @package    Tkt_Contact_Form
 * @subpackage Tkt_Contact_Form/public
 * @author     Your Name <hello@tukutoi.com>
 */
class Tkt_Contact_Form_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The unique prefix of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of this plugin.
	 */
	private $plugin_prefix;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name      The name of the plugin.
	 * @param      string $plugin_prefix          The unique prefix of this plugin.
	 * @param      string $version          The version of this plugin.
	 */
	public function __construct( $plugin_name, $plugin_prefix, $version ) {

		$this->plugin_name   = $plugin_name;
		$this->plugin_prefix = $plugin_prefix;
		$this->version = $version;

		$this->form_fields = array(
			'eman_dleif'        => '',
			'liame_dleif'       => '',
			'tcejbus_dleif'     => '',
			'egassem_dleif'     => '',
			'submit'            => '',
			'_wpnonce'          => '',
			'_wp_http_referer'  => '',
			'error_empty'       => '',
			'error_noemail'     => '',
			'success'           => '',
			'id'                => '',
		);

		$this->required_fields = array(
			'eman_dleif'    => '',
			'liame_dleif'   => '',
			'tcejbus_dleif' => '',
			'egassem_dleif' => '',
		);

		$this->honeypot_fields = array(
			'name'    => '',
			'email'   => '',
			'subject' => '',
			'message' => '',
		);

		$this->error = array(
			'eman_dleif'    => '',
			'liame_dleif'   => '',
			'tcejbus_dleif' => '',
			'egassem_dleif' => '',
		);

		$this->send_email_response = array(
			'result'    => null,
		);

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tkt-contact-form-public.css', array(), $this->version, 'screen' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tkt-contact-form-public.js', array( 'jquery' ), $this->version, true );

	}

	/**
	 * The Contact Form ShortCode
	 *
	 * @see https://developer.wordpress.org/plugins/shortcodes/enclosing-shortcodes/
	 *
	 * @since    1.0.0
	 * @param    array  $atts    ShortCode Attributes.
	 * @param    mixed  $content ShortCode enclosed content.
	 * @param    string $tag    The Shortcode tag.
	 */
	public function contact_form( $atts, $content = null, $tag ) {

		$atts = shortcode_atts(
			array(
				'id'            => 1,
				'subject'       => '',
				'label_name'    => 'Your Name',
				'label_email'   => 'Your E-mail Address',
				'label_subject' => 'Subject',
				'label_message' => 'Your Message',
				'label_submit'  => 'Submit',
				'error_empty'   => 'Please fill in all the required fields.',
				'error_noemail' => 'Please enter a valid e-mail address.',
				'success'       => "Thanks for your e-mail! We'll get back to you as soon as we can.",
			),
			$atts,
			$tag
		);

		// Sanitize/Validate ShortCode attribute values.
		$atts = $this->sanitize_atts( $atts );

		// Enqueue scripts and styles only when ShortCode is used.
		$this->enqueue_on_demand();

		// Get the HTML Form or/and Message.
		$contact_form = $this->form( $this->send_email_response, $atts, $this->form_fields );

		// Display Success Message if send form successful, or error + form if failure.
		if ( isset( $_GET['success'] ) && 'true' === $_GET['success'] ) {
			return $contact_form['info'];
		} else {
			return $contact_form['info'] . $contact_form['form'];
		}

	}

	/**
	 * Build the HTML Form
	 */
	public function handle_contact_form() {

		// Validate POSTed data.
		// Check Nonce and $_POSTed data.
		if ( empty( $_POST )
			|| ! isset( $_POST['submit'] )
			|| ! isset( $_REQUEST['_wpnonce'] )
			|| ( isset( $_REQUEST['_wpnonce'] )
				&& false === wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'tkt_cntct_frm_nonce' )
			)
		) {
			return $this->send_email_response;
		}

		$posted_data = $this->validate_posted_data( $_POST );

		// Sanitize POSTed Data.
		$this->sanitize_posted_data( $posted_data );

		// Validate inputs.
		$errors = $this->error_validation();

		if ( false === $errors['error'] ) {

			/**
			 * Get Visible Form Input Fields.
			 * Form Fields already sanitized.
			 *
			 * @see sanitize_posted_data.
			 */
			$form_name = $this->form_fields['eman_dleif'];
			$form_email = $this->form_fields['liame_dleif'];
			$form_subject = $this->form_fields['tcejbus_dleif'];
			$form_message = $this->form_fields['egassem_dleif'];

			/**
			 * Get hidden Form Data.
			 */
			$form_ip = sanitize_text_field( $this->get_the_ip() );

			/**
			 * Build Email Data.
			 */
			$receiver = sanitize_email( apply_filters( 'tkt_cntct_frm_email', get_bloginfo( 'admin_email' ), $this->form_fields['id'] ) );
			$from = sanitize_text_field( apply_filters( 'tkt_cntct_frm_from', get_bloginfo( 'name' ), $this->form_fields['id'] ) );
			$subject = sanitize_text_field( apply_filters( 'tkt_cntct_frm_internal_subject', __( 'New Contact Initiated', 'tkt-contact-form' ), $this->form_fields, $receiver ) );

			// Get headers.
			$headers = $this->build_header_data( $from, $form_email, $receiver );

			// Apply filters to, and sanitize (filtered) Form Data.
			$form_subject = sanitize_text_field( apply_filters( 'tkt_cntct_frm_subject', $form_subject, $this->form_fields, $receiver ) );
			$form_message = wp_kses_post( apply_filters( 'tkt_cntct_frm_message', $form_message, $this->form_fields, $receiver ) );
			$form_ip_string = '<p>IP: ' . sanitize_text_field( $form_ip ) . '</p>';
			$form_ip_string = wp_kses_post( apply_filters( 'tkt_cntct_frm_ip', $form_ip_string, $this->form_fields['id'] ) );

			// Build Email Body for notification.
			$email_body = '<p>' . esc_html__( 'Subject: ', 'tkt-contact-form' ) . $form_subject . '</p>' . $form_message . '<p>' . esc_html__( 'Contact Email: ', 'tkt-contact-form' ) . $form_email . '</p><p>' . esc_html__( 'Contact Name: ', 'tkt-contact-form' ) . $form_name . '</p>' . $form_ip_string;

			// Build Email Body for Confirmation.
			$confirmation_message = esc_html( apply_filters( 'tkt_cntct_frm_confirmation_message', __( 'We have received your message and will reply soon. For the records, this was your message:', 'tkt-contact-form' ), $this->form_fields['id'] ) );
			$confirmation_message = $confirmation_message . '<p>' . $form_message . '</p>';

			// Wether to send confirmation.
			$send_confirmation = boolval( apply_filters( 'tkt_cntct_frm_send_confirmation', true ) );

			// Action fired just before email is sent.
			do_action( 'tkt_cntct_frm_pre_send_mail', $this->form_fields );

			// Send Email to host.
			wp_mail( $receiver, $subject, $email_body, $headers['notification'] );

			// Send Email to prospect.
			if ( true === $send_confirmation ) {
				wp_mail( $form_email, $form_subject, $confirmation_message, $headers['confirmation'] );
			}

			// Action fired just after email is sent.
			do_action( 'tkt_cntct_frm_post_send_mail', sanitize_email( $receiver ), $form_subject, $email_body, $headers['notification'], $this->form_fields );

			if ( $_SERVER && isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {

				// Build redirect URL.
				$redirect_url  = is_ssl() ? 'https://' : 'http://';

				/**
				 * We can NOT escape or sanitize an URL here at this point! False WPCS alarm.
				 * If we do sanitize/escape, since we do not have a protocol yet prepended, esc_url_raw will fallback to HTTP.
				 */
				$redirect_url .= wp_unslash( $_SERVER['HTTP_HOST'] );
				$redirect_url .= wp_unslash( $_SERVER['REQUEST_URI'] );
				$redirect_url = $redirect_url . '?success=true';
			} else {
				$redirect_url = get_home_url();
			}
			// Apply filter to change the Redirect URL.
			$redirect_url = esc_url_raw( apply_filters( 'tkt_cntct_frm_redirect_uri', $redirect_url, $this->form_fields['id'] ) );

			// Action just before redirect happens.
			do_action( 'tkt_cntct_frm_pre_redirect', $redirect_url, $this->form_fields['id'] );

			// Safe redirect.
			wp_safe_redirect( $redirect_url );

			// Action just after redirect happend.
			do_action( 'tkt_cntct_frm_post_redirect' );

			exit;

		}

		// If the Form was not filled or invalid data provided, update the response.
		$this->send_email_response = array(
			'result'    => $errors['result'],
		);

	}

	/**
	 * Enqueue Scripts and Styles on Demand.
	 */
	private function enqueue_on_demand() {

		wp_enqueue_style( $this->plugin_name );
		wp_enqueue_script( $this->plugin_name );

	}

	/**
	 * Sanitize/Validate the ShortCode attributes.
	 * At the moment only for Email and Text/Area fields.
	 *
	 * @param array $atts The ShortCode Attributes.
	 */
	private function sanitize_atts( $atts ) {

		foreach ( $atts as $key => $value ) {
			if ( 'email' === $key ) {
				$atts[ $key ] = sanitize_email( $value );
			} else {
				$atts[ $key ] = sanitize_text_field( $value );
			}
		}

		return $atts;

	}

	/**
	 * Remove HoneyPot fields and Validate POSTed Data.
	 *
	 * @param array $posted_data The POSTed data.
	 * @return array $posted_data|empty array The POSTed data without HoneyPot with only whitelisted Members, or empty array.
	 */
	private function validate_posted_data( $posted_data ) {

		/**
		 * Unset fake HoneyPot data.
		 */
		foreach ( $this->honeypot_fields as $field => $value ) {
			unset( $posted_data[ $field ] );
		}

		/**
		 * If at this point there are any other fields in $_POST something is sketchy, thus abort.
		 * We expect an empty array (no difference).
		 */
		$diff = array_diff_key( $posted_data, $this->form_fields );

		if ( ! empty( $diff ) ) {
			return array();
		}

		return $posted_data;

	}

	/**
	 * Sanitize the POSTed data.
	 *
	 * @param array $posted_data the POSTed data.
	 */
	private function sanitize_posted_data( $posted_data ) {
		// fetch everything that has been POSTed, sanitize.
		foreach ( $posted_data as $field => $value ) {

			if ( 'liame_dleif' === $field ) {
				$value = sanitize_email( $value );
			} elseif ( 'egassem_dleif' === $field ) {
				$value = sanitize_textarea_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}

			$this->form_fields[ $field ] = $value;

		}
	}

	/**
	 * Validate inputs and return errors.
	 *
	 * @return array $errors Wether error is true, and error messages.
	 */
	private function error_validation() {

		$errors = array(
			'error' => false,
			'result' => '',
		);

		// if the required fields are empty, switch $error to TRUE and set the result text to the shortcode attribute named 'error_empty'.
		foreach ( $this->required_fields as $required_field => $value ) {

			$value = trim( $this->form_fields[ $required_field ] );

			if ( empty( $value ) ) {
				$this->error[ $required_field ] = 'tkt-missing-or-invalid';
				$errors['error'] = true;
				$errors['result'] = $this->form_fields['error_empty'];
			}
		}

		// if the e-mail is not valid or missing, switch $error to TRUE and set the result text to the shortcode attribute named 'error_noemail'.
		if ( ! is_email( $this->form_fields['liame_dleif'] ) ) {
			$this->error['liame_dleif'] = 'tkt-missing-or-invalid';
			$errors['error'] = true;
			$errors['result'] = $this->form_fields['error_noemail'];
		}

		return $errors;
	}

	/**
	 * Build Email Headers,
	 *
	 * @param string $from The "From" Name Header attribute of the notification mail. Defaults to BlogName.
	 * @param email  $form_email The Prospect's email as added in the Form Email Field.
	 * @param email  $receiver The "From" Email Header attribute of the notification mail. Defaults to Blog Admin Email.
	 */
	private function build_header_data( $from, $form_email, $receiver ) {

		$headers = array(
			'notification' => '',
			'confirmation' => '',
		);

		/**
		 * Build header data.
		 */
		$headers['notification']  = 'From: ' . sanitize_text_field( $from ) . ' <' . sanitize_email( $receiver ) . ">\n";
		$headers['notification'] .= 'Reply-To: <' . sanitize_email( $form_email ) . '>' . "\r\n";
		$headers['notification'] .= "Content-Type: text/html; charset=UTF-8\n";
		$headers['notification'] .= "Content-Transfer-Encoding: 8bit\n";

		$headers['confirmation'] = 'From: ' . sanitize_text_field( $from ) . ' <' . sanitize_email( $receiver ) . ">\n";
		$headers['confirmation'] .= 'Reply-To: <' . sanitize_email( $receiver ) . '>' . "\r\n";
		$headers['confirmation'] .= "Content-Type: text/html; charset=UTF-8\n";
		$headers['confirmation'] .= "Content-Transfer-Encoding: 8bit\n";

		return $headers;

	}

	/**
	 * Try to fetch the User's real IP
	 */
	private function get_the_ip() {
		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		} else {
			return 'Could not detect the IP';
		}
	}

	/**
	 * Build the HTML Form
	 *
	 * @param array $send_email_response The response of the send_mail function.
	 * @param array $atts The ShortCode attributes.
	 * @param array $form_data The Form POST Data.
	 */
	private function form( $send_email_response, $atts, $form_data ) {

		$info = '';

		// Note: $atts is already sanitized in ShortCode. Nonetheless we escape it again.
		if ( ! empty( $send_email_response['result'] ) ) {
			$info = '<div class="tkt-error">' . esc_textarea( $send_email_response['result'] ) . '</div>';
		} elseif ( isset( $_GET['success'] ) && 'true' === $_GET['success'] ) {
			$info = '<div class="tkt-success">' . esc_textarea( $atts['success'] ) . '</div>';
		}

		/**
		 * Build Form HTML.
		 * NOTE: Real Field Name/ID are mirrored. Non mirrored field names and IDs signify Bot Fields (honeypot)
		 */
		$form = '<form class="tkt-contact-form" method="post" action="' . esc_url_raw( get_permalink() ) . '" id="' . esc_attr( $atts['id'] ) . '">
		    <div>
		        <label for="eman_dleif">' . esc_html( $atts['label_name'] ) . ':</label>
		        <input type="text" class="' . esc_attr( $this->error['eman_dleif'] ) . '" name="eman_dleif" id="eman_dleif" size="50" maxlength="50" value="' . esc_attr( $form_data['eman_dleif'] ) . '" />
		    </div>
		    <div>
		        <label for="liame_dleif">' . esc_html( $atts['label_email'] ) . ':</label>
		        <input type="text" class="' . esc_attr( $this->error['liame_dleif'] ) . '" name="liame_dleif" id="liame_dleif" value="' . esc_attr( $form_data['liame_dleif'] ) . '" />
		    </div>
		    <div>
		        <label for="tcejbus_dleif">' . esc_html( $atts['label_subject'] ) . ':</label>
		        <input type="text" class="' . esc_attr( $this->error['tcejbus_dleif'] ) . '" name="tcejbus_dleif" id="tcejbus_dleif" size="50" maxlength="50" value="' . esc_attr( $form_data['tcejbus_dleif'] ) . '" />
		    </div>
		    <div>
		        <label for="egassem_dleif">' . esc_html( $atts['label_message'] ) . ':</label>
		        <textarea class="' . esc_attr( $this->error['egassem_dleif'] ) . '" name="egassem_dleif" id="egassem_dleif" cols="50" rows="15">' . esc_textarea( $form_data['egassem_dleif'] ) . '</textarea>
		    </div>

			<label class="tkt-ohnohoney" for="name"></label>
		    <input class="tkt-ohnohoney" autocomplete="off" type="text" name="name" id="name" />
		    <label class="tkt-ohnohoney" for="email"></label>
		    <input class="tkt-ohnohoney" autocomplete="off" type="email" name="email" id="email" />
		    <label class="tkt-ohnohoney" for="subject"></label>
		    <input class="tkt-ohnohoney" autocomplete="off" type="text" name="subject" id="subject" />
		    <label class="tkt-ohnohoney" for="message"></label>
		    <textarea class="tkt-ohnohoney" autocomplete="off" name="message" id="message"></textarea>

		    <div>
		        <input type="submit" value="' . esc_attr( $atts['label_submit'] ) . '" name="submit" id="submit" />
		    </div>

		    <input type="hidden" id="error_empty" name="error_empty" value="' . esc_attr( $atts['error_empty'] ) . '">
		    <input type="hidden" id="error_noemail" name="error_noemail" value="' . esc_attr( $atts['error_noemail'] ) . '">
		    <input type="hidden" id="success" name="success" value="' . esc_attr( $atts['success'] ) . '">
		    <input type="hidden" id="id" name="id" value="' . esc_attr( $atts['id'] ) . '">

		    ' . wp_nonce_field( 'tkt_cntct_frm_nonce', '_wpnonce', true, false ) . '

		</form>';

		$contact_form = array(
			'info'  => $info,
			'form'  => $form,
		);

		return $contact_form;

	}

}
