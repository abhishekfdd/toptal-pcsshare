<?php
    function pcs_get_current_url_with_parameters() {
        return home_url( add_query_arg( null, null ));
    }

    function pcs_get_inbox_tab($tab) {
        if ( $tab === 'inbox' ) {
            get_template_part( 'profile/inbox', 'loop' );
        } else if ( $tab === 'sent' ) {
            get_template_part( 'profile/inbox', 'sent' );
        } else {
            get_template_part( 'profile/inbox', 'loop' );
        }
    }

    function pcs_determine_inbox_tab_class($tab) {
        return pcs_is_inbox_tab_active( $tab ) ? 'active' : '';
    }

    function pcs_is_inbox_tab_active($tab) {
        if ( isset( $_GET['inbox-tab'] ) ) {
            return $_GET['inbox-tab'] === $tab;
        } else {
            if ( isset( $_GET['profile-tab'] ) ) {
                return $_GET['profile-tab'] === $tab;
            } else {
                return ( 'compose' === $tab ? true : false );
            }
        }
    }

    $inbox_url = bp_core_get_user_domain( pcs_get_current_user_id() ) . '?profile-tab=inbox';
    $sent_url = add_query_arg( ['inbox-tab' => 'sent', 'mpage' => '1'], pcs_get_current_url_with_parameters() );
?>

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="<?php echo pcs_determine_inbox_tab_class( 'inbox' ); ?>"><a href="<?php echo $inbox_url; ?>">Inbox</a></li>
    <li role="presentation" class="<?php echo pcs_determine_inbox_tab_class( 'sent' ); ?>"><a href="<?php echo $sent_url; ?>">Sent</a></li>
    <li role="presentation" class="<?php echo pcs_determine_inbox_tab_class( 'compose' ); ?>"><a href="<?php echo bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose'; ?>">Compose</a></li>
</ul>
