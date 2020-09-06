<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Wprus_Api_Login extends Wprus_Api_Abstract {

	/*******************************************************************
	 * Public methods
	 *******************************************************************/

	public function init_notification_hooks() {
		add_action( 'wp_login', array( $this, 'notify_remote' ), PHP_INT_MIN, 2 );
	}

	public function has_remote_async_actions() {

		return true;
	}

	public function needs_redirect() {
		global $is_safari;

		return (
			(
				self::$browser_support_settings['force_login_logout_strict'] ||
				$is_safari ||
				1 === preg_match( '/iPhone|iPad/', $_SERVER['HTTP_USER_AGENT'] )
			) &&
			! self::$browser_support_settings['force_disable_login_logout_strict']
		);
	}

	public function handle_notification() {
		$result  = false;
		$data    = $this->get_data_get();
		$proceed = true;

		if ( ! $this->validate( $data ) ) {
			Wprus_Logger::log(
				array(
					'message' => __( 'Login action failed - received invalid data.', 'wprus' ),
					'data'    => $data,
				),
				'alert',
				'db_log'
			);

			$proceed = false;
		}

		$data               = $this->sanitize( $data );
		$site               = $this->get_active_site_for_action( $this->endpoint, $data['base_url'] );
		$this->callback_url = isset( $data['callback_url'] ) ? $data['callback_url'] : home_url();

		if ( is_user_logged_in() ) {
			Wprus_Logger::log(
				__( 'Login action failed - a user is already logged in.', 'wprus' ),
				'warning',
				'db_log'
			);

			$proceed = false;
		}

		if ( $site && $proceed ) {
			$user = get_user_by( 'login', $data['username'] );

			if ( $user ) {
				$result = true;

				wp_set_current_user( $user->ID, $data['username'] );
				wprus_set_auth_cookie( $user->ID, $data['remember'] );
				do_action( 'wp_login', $data['username'], $user );
				Wprus_Logger::log(
					sprintf(
						// translators: %1$s is the username, %2$s is the caller
						__( 'Login action - successfully logged in user "%1$s" from %2$s.', 'wprus' ),
						$data['username'],
						$site['url']
					),
					'success',
					'db_log'
				);
			} else {
				Wprus_Logger::log(
					sprintf(
						// translators: %1$s is the username, %2$s is the caller
						__( 'Login action aborted - user "%1$s" from %2$s does not exist locally.', 'wprus' ),
						$data['username'],
						$site['url']
					),
					'warning',
					'db_log'
				);
			}
		} elseif ( ! $site ) {
			Wprus_Logger::log(
				sprintf(
					// translators: %s is the url of the caller
					__( 'Login action failed - incoming login action not enabled for %s', 'wprus' ),
					$data['base_url']
				),
				'alert',
				'db_log'
			);
		}

		return $result;
	}

	public function notify_remote( $user_login, $user ) {

		if ( is_user_logged_in() ) {

			return;
		}

		$this->async_user_id = $user->ID;
		$sites               = $this->settings->get_sites( $this->endpoint, 'outgoing' );

		if ( ! empty( $sites ) ) {
			$remember = filter_input( INPUT_POST, 'rememberme', FILTER_SANITIZE_STRING );

			Wprus_Logger::log(
				sprintf(
					// translators: %s is the username
					__( 'Login action - enqueueing asynchronous actions for username "%s"', 'wprus' ),
					$user_login
				),
				'info',
				'db_log'
			);

			foreach ( $sites as $index => $site ) {
				$this->add_remote_async_action(
					$site['url'],
					array(
						'username' => $user_login,
						'remember' => ( $remember ) ? 1 : 0,
					)
				);
			}
		}
	}

	/*******************************************************************
	 * Protected methods
	 *******************************************************************/

	protected function validate( $data ) {
		$valid =
			parent::validate( $data ) &&
			username_exists( $data['username'] ) &&
			is_numeric( $data['remember'] ) &&
			(
				0 === absint( $data['remember'] ) ||
				1 === absint( $data['remember'] )
			);

		if ( $this->needs_redirect() ) {
			$valid = $valid && isset( $data['callback_url'] ) && filter_var( $data['callback_url'], FILTER_VALIDATE_URL );
		}

		return $valid;
	}

	protected function sanitize( $data ) {
		$data['remember'] = (bool) $data['remember'];

		return $data;
	}

}
