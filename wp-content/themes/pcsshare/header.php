<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>" />
        <title><?php wp_title(); ?></title>
        <link rel="profile" href="http://gmpg.org/xfn/11" />
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
        <?php if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); ?>
        <?php wp_head(); ?>
    </head>

    <body <?php body_class() ?>>
      <div class="container-fluid">
        <div class="row"><!-- Start Title Row -->
          <div class="col-lg-12 header">
            <div class="pull-left ">
              <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/pcsshare_logo.png" title="PCS Share Treatments" alt="PCS Share Treatments" class="site-logo">
              <a href="<?php echo site_url(); ?>" class="site-name">PCS Share</a>
            </div>
            <?php if ( pcs_get_current_user_id() === 0 ): ?>
            <span class="pull-right btn-sign-in" data-toggle="modal" data-target=".sign-in-modal">Sign in</span>
            <?php else: ?>
              <div class="dropdown pull-right">
                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                  <?php echo pcs_get_current_user_username(); ?>
                  <?php if ( bp_get_total_unread_messages_count( pcs_get_current_user_id() ) > 0 ) : ?>
                  <span class="badge badge-unread-messages"><?php echo bp_get_total_unread_messages_count( pcs_get_current_user_id() ); ?></span>
                  <?php endif; ?>
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                  <li><a href="<?php echo bp_core_get_user_domain( pcs_get_current_user_id() ); ?>">Profile</a></li>
                  <li><a href="<?php echo bp_core_get_user_domain( pcs_get_current_user_id() ) . '?profile-tab=inbox'; ?>">Inbox</a></li>
                  <li role="separator" class="divider"></li>
                  <li><a href="<?php echo wp_logout_url( pcs_get_current_url() ); ?> ">Sign out</a></li>
                </ul>
              </div>
            <?php endif; ?>
          </div>
        </div><!-- EO Top Tow  -->
      </div>

      <div class="modal fade sign-in-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm">
          <div class="modal-content">
            <div class="modal-body">
              <form method="post" action="<?php echo wp_login_url( pcs_get_current_url() ); ?>&wpe-login=pcsshare">
                <div class="form-group">
                  <label for="sign-in-username">Username</label>
                  <input type="text" class="form-control" placeholder="username" name="log" id="sign-in-username">
                </div>
                <div class="form-group">
                  <label for="sign-in-password">Password</label>
                  <input type="password" class="form-control" name="pwd" id="sign-in-password">
                </div>
                <div class="form-group">
                  <button class="btn">Sign in</button>
                  <a class="btn" href="<?php echo site_url('/register'); ?>">Register</a>
                </div>
                <div class="form-group">
                  <a href="<?php echo site_url('/wp-login.php?action=lostpassword'); ?>">Forgot your password?</a>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <nav class="navbar navbar-default"><!-- Start Nav Row -->
        <div class="container-fluid">
          <!-- toggle for better mobile display -->
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#site-navigation" aria-expanded="false">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
          </div>

          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="site-navigation">
            <?php pcs_show_navigation_menu( MAIN_NAVIGATION_MENU ); ?>
          </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
      </nav><!--EO Nav Row -->

      <div class="container-fluid">
