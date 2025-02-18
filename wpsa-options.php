<?php

/**
 * NOTE: options defined here in utexas_wpsax_filter_option are the defaults for UT sites on Pantheon. If for some reason you want to override anything set here, create your own mini-plugin -- utexas-wp-saml-auth-config-overrides.php.example
 */

function utexas_wpsax_filter_option($value, $option_name) {
  $defaults = [
    'connection_type' => 'internal',
    'auto_provision'         => false,
    'permit_wp_login'        => true,
    'get_user_by'            => 'login',
    'user_login_attribute'   => 'username',
    'user_email_attribute'   => 'Email',
    'display_name_attribute' => 'full_name',
    'first_name_attribute' => 'full_name',
    'default_role'           => get_option('default_role'),
    'internal_config' => [
      'strict'       => true,
      'debug'        => false,
      'baseurl'      => home_url(),
      'sp'           => [
        'entityId' => home_url() . '/onelogin',
        'assertionConsumerService' => [
          'url'  => wp_login_url(),
          'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ],
        'x509cert' => file_get_contents(ABSPATH . 'wp-content/uploads/private/saml/assets/cert/sp-cert.crt'),
        'privateKey' => file_get_contents(ABSPATH . 'wp-content/uploads/private/saml/assets/cert/sp-key.pem'),
      ],
      'idp'          => [
        'entityId' => 'https://enterprise.login.utexas.edu/idp/shibboleth',
        'singleSignOnService' => [
          'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
          'url' => 'https://enterprise.login.utexas.edu/idp/profile/SAML2/Redirect/SSO',
        ],
        'singleLogoutService' => [
          'https://enterprise.login.utexas.edu/idp/profile/Logout'
        ],
        'x509cert' => file_get_contents(ABSPATH . 'wp-content/uploads/private/saml/assets/cert/idp-cert-prod.crt'),
        'certFingerprint' => '',
        'certFingerprintAlgorithm' => '',
      ],
      'security' => [
        'allowRepeatAttributeName' => true,
      ],
    ],
  ];
  $value = isset($defaults[$option_name]) ? $defaults[$option_name] : $value;
  return $value;
}
