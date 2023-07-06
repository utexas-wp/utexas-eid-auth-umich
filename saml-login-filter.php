<?php


add_filter( 'wp_saml_auth_option', function( $value, $option ){
    if ( 'simplesamlphp_autoload' === $option ) {
        // Note: Your path may differ, if you've installed a later SimpleSAMLphp version
        $value = ABSPATH . '/vendor/simplesamlphp/simplesamlphp/lib/_autoload.php';
    }
    return $value;
}, 10, 2 );
