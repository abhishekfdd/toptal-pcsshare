<?php
/**
 * Manage media uploaded file.
 *
 * There are many filters in here for media. Plugins can extend functionality
 * by hooking into the filters.
 *
 * @package WordPress
 * @subpackage Administration
 */

if ( ! isset( $_GET['inline'] ) )
	define( 'IFRAME_REQUEST' , true );

/** Load WordPress Administration Bootstrap */
require_once( '../../../wp-admin/admin.php' );
global $wpdb;

// Get requested params
$post_id = (int)$_REQUEST['post_id'];
//$last_time = $_REQUEST['last_time'];
$limit = isset( $_REQUEST['limit'] ) ? (int)$_REQUEST['limit'] : 20;

// SQL where conditions
$where = array();
$where[] = "post_id = $post_id";
$where[] = "value > 0";
$where[] = "user_id > 0";

if(!empty($_REQUEST['last_time'])) {
	$where[] = "date_time > '" . $_REQUEST['last_time'] . "'";
}

// Build where clause and get the result
$where_clause = " WHERE " . implode(" AND ", $where);

$query = "SELECT user_id, SUM(value) AS like_count FROM `{$wpdb->prefix}wti_like_post` $where_clause GROUP BY user_id ORDER BY like_count DESC, user_id ASC LIMIT $limit";
$result = $wpdb->get_results($query);
$counter = 0;

if(count($result) > 0) {
	?>
	<div class="ui-sortable meta-box-sortables">
		<table cellspacing="0" class="wp-list-table widefat fixed likes" width="95%" align="center">
			<thead>
				<tr>
					<td width="5%">
						<strong><?php echo __('#', 'wti-like-post'); ?></strong>
					</td>
					<td width="8%">
						<strong><?php echo __('User Id', 'wti-like-post'); ?></strong>
					</td>
					<td width="30%">
						<strong><?php echo __('Name', 'wti-like-post'); ?></strong>
					</td>
					<td width="40%">
						<strong><?php echo __('Email', 'wti-like-post'); ?></strong>
					</td>
					<td>
						<strong><?php echo __('Count', 'wti-like-post'); ?></strong>
					</td>
					<td></td>
				<tr>
			</thead>
			<tbody class="list:likes" id="the-list">
				<?php
				$nonce = wp_create_nonce("wti_like_post_clear_nonce");
				foreach($result as $row) {
					$user = get_userdata($row->user_id);
					?>
					<tr>
						<td>
							<?php echo ++$counter; ?>
						</td>
						<td>
							<?php echo $row->user_id; ?>
						</td>
						<td>
							<?php echo $user->display_name; ?>
						</td>
						<td>
							<a href="mailto:<?php echo $user->user_email; ?>"><?php echo $user->user_email; ?></a>
						</td>
						<td>
							<?php echo $row->like_count; ?>
						</td>
						<td id="clear-<?php echo $post_id?>">
							<a href="javascript:void(0)" class="button button-secondary clear-vote" data-post-id="<?php echo $post_id?>" data-user-id="<?php echo $row->user_id?>" data-nonce="<?php echo $nonce?>"><?php echo __('Clear Vote', 'wti-like-post'); ?></a>
						</td>
					<tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
	<?php
} else {
	echo '<p>';
	echo __('No users voted yet. This will show the users only when logged in users are allowed to vote.', 'wti-like-post');
	echo '</p>';
}