<?php

// disable password fields on the Edit User Profile page
add_filter('show_password_fields', '__return_false');

// override the lost password URL with a link to the UT EID self help site
function utexas_lostpassword_url_filter($lostpassword_url, $redirect="") {
	global $UTexas_EID_self_help_url;
	return $UTexas_EID_self_help_url;
}
add_filter('lostpassword_url', 'utexas_lostpassword_url_filter', 10, 2);

// override the message body of the pasword reset email (if a user still gets to that page somehow)
function utexas_retrieve_password_message_filter($message, $key, $user_login, $user_data) {
	global $UTexas_EID_self_help_url;
	$message = __('Someone has requested a password reset for the following account:');
	$message .=  "\r\n\r\n";
	$message .= network_home_url( '/' ) . "\r\n\r\n";
	$message .= sprintf(__('Username (EID): %s'), $user_login);
	$message .= "\r\n\r\n";
	$message .= __('Since this site uses your UT EID for authentication, you will need to use the UT EID  Self-Service Tools to change or reset your password:') . "\r\n\r\n";
	$message .= $UTexas_EID_self_help_url . "\r\n";
}
add_filter('retrieve_password_message', 'utexas_retrieve_password_message_filter', 10, 4);

if ( !function_exists('wp_new_user_notification') ) :
/**
 * Email login credentials to a newly-registered user.
 *
 * A new user registration notification is also sent to admin email.
 * This overrides the default wp_new_user_notification found in wp-includes/pluggable.php
 * When UTexas EID Auth is enabled, we don't want to include the password when
 * emailing new users, so we override the content of the message in this
 * function.
 *
 * @param int    $user_id    User ID.
 * @param null   $deprecated Not used (argument deprecated).
 * @param string $notify     Optional. Type of notification that should happen. Accepts 'admin' or an empty
 *                           string (admin only), or 'both' (admin and user). Default empty.
 */
function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {
  if ( $deprecated !== null ) {
    _deprecated_argument( __FUNCTION__, '4.3.1' );
  }

  $user = get_userdata( $user_id );

  // The blogname option is escaped with esc_html on the way into the database in sanitize_option
  // we want to reverse this for the plain text arena of emails.
  $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

  $message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
  $message .= sprintf(__('Username (EID): %s'), $user->user_login) . "\r\n\r\n";
  $message .= sprintf(__('Email: %s'), $user->user_email) . "\r\n";

  @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);

  // `$deprecated was pre-4.3 `$plaintext_pass`. An empty `$plaintext_pass` didn't sent a user notifcation.
  if ( 'admin' === $notify || ( empty( $deprecated ) && empty( $notify ) ) ) {
    return;
  }

  $message = sprintf(__('An account has been created for you on the site "%s"'), $blogname) . "\r\n\r\n";
  $message = sprintf(__('Username (EID): %s'), $user->user_login) . "\r\n\r\n";
  $message .= __('To access this site, visit the following link and log in using your UT EID.') . "\r\n";

  $message .= wp_login_url() . "\r\n\r\n";

  wp_mail($user->user_email, sprintf(__('[%s] Access info'), $blogname), $message);
}
endif;
