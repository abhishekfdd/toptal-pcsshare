<?php

/*
 * Plugin Name: PCS Flash
 * Plugin URI: http://pcssharetreatments.com
 * Description: Simple functionality for passing session flash data between requests.
 * Author: Hrvoje Gazibara
 * Version: 1.0
 */
defined( 'ABSPATH' ) || exit;

if ( ! session_id() ) {
    session_start();
}

/**
 * @param string $name message name
 * @param string $value message value
 */
function pcs_set_flash($name, $value) {
    if ( empty( $name ) || empty( $value ) ) {
        return;
    }

    $_SESSION[$name] = $value;
}

function pcs_get_flash($name) {
    if ( empty( $_SESSION[$name] ) ) {
        return;
    }

    $value = $_SESSION[$name] . '';
    unset($_SESSION[$name]);

    return $value;
}

function pcs_has_flash($name) {
    return empty( $_SESSION[$name] ) === false;
}
