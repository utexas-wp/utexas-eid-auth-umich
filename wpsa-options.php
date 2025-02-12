<?php

/**
 * NOTE: options defined here in utexas_wpsax_filter_option are the defaults for UT
 * sites on Pantheon. If for some reason you want to override anything set
 * here, create your own mini-plugin -- see utexas-wp-saml-auth-config-overrides.php.example
 */

function utexas_wpsax_filter_option($value, $option_name) {
	$defaults = array(
		/**
		 * Type of SAML connection bridge to use.
		 *
		 * 'internal' uses OneLogin bundled library; 'simplesamlphp' uses SimpleSAMLphp.
		 *
		 * Defaults to SimpleSAMLphp for backwards compatibility.
		 *
		 * @param string
		 */
		'connection_type' => 'internal',
		/**
		 * Configuration options for OneLogin library use.
		 *
		 * See comments with "Required:" for values you absolutely need to configure.
		 *
		 * @param array
		 */
		'internal_config'        => array(
			// Validation of SAML responses is required.
			'strict'       => true,
			'debug'        => true,
			'baseurl'      => home_url(),
			'sp'           => array(
				'entityId' => home_url() . '/onelogin',
				'assertionConsumerService' => array(
					'url'  => wp_login_url(),
					'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
				),
				'x509cert' => file_get_contents(ABSPATH . 'wp-content/uploads/private/saml/assets/cert/sp-cert.crt'),
				'privateKey' => file_get_contents(ABSPATH . 'wp-content/uploads/private/saml/assets/cert/sp-key.pem'),
			),
			'idp'          => array(
				// Required: Set based on provider's supplied value.
				'entityId' => 'https://enterprise.login.utexas.edu/idp/shibboleth',
				'singleSignOnService' => array(
					'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
					'url' => 'https://enterprise.login.utexas.edu/idp/profile/SAML2/Redirect/SSO',
				),
				'singleLogoutService' => array(
					// Required: Set based on provider's supplied value.
				),
				// Required: Contents of the IDP's public x509 certificate.
				// Use file_get_contents() to load certificate contents into scope.
				'x509cert' => file_get_contents(ABSPATH . 'wp-content/uploads/private/saml/assets/cert/idp-cert-prod.crt'),
				'privateKey' => file_get_contents(ABSPATH . 'wp-content/uploads/private/saml/assets/cert/sp-key.pem'),
			),
		),
		/**
		 * Whether or not to automatically provision new WordPress users.
		 *
		 * When WordPress is presented with a SAML user without a
		 * corresponding WordPress account, it can either create a new user
		 * or display an error that the user needs to contact the site
		 * administrator.
		 *
		 * @param bool
		 */
		'auto_provision'         => true,
		/**
		 * Whether or not to permit logging in with username and password.
		 *
		 * If this feature is disabled, all authentication requests will be
		 * channeled through SimpleSAMLphp.
		 *
		 * @param bool
		 */
		'permit_wp_login'        => true,
		/**
		 * Attribute by which to get a WordPress user for a SAML user.
		 *
		 * @param string Supported options are 'email' and 'login'.
		 */
		'get_user_by'            => 'login',
		/**
		 * SAML attribute which includes the user_login value for a user.
		 *
		 * @param string
		 */
		'user_login_attribute'   => 'uid',
		/**
		 * SAML attribute which includes the user_email value for a user.
		 *
		 * @param string
		 */
		'user_email_attribute'   => 'mail',
		/**
		 * SAML attribute which includes the display_name value for a user.
		 *
		 * @param string
		 */
		'display_name_attribute' => 'displayName',
		/**
		 * SAML attribute which includes the first_name value for a user.
		 *
		 * @param string
		 */
		'first_name_attribute' => 'first_name',
		/**
		 * SAML attribute which includes the last_name value for a user.
		 *
		 * @param string
		 */
		'last_name_attribute' => 'last_name',
		/**
		 * Default WordPress role to grant when provisioning new users.
		 *
		 * @param string
		 */
		'default_role'           => get_option('default_role'),
	);
	$value = isset($defaults[$option_name]) ? $defaults[$option_name] : $value;
	return $value;
}
