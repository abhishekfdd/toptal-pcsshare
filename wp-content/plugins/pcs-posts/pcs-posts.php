<?php

/*
 * Plugin Name: PCS Posts
 * Plugin URI: http://pcssharetreatments.com
 * Description: Makes it easy to embed control for creating, updating and deleting posts directly into site's front-end.
 * Author: Hrvoje Gazibara
 * Version: 1.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', 'check_pcs_flash_active' );
add_action( 'admin_init', 'check_pcs_categories_active' );
add_action( 'admin_init', 'check_pcs_users_active' );

function check_pcs_flash_active() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  ! is_plugin_active( 'pcs-flash/pcs-flash.php' ) ) {
        add_action( 'admin_notices', 'pcs_flash_missing' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function check_pcs_categories_active() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  ! is_plugin_active( 'pcs-categories/pcs-categories.php' ) ) {
        add_action( 'admin_notices', 'pcs_categories_missing' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function check_pcs_users_active() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  ! is_plugin_active( 'pcs-users/pcs-users.php' ) ) {
        add_action( 'admin_notices', 'pcs_users_missing' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function pcs_flash_missing() {
    echo '<div class="error"><p>Sorry, but you need to install PCS Flash first. :(</p></div>';
}

function pcs_categories_missing() {
    echo '<div class="error"><p>Sorry, but you need to install PCS Categories first. :(</p></div>';
}

function pcs_users_missing() {
    echo '<div class="error"><p>Sorry, but you need to install PCS Users first. :(</p></div>';
}

/**
 * If all dependencies are met, the plug-in can be initialized.
 */
PCSPostsSetup::initialize();

/**
 * Entry point for plug-in initialization
 */
class PCSPostsSetup {

    public static function initialize() {
        $create_post_action = new PCSCreatePostAction;
        $create_post_action->initialize();

        $update_post_action = new PCSUpdatePostAction;
        $update_post_action->initialize();

        $delete_post_action = new PCSDeletePostAction;
        $delete_post_action->initialize();
    }
}


/**
 * Base class defining interface for actions which expose functionality for
 * working with posts.
 */
abstract class PCSPostAction {

    public function initialize() {
        $this->add_actions();
        $this->add_filters();
    }

    public function add_actions() {
        add_action(
            sprintf( 'admin_post_%s', $this->get_name() ),
            [ $this, 'handle' ]
        );
        add_action(
            sprintf( 'admin_post_nopriv_%s', $this->get_name() ),
            [ $this, 'handle' ]
        );
    }

    public function add_filters() {
        // By default, no filters are registered. Subclasses can overwrite this
        // if needed.
    }

    public abstract function get_name();

    public function handle() {
        $data = $_POST;

        $this->check_action();
        $this->check_capabilities($data);

        return $this->execute($data);
    }

    public function check_action() {
        check_admin_referer( $this->get_name() );
    }

    public abstract function check_capabilities($post);

    protected abstract function execute($post);

    public function forbidden() {
        wp_die( 'You do not have permission do edit this post.', '', [ 'status' => 403 ] );
    }
}

/**
 * Child action class for creating a new post.
 */
class PCSCreatePostAction extends PCSPostAction {

    const NAME = 'pcs_posts_create';
    const AFTER_POST_CREATE_FILTER = 'pcs_posts_create_after';

    public function add_actions() {
        parent::add_actions();
        add_action( self::AFTER_POST_CREATE_FILTER, [$this, 'vote'], 10, 2 );
    }

    public function get_name() {
        return self::NAME;
    }

    public function check_capabilities($post) {
        // Everyone can create a post
        return true;
    }

    protected function execute($post) {
        $post_id = wp_insert_post([
    		'post_author' => intval($post['author']) ?: pcs_get_anonymous_user_id(),
    		'post_content' => $post['description'],
    		'post_title' => $post['title'],
    		'post_category' => [$post['category']],
    		'post_status' => pcs_is_current_user_logged_in() ? 'publish' : 'draft'
    	]);

    	if ( ! $post_id ) {
            pcs_set_flash( 'create.error', 'Could not update a post' );
            pcs_posts_go_back();
    	}

    	$page = new PCSPage($post['link']);
    	$page->load();

        pcs_set_post_excerpt_link( $post_id, $post['link'] );
        pcs_set_post_excerpt_title( $post_id, $page->title() );
        pcs_set_post_excerpt_image( $post_id, $page->image() );
        pcs_set_post_excerpt_description( $post_id, $page->description() );
        pcs_set_post_excerpt_meta_description( $post_id, $post['link'] );

        // Do whatever you want after the post is successfully saved
        do_action( self::AFTER_POST_CREATE_FILTER, $_POST, $post_id );

        pcs_set_flash( 'create.success', 'Post created successfully' );
        pcs_posts_go_back();
    }

    /**
     * Add support for immediately adding votes for Wti Post PRO
     */
    public function vote($post, $post_id) {
        if ( $post['author'] != '0' ) {
            pcs_posts_trigger_user_vote( $post, $post_id );
        } else {
            pcs_posts_trigger_anonymous_vote( $post, $post_id );
        }
    }
}

function pcs_posts_trigger_anonymous_vote($post, $post_id) {
    global $wpdb, $wti_ip_address;

    $datetime_now = date( 'Y-m-d H:i:s' );
    $cookie_value = microtime(true) . rand( 1, 99999 );

    $query = "INSERT INTO {$wpdb->prefix}wti_like_post SET ";
    $query .= "post_id = '" . $post_id . "', ";
    $query .= "value = '" . str_replace( '+', '', $post['vote']) . "', ";
    $query .= "user_id = " . pcs_get_anonymous_user_id() . ", ";
    $query .= "date_time = '" . $datetime_now . "', ";
    $query .= "ip = '$wti_ip_address', ";
    $query .= "cookie_value = '$cookie_value'";

    $wpdb->query( $query );
}

function pcs_posts_trigger_user_vote($post, $post_id) {
    if ( function_exists( 'WtiLikePostProcessVote' ) ) {
        if ( isset( $post['vote'] ) && ($post['vote'] === '+1' || $post['vote'] === '-1') ) {
            $_REQUEST['post_id'] = $post_id;
            $_REQUEST['task'] = $post['vote'] === '+1' ? 'like' : 'unlike';
            $_REQUEST['nonce'] = wp_create_nonce("wti_like_post_vote_nonce");

            WtiLikePostProcessVote();
        }
    }
}

class PCSPage {

	const IMAGE_PATTERN = '/<meta property="og:image"\s+content="(.*?)"\s*\/?>/';
	const TITLE_PATTERN = '/<meta property="og:title"\s+content="(.*?)"\s*\/?>/';
    const DESCRIPTION_PATTERN = '/<meta property="og:description"\s+content="(.*?)"\s*\/?>/';

	private $url;
	private $contents;

	public function __construct($url) {
		$this->url = $url;
	}

	public function load() {
		$this->contents = explode("\n", file_get_contents($this->url));
		return $this;
	}

	public function image() {
		$image_path = $this->find(self::IMAGE_PATTERN);

		if ( ! empty( $image_path ) ) {
			return $image_path;
		}

		$images = $this->find_images();
		$final_image = new DummyImage;

		foreach ( $images as $image ) {
			if ( $image->width() < 350 ) {
				continue;
			}

			if ( $image->quality() > $final_image->quality() ) {
				$final_image = $image;
			}
		}

		return $final_image->url;
	}

	public function title() {
		$title = $this->find(self::TITLE_PATTERN);

		if ( ! empty( $title ) ) {
			return $title;
		}

		return $this->get_site_title();
	}

	public function get_site_title() {
		$site_title_pattern = '/<title>(.+?)<\/title>/';
		return $this->find( $site_title_pattern );
	}

    public function description() {
        return $this->find(self::DESCRIPTION_PATTERN);
    }

	public function find($pattern) {
		$data = '';

		foreach ( $this->contents as $line ) {
			$count = preg_match_all( $pattern, $line, $matches );
			if ( $count ) {
				$data = $matches[1][0];
				break;
			}
		}

		return $data;
	}

	public function find_images() {
		$dom = new domDocument;
		@$dom->loadHTML( file_get_contents( $this->url ) );
		$dom->preserveWhiteSpace = false;

		$images = $dom->getElementsByTagName( 'img' );
		$images_from_site = [];

		foreach ( $images as $image ) {
			$image_path = UrlBuilder::build( $this->url, $image->getAttribute( 'src' ) );
			$images_from_site[] = new Image( $image_path );
		}

		return $images_from_site;
	}
}

class Image {

	protected $width;
	protected $height;

	public function __construct($url) {
		$this->url = $url;

		$this->width = 0;
		$this->height = 0;
	}

	public function quality() {
		return $this->width() / $this->height();
	}

	public function width() {
		$this->get_properties();
		return $this->width;
	}

	public function height() {
		$this->get_properties();
		return $this->height;
	}

	public function get_properties() {
		if ( $this->width != 0 && $this->height != 0 ) {
			return;
		}

		list( $width, $height, $type, $attr ) = getimagesize( $this->url );

		$this->width = $width;
		$this->height = $height;
	}
}

class DummyImage extends Image {

	public function __construct() {
		parent::__construct('');
	}

	public function get_properties() {
		$this->width = 1;
		$this->height = 1;
	}
}

class UrlBuilder {
	public static function build($path, $relative) {
		if ( substr( $relative, 0, 4 ) === 'http' ) {
			return $relative;
		}
		return rtrim( $path, '/' ) . '/' . ltrim( $relative, '/' );
	}
}

/**
 * Child action class for updating an existing post.
 */
class PCSUpdatePostAction extends PCSPostAction {

    const NAME = 'pcs_posts_update';
    const AFTER_POST_UPDATE_FILTER = 'pcs_posts_update_after';

    public function get_name() {
        return self::NAME;
    }

    protected function execute($post) {
        $result = wp_update_post([
    		'ID' => $post['id'],
    		'post_title' => $post['title'],
    		'post_content' => $post['description']
    	]);

    	if ( ! $result ) {
            pcs_set_flash( 'update.error', 'Could not update a post' );
            pcs_posts_go_back();
    	}

    	if ( $post['link'] !== pcs_get_post_excerpt_link( $post['id'] ) ) {
    		$page = new PCSPage($post['link']);
    		$page->load();

            $post_id = intval( $post['id'] );

            pcs_update_post_excerpt_link( $post_id, $post['link'] );
            pcs_update_post_excerpt_image( $post_id, $page->image() );
            pcs_update_post_excerpt_title( $post_id, $page->title() );
            pcs_update_post_excerpt_description( $post_id, $page->description() );
            pcs_update_post_excerpt_meta_description( $post_id, $post['link'] );
    	}

        // Do whatever you want after the post is successfully update
        apply_filters( self::AFTER_POST_UPDATE_FILTER, $post );

        pcs_set_flash( 'update.success', 'Post updated successfully' );
        pcs_posts_go_back();
    }

    public function check_capabilities($post) {
        if ( ! current_user_can( 'edit_post', $post['id'] ) ) {
    		$this->forbidden();
    	}
    }
}

/**
 * Child action class for deleting a post.
 */
class PCSDeletePostAction extends PCSPostAction {

    const NAME = 'pcs_posts_delete';
    const AFTER_POST_DELETE_FILTER = 'pcs_posts_delete_after';

    public function add_actions() {
        add_action(
            sprintf( 'wp_ajax_%s', $this->get_name() ),
            [ $this, 'handle' ]
        );
    }

    public function get_name() {
        return self::NAME;
    }

    protected function execute($post) {
        $status = wp_update_post([
    		'ID' => $post['id'],
    		'post_status' => 'trash'
    	]);

    	if ( $status === 0 ) {
            wp_send_json( [
                'status' => 'error',
                'data' => 'Could not delete post' ]
            );
    	}

        // Do whatever you want after the post is successfully deleted
        apply_filters( self::AFTER_POST_DELETE_FILTER, $post );

        wp_send_json( [ 'data' => $post['id'], 'status' => 'success' ] );
    }

    public function check_action() {
        check_ajax_referer( $this->get_name(), '_wpnonce' );
    }

    public function check_capabilities($post) {
        if ( ! current_user_can( 'edit_post', $post['id'] ) ) {
    		echo json_encode( [
                'status' => 'error',
                'data' => 'You do not have permission to delete the post'
            ] );
            wp_die();
    	}
    }
}

/**
 * Global functions to be used throught client code
 */

/**
 *------------------------------------------------------------------------------
 * Utility functions - Meta
 *------------------------------------------------------------------------------
 */
function pcs_get_post_excerpt_link_meta_name() {
    return 'pcs_post_excerpt_link';
}

function pcs_set_post_excerpt_link($post_id, $link) {
    return add_post_meta( $post_id, pcs_get_post_excerpt_link_meta_name(), $link );
}

function pcs_update_post_excerpt_link($post_id, $link) {
    return update_post_meta( $post_id, pcs_get_post_excerpt_link_meta_name(), $link );
}

function pcs_post_excerpt_link($post_id) {
    return printf( '<a href="%1$s" target="_blank">%1$s</a>', pcs_get_post_excerpt_link( $post_id ) );
}

function pcs_get_post_excerpt_link($post_id) {
	return esc_url( get_post_meta( $post_id, pcs_get_post_excerpt_link_meta_name(), true ) );
}

function pcs_get_post_excerpt_image_meta_name() {
    return 'pcs_post_excerpt_image';
}

function pcs_set_post_excerpt_image($post_id, $image) {
    return add_post_meta( $post_id, pcs_get_post_excerpt_image_meta_name(), $image );
}

function pcs_update_post_excerpt_image($post_id, $image) {
    return update_post_meta( $post_id, pcs_get_post_excerpt_image_meta_name(), $image );
}

function pcs_get_post_excerpt_image($post_id) {
	return esc_url( get_post_meta( $post_id, pcs_get_post_excerpt_image_meta_name(), true ) );
}

function pcs_get_post_excerpt_title_meta_name() {
    return 'pcs_post_excerpt_title';
}

function pcs_set_post_excerpt_title($post_id, $title) {
    return add_post_meta( $post_id, pcs_get_post_excerpt_title_meta_name(), $title );
}

function pcs_update_post_excerpt_title($post_id, $title) {
    return update_post_meta( $post_id, pcs_get_post_excerpt_title_meta_name(), $title );
}

function pcs_get_post_excerpt_title($post_id) {
	return esc_html( get_post_meta( $post_id, pcs_get_post_excerpt_title_meta_name(), true ) );
}

function pcs_get_post_excerpt_description_name() {
    return 'pcs_post_excerpt_description';
}

function pcs_set_post_excerpt_description($post_id, $description) {
    return add_post_meta( $post_id, pcs_get_post_excerpt_description_name(), $description );
}

function pcs_update_post_excerpt_description($post_id, $description) {
    return update_post_meta( $post_id, pcs_get_post_excerpt_description_name(), $description );
}

function pcs_get_post_excerpt_description($post_id) {
	return esc_html( get_post_meta( $post_id, pcs_get_post_excerpt_description_name(), true ) );
}

function pcs_get_post_excerpt_meta_description_name() {
    return 'pcs_post_excerpt_meta_description';
}

function pcs_set_post_excerpt_meta_description($post_id, $excerpt_link) {

	if( ! empty( $excerpt_link ) ) {

		$tags = get_meta_tags( esc_url( $excerpt_link[0] ) );

		if( ! empty( $tags['description'] ) ) {
			return add_post_meta( $post_id, pcs_get_post_excerpt_meta_description_name(), $tags['description'] );
		}
	}

    return false;
}

function pcs_update_post_excerpt_meta_description($post_id, $excerpt_link) {

    if( ! empty( $excerpt_link ) ) {

		$tags = get_meta_tags( esc_url( $excerpt_link[0] ) );

		if( ! empty( $tags['description'] ) ) {
			return update_post_meta( $post_id, pcs_get_post_excerpt_meta_description_name(), $tags['description'] );
		}
	}

    return false;
}

function pcs_get_post_excerpt_meta_description($post_id) {

    $description = get_post_meta( $post_id, pcs_get_post_excerpt_meta_description_name(), true );

    if( empty( $description ) ) {
        $description = get_post_meta( $post_id, pcs_get_post_excerpt_description_name(), true );
    }


	return esc_html( $description );
}

/**
 *------------------------------------------------------------------------------
 * Utility functions - Nonce
 *------------------------------------------------------------------------------
 */
function pcs_posts_get_create_nonce() {
    return wp_create_nonce( PCSCreatePostAction::NAME );
}

function pcs_posts_get_update_nonce() {
    return wp_create_nonce( PCSUpdatePostAction::NAME );
}

function pcs_posts_get_delete_nonce() {
    return wp_create_nonce( PCSDeletePostAction::NAME );
}

/**
 *------------------------------------------------------------------------------
 * Utility functions - Posts
 *------------------------------------------------------------------------------
 */
function pcs_posts_get_action_name_create() {
    return PCSCreatePostAction::NAME;
}

function pcs_posts_get_action_name_update() {
    return PCSUpdatePostAction::NAME;
}

function pcs_posts_get_action_name_delete() {
    return PCSDeletePostAction::NAME;
}

function pcs_get_posts_by_user($user_id) {
	return new WP_Query([
		'posts_per_page' => -1,
		'author' => $user_id,
		'post_type' => 'post'
	]);
}

function pcs_current_user_can_edit_post($post_id) {
    return current_user_can( 'edit_post', $post_id );
}

/**
 *------------------------------------------------------------------------------
 * Utility functions - Templates
 *------------------------------------------------------------------------------
 */
function pcs_posts_show_create_modal() {
    pcs_posts_show_template( 'modal_create' );
}

function pcs_posts_show_delete_modal() {
    pcs_posts_show_template( 'modal_delete' );
}

function pcs_posts_show_update_modal() {
    pcs_posts_show_template( 'modal_update' );
}

function pcs_posts_show_create_button() {
    pcs_posts_show_template( 'button_create' );
}

function pcs_posts_show_update_button() {
    pcs_posts_show_template( 'button_update' );
}

function pcs_posts_show_delete_button() {
    pcs_posts_show_template( 'button_delete' );
}

function pcs_posts_show_template($template) {
    $filename = sprintf( '%s.php', $template );

    $theme_path = get_template_directory();

    $local_path = $theme_path . DIRECTORY_SEPARATOR . 'pcs-posts';
    $local_template = $local_path . DIRECTORY_SEPARATOR . $filename;

    if ( file_exists( $local_template ) ) {
        include $local_template;
        return;
    }

    $plugin_path = plugin_dir_path( __FILE__ );
    $plugin_template = $plugin_path . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $filename;

    if ( file_exists( $plugin_template ) ) {
        include $plugin_template;
        return;
    }
}

/**
 *------------------------------------------------------------------------------
 * Utility functions - Other
 *------------------------------------------------------------------------------
 */
function pcs_posts_go_back() {
    wp_safe_redirect( wp_get_referer() );
    exit;
}
