<?php

/*
 * Plugin Name: PCS Users
 * Plugin URI: http://pcssharetreatments.com
 * Description: Utility functions for easier working with users.
 * Author: Hrvoje Gazibara
 * Version: 1.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', 'check_buddypress_active' );

function check_buddypress_active() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  ! is_plugin_active( 'buddypress/bp-loader.php' ) ) {
        add_action( 'admin_notices', 'pcs_buddypress_missing' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function pcs_buddypress_missing() {
    echo '<div class="error"><p>Sorry, but you need to install BuddyPress first. :(</p></div>';
}

function pcs_get_anonymous_user_id() {
    $anonymous_user = get_user_by( 'slug', pcs_get_anonymous_user_name() );

    if ( $anonymous_user == false ) {
        return null;
    }

    return $anonymous_user->ID;
}

function pcs_get_anonymous_user_name() {
    return 'anonymous';
}

function pcs_get_username($user_id) {
    $user = get_userdata( $user_id );
    return esc_html( $user->user_login );
}

function pcs_get_current_user_id() {
    $current_user = wp_get_current_user();
    return $current_user->ID;
}

function pcs_get_current_user_username() {
    $current_user = wp_get_current_user();
    return esc_html( $current_user->user_login );
}

function pcs_is_user_logged_in() {
     return pcs_get_current_user_id() !== 0;
}

function pcs_set_profile_value($user_id, $key, $value) {
	switch ($key) {
		case 'dateOfInjury':
			$date = date( 'Y-m-d 00:00:00', strtotime( $value ) );
		 	return pcs_set_date_of_injury( $user_id, $date );
		case 'causeOfInjury':
			return pcs_set_cause_of_injury( $user_id, $value );
		case 'symptoms':
			return pcs_set_symptoms( $user_id, $value );
		case 'additionalInformation':
			return pcs_set_additional_information( $user_id, $value );
	}
}

function pcs_get_opened_user_date_of_injury() {
    return pcs_get_date_of_injury( pcs_get_visited_user_id() );
}

function pcs_get_date_of_injury($user_id) {
    return esc_html( xprofile_get_field_data( 2, $user_id ) );
}

function pcs_set_date_of_injury($user_id, $date_of_injury) {
    xprofile_set_field_data( 2, $user_id, $date_of_injury );
}

function pcs_get_opened_user_cause_of_injury() {
    return pcs_get_cause_of_injury( pcs_get_visited_user_id() );
}

function pcs_get_cause_of_injury($user_id) {
    return esc_html( xprofile_get_field_data( 3, $user_id ) );
}

function pcs_set_cause_of_injury($user_id, $cause_of_injury) {
    xprofile_set_field_data( 3, $user_id, $cause_of_injury );
}

function pcs_get_opened_user_symptoms() {
    return pcs_get_symptoms( pcs_get_visited_user_id() );
}

function pcs_get_symptoms($user_id) {
    return esc_html( xprofile_get_field_data( 4, $user_id ) );
}

function pcs_set_symptoms($user_id, $symptoms) {
    xprofile_set_field_data( 4, $user_id, $symptoms );
}

function pcs_get_opened_user_additional_information() {
    return pcs_get_additional_information( pcs_get_visited_user_id() );
}

function pcs_get_additional_information($user_id) {
    return xprofile_get_field_data( 5, $user_id );
}

function pcs_set_additional_information($user_id, $additional_information) {
    xprofile_set_field_data( 5, $user_id, $additional_information );
}

function pcs_is_current_user_author() {
    return pcs_get_current_user_id() === pcs_get_visited_user_id();
}

function pcs_is_displayed_user_author() {
    return pcs_get_current_user_id() === bp_displayed_user_id();
}

function pcs_is_current_user_logged_in() {
    return pcs_get_current_user_id() != 0;
}

/**
 * IX. Profile Specific Functions
 */
function get_active_user_profile_url($tab = '') {
    return get_user_profile_url( get_the_author() );
}

function get_user_profile_url($user, $tab = '') {
    $profile_url = site_url( '/author/' ) . $user;
    return $tab ? "$profile_url?profile-tab=$tab" : $profile_url;
}

function pcs_get_visited_user_id() {
    return bp_displayed_user_id();
}

function pcs_get_visited_user_profile_url($tab = '') {
    $profile_url = site_url( '/members/' ) . bp_get_displayed_user_username();
    return $tab ? "$profile_url?profile-tab=$tab" : $profile_url;
}

function get_active_profile_tab() {
    return isset($_GET['profile-tab']) ? $_GET['profile-tab'] : 'profile';
}

add_action( 'wp_ajax_pcs-delete-profile', 'pcs_ajax_delete_profile' );

function pcs_can_manage_visited_user($user_id) {
    $is_administrator = current_user_can( 'edit_users' );
    $is_profile_owner = $user_id == pcs_get_current_user_id();

    return $is_administrator || $is_profile_owner;
}

function pcs_ajax_delete_profile() {
    check_ajax_referer( 'pcs-delete-profile', '_wp_nonce' );

    if ( ! pcs_can_manage_visited_user( $_POST['id'] ) ) {
    	wp_die( 'You do not have permission do delete this profile.', '', [ 'status' => 403 ] );
    }

    echo json_encode( pcs_delete_profile( $_POST['id'] ) );

    wp_die();
}

function pcs_delete_profile($user_id) {
    wp_delete_user( $user_id );

    // If a user who deleted the profile is not the administrator, then it's the
    // profile owner itself. Make sure to log him/her out of the site after the
    // profile has been deleted.
    if ( ! current_user_can( 'edit_users' ) ) {
        wp_logout();
    }

    return [ 'status' => 'success', 'data' => $user_id ];
}

add_action( 'admin_post_pcs-update-profile', 'pcs_handle_update_profile' );

function pcs_handle_update_profile() {
    check_admin_referer( 'pcs-update-profile', '_wpnonce' );

    if ( ! pcs_can_manage_visited_user( $_POST['id'] ) ) {
        wp_safe_redirect( wp_get_referer() );
    }

    pcs_update_profile( $_POST );

    wp_safe_redirect( wp_get_referer() );
    exit;
}

function pcs_update_profile($data) {
    $user_id = $data['id'];

    $keys_to_set = [
        'dateOfInjury', 'causeOfInjury', 'symptoms', 'additionalInformation'
    ];

    $new_data = [];
    $statues = [];

    foreach ( $keys_to_set as $key ) {
        $statues[] = pcs_set_profile_value( $user_id, $key, $data[$key] );
        $new_data[$key] = $data[$key];
    }

    if ( in_array( false, $statues, true ) ) {
        return [ 'status' => 'error', 'data' => 'Could not update some fields' ];
    }

    return [ 'status' => 'success', 'data' => $new_data ];
}
