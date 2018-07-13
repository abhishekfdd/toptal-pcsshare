<?php

/*
 * Plugin Name: PCS Comments
 * Plugin URI: http://pcssharetreatments.com
 * Description: Makes it easy to embed control for creating, updating and deleting comments directly into site's front-end.
 * Author: Hrvoje Gazibara
 * Version: 1.0
 */
defined( 'ABSPATH' ) || exit;

require_once('pcs-comments-walker.php');


/**
 * If all dependencies are met, the plug-in can be initialized.
 */
PCSCommentsSetup::initialize();


/**
 * Entry point for plug-in initialization
 */
class PCSCommentsSetup {

    public static function initialize() {
        $deleteCommentAction = new PCSDeleteCommentAction;
        $deleteCommentAction->initialize();

        $updateCommentAction = new PCSUpdateCommentAction;
        $updateCommentAction->initialize();

        // Simpler action registration
        //add_action( 'comment_post', 'set_anonymous_comment_message', 10, 2 );

        self::add_filters();
        self::add_actions();
    }

    public static function add_filters() {
        // add_filter( 'edit_comment_link', 'pcs_modify_edit_comment_link', 10, 2 );
        add_filter( 'get_comment_author_link', 'pcs_modify_comment_author_link', 10, 3 );
    }

    public static function add_actions() {
        add_action( 'wp_enqueue_scripts', 'pcs_comments_enqueue_scripts' );
    }
}


/**
 * Action class for updating a comment.
 */
class PCSUpdateCommentAction {

    const NAME = 'pcs-update-comment';

    public function initialize() {
        add_action( sprintf( 'admin_post_%s', $this->get_name() ), [$this, 'handle'] );
        add_action( sprintf( 'admin_post_nopriv_%s', $this->get_name() ), [$this, 'handle'] );
    }

    public function get_name() {
        return self::NAME;
    }

    public function handle() {
        $data = $_POST;

        $this->check_action();
        $this->check_capabilities( $data );

        $this->execute( $data );
    }

    public function check_action() {
        check_admin_referer( $this->get_name(), '_wpnonce' );
    }

    public function check_capabilities( $data ) {
        $comment = get_comment( $data['id'] );

        if ( ! pcs_current_user_can_delete_comment( $comment ) ) {
            wp_safe_redirect( wp_get_referer() );
            exit;
        }
    }

    public function execute(array $data) {
        $status = wp_update_comment([
            'comment_ID' => $data['id'],
            'comment_content' => $data['message']
        ]);

        wp_safe_redirect( wp_get_referer() );
        exit;
    }
}


/**
 * Action class for deleting a comment.
 */
class PCSDeleteCommentAction {

    const NAME = 'pcs-delete-comment';

    public function initialize() {
        add_action( sprintf( 'wp_ajax_%s', $this->get_name() ), [$this, 'handle'] );
        add_action( sprintf( 'wp_ajax_nopriv_%s', $this->get_name() ), [$this, 'handle'] );
    }

    public function get_name() {
        return self::NAME;
    }

    public function handle() {
        $data = $_POST;

        $this->check_action();
        $this->check_capabilities( $data );

        wp_send_json( $this->execute( $data ) );
    }

    public function check_action() {
        check_ajax_referer( $this->get_name(), '_wp_nonce' );
    }

    public function check_capabilities(array $data) {
        $comment = get_comment( $data['id'] );

        if ( ! pcs_current_user_can_delete_comment( $comment ) ) {
    		wp_die( 'You do not have permission do delete this comment.', '', [ 'status' => 403 ] );
    	}
    }

    public function execute(array $data) {
        $status = wp_delete_comment( $data['id'] );

    	if ( ! $status ) {
    		return [ 'status' => 'error', 'data' => 'Could not delete comment' ];
    	}

    	return [ 'status' => 'success', 'data' => $data['id'] ];
    }
}


/* Help functions */
add_action( 'comment_post', 'set_anonymous_comment_message', 10, 2 );
add_action( 'comment_post', 'pcs_approve_registered_user_comment', 10, 2 );

function pcs_current_user_can_delete_comment($comment) {
	$current_user_id = get_current_user_id();

	if ( empty( $comment ) || $current_user_id === 0 ) {
		return false;
	}

	// Either the comment author or someone with permission to moderate comments
	// is allowed to edit the comment
	$can_edit_comment = current_user_can( 'administrator' );
	$is_comment_author = $current_user_id == $comment->user_id;

	return $is_comment_author || $can_edit_comment;
}

function pcs_get_comments_by_user($user_id) {
    return get_comments([
        'type' => 'comment',
        'author__in' => [$user_id]
    ]);
}

function set_anonymous_comment_message( $comment_ID, $comment_approved ) {
	$comment = get_comment( $comment_ID );

	if ( $comment->user_id == 0 ) {
		pcs_set_flash(
			'comment.success.anonymous',
			'Thank you for the comment. It needs to be approved before showing on the site.'
		);
	}
}

function pcs_approve_registered_user_comment($comment_ID, $comment_approved) {
    $comment = get_comment( $comment_ID );

    if ( $comment->user_id != 0 ) {
        wp_update_comment([
            'comment_ID' => $comment_ID,
            'comment_approved' => 1
        ]);
    }
}

function pcs_modify_edit_comment_link($link, $comment_id) {
    $comment = get_comment( $comment_id );

    if ( ! pcs_current_user_can_delete_comment( $comment ) ) {
        return '';
    }

    return sprintf(
        '%s %s',
        pcs_comments_get_template('button_update'),
        pcs_comments_get_template('button_delete')
    );
}

function pcs_modify_comment_author_link($link, $author, $comment_id) {
    return str_replace('/author', '/members', $link);
}

function pcs_comments_enqueue_scripts() {
    wp_enqueue_script(
        'pcs_comments',
        plugin_dir_url( __FILE__ ) . 'assets/js/pcs-comments.js',
        [ 'jquery' ]
    );
}

/**
 *------------------------------------------------------------------------------
 * Utility functions - Nonce
 *------------------------------------------------------------------------------
 */
function pcs_comments_get_update_nonce() {
    return wp_create_nonce( PCSUpdateCommentAction::NAME );
}

function pcs_comments_get_delete_nonce() {
    return wp_create_nonce( PCSDeleteCommentAction::NAME );
}

/**
 *------------------------------------------------------------------------------
 * Utility functions - Comments
 *------------------------------------------------------------------------------
 */
function pcs_comments_get_update_action() {
    return PCSUpdateCommentAction::NAME;
}

function pcs_comments_get_delete_action() {
    return PCSDeleteCommentAction::NAME;
}

/**
 *------------------------------------------------------------------------------
 * Utility functions - Templates
 *------------------------------------------------------------------------------
 */
function pcs_comments_show_delete_modal() {
    echo str_replace(
        [ '%action%', '%nonce%' ],
        [
            pcs_comments_get_delete_action(),
            pcs_comments_get_delete_nonce()
        ],
        pcs_comments_get_template( 'modal_delete' )
    );
}

function pcs_comments_show_update_modal() {
    echo str_replace(
        [ '%form%', '%action%', '%nonce%' ],
        [
            esc_url( admin_url('admin-post.php') ),
            pcs_comments_get_update_action(),
            pcs_comments_get_update_nonce()
        ],
        pcs_comments_get_template( 'modal_update' )
    );
}

function pcs_comments_show_update_button() {
     pcs_comments_show_template( 'button_update' );
}

function pcs_comments_show_delete_button() {
    pcs_comments_show_template( 'button_delete' );
}

function pcs_comments_show_template($template) {
    echo pcs_comments_get_template( $template );
}

function pcs_comments_get_template($template) {
    $filename = sprintf( '%s.php', $template );

    $theme_path = get_template_directory();

    $local_path = $theme_path . DIRECTORY_SEPARATOR . 'pcs-comments';
    $local_template = $local_path . DIRECTORY_SEPARATOR . $filename;

    if ( file_exists( $local_template ) ) {
        $markup = file_get_contents( $local_template );
        return $markup;
    }

    $plugin_path = plugin_dir_path( __FILE__ );
    $plugin_template = $plugin_path . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $filename;

    if ( file_exists( $plugin_template ) ) {
        $markup = file_get_contents( $plugin_template );
        return $markup;
    }
}
