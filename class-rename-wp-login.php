<?php defined( 'ABSPATH' ) || die();

if ( defined( 'ABSPATH' ) && ! class_exists( 'RenameWPLogin' ) ) {

	/**
	 * Rename WP Login Class
	 *
	 * This class handles the renaming of the WordPress login URL.
	 *
	 * @package RenameWPLogin
	 */
	class Rename_WP_Login {

		/**
		 * Defines the replacement login path.
		 *
		 * @var string
		 */
		private $new_login_slug = 'saml/login';

		/**
		 * Whether the current request is to the WordPress default login path.
		 *
		 * @var bool
		 */
		private $wp_login_php;

		/**
		 * The class constructor. Registers our functions in WP hooks.
		 */
		public function __construct() {
			register_uninstall_hook( plugin_basename( __FILE__ ), array( 'Rename_WP_Login', 'uninstall' ) );
			if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 1 );
			add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
			add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
			add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );
			add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );
			add_filter( 'site_option_welcome_email', array( $this, 'welcome_email' ) );
			remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
		}

		/**
		 * Loads the correct template based on the visitor's url.
		 */
		private function wp_template_loader() {
			global $pagenow;
			$pagenow = 'index.php';
			if ( ! defined( 'WP_USE_THEMES' ) ) {
				define( 'WP_USE_THEMES', true );
			}
			wp();
			if ( $_SERVER['REQUEST_URI'] === trailingslashit( str_repeat( '-/', 10 ) ) ) {
				$_SERVER['REQUEST_URI'] = trailingslashit( '/wp-login-php/' );
			}
			require_once ABSPATH . WPINC . '/template-loader.php';
			die;
		}

		/**
		 * Returns the site's new login URL.
		 *
		 * @return string
		 */
		public function new_login_url() {
			return home_url( '/', 'https' ) . trailingslashit( $this->new_login_slug );
		}

		/**
		 * Callback for uninstallation. Ensures redirects are flushed.
		 */
		public static function uninstall() {
			global $wpdb;
			if ( is_multisite() ) {
				flush_rewrite_rules();
				$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
				if ( $blogs ) {
					foreach ( $blogs as $blog ) {
						switch_to_blog( $blog['blog_id'] );
						flush_rewrite_rules();
						restore_current_blog();
					}
				}
			} else {
				flush_rewrite_rules();
			}
		}

		/**
		 * Plugins are loaded. Evaluate whether the current page is login.
		 */
		public function plugins_loaded() {

			global $pagenow;

			if (
				! is_multisite()
				&& ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-signup' ) !== false
					|| strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-activate' ) !== false )
			) {

				wp_die( __( 'This feature is not enabled.', 'rename-wp-admin-login' ) );
			}

			$request = wp_parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );

			if ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-login.php' ) !== false
					|| ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) ) )
				&& ! is_admin()
			) {
				$this->wp_login_php     = true;
				$_SERVER['REQUEST_URI'] = trailingslashit( '/' . str_repeat( '-/', 10 ) );
				$pagenow                = 'index.php';
			} elseif ( ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === home_url( $this->new_login_slug, 'relative' ) ) ) {
				$pagenow = 'wp-login.php';
			} elseif ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-register.php' ) !== false
					|| ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) ) )
				&& ! is_admin()
			) {
				$this->wp_login_php     = true;
				$_SERVER['REQUEST_URI'] = trailingslashit( '/' . str_repeat( '-/', 10 ) );
				$pagenow                = 'index.php';
			}
		}

		/**
		 * WordPress has bootstrapped. If the request is for a login page, redirect.
		 */
		public function wp_loaded() {
			global $pagenow;
			if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) ) {
				wp_safe_redirect( '/' );
				die();
			}
			//if (is_user_logged_in() && $pagenow === 'wp-login.php') {
			//	wp_safe_redirect('/');
			//	die();
			//}
			if ( $this->wp_login_php ) {
				if (
					( $referer = wp_get_referer() ) &&
					strpos( $referer, 'wp-activate.php' ) !== false &&
					( $referer = wp_parse_url( $referer ) ) &&
					! empty( $referer['query'] )
				) {
					parse_str( $referer['query'], $referer );

					if (
						! empty( $referer['key'] ) &&
						( $result = wpmu_activate_signup( $referer['key'] ) ) &&
						is_wp_error( $result ) && (
							$result->get_error_code() === 'already_active' ||
							$result->get_error_code() === 'blog_taken'
						)
					) {
						wp_safe_redirect( $this->new_login_url() . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
						die;
					}
				}
				$this->wp_template_loader();
			} elseif ( $pagenow === 'wp-login.php' ) {
				global $error, $interim_login, $action, $user_login;
				@require_once ABSPATH . 'wp-login.php';
				die;
			}
		}

		/**
		 * Provide the new URL for the login path including parameters.
		 *
		 * @param string $url The old login URL.
		 * @return string The new login URL.
		 */
		public function filter_wp_login_php( $url ) {
			if ( strpos( $url, 'wp-login.php' ) !== false ) {
				$args = explode( '?', $url );
				if ( isset( $args[1] ) ) {
					parse_str( $args[1], $args );
					$url = add_query_arg( $args, $this->new_login_url() );
				} else {
					$url = $this->new_login_url();
				}
			}
			return $url;
		}

		/**
		 * Hooks into network_site_url to rewrite login URLs.
		 *
		 * @param string $url The current base URL.
		 * @param string $path The current path.
		 * @param string $scheme An HTTP scheme.
		 * @param string $blog_id The (multisite) site blog id.
		 * @return string The prepared URL.
		 */
		public function site_url( $url, $path, $scheme, $blog_id ) {
			return $this->filter_wp_login_php( $url );
		}

		/**
		 * Hooks into network_site_url to rewrite login URLs.
		 *
		 * @param string $url The current base URL.
		 * @param string $path The current path.
		 * @param string $scheme An HTTP scheme.
		 * @return string The prepared URL.
		 */
		public function network_site_url( $url, $path, $scheme ) {
			return $this->filter_wp_login_php( $url );
		}

		/**
		 * Hooks into wp_redirect to rewrite login URLs.
		 *
		 * @param string $location A URL.
		 * @param string $status An HTTP status code.
		 * @return string The prepared URL.
		 */
		public function wp_redirect( $location, $status ) {
			return $this->filter_wp_login_php( $location );
		}

		/**
		 * Replace "wp-login.php" in our welcome email with the new path.
		 *
		 * @param string $value The original welcome email text.
		 * @return string The new welcome email text.
		 */
		public function welcome_email( $value ) {
			return str_replace( 'wp-login.php', trailingslashit( $this->new_login_slug ), $value );
		}

		/**
		 * Update the list of forbidden paths.
		 *
		 * @return [] The new list of forbidden paths.
		 */
		public function forbidden_slugs() {
			$wp = new WP();
			return array_merge( $wp->public_query_vars, $wp->private_query_vars );
		}
	}
	new Rename_WP_Login();
}
