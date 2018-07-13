<?php if ( ! pcs_is_displayed_user_author() ): die('Can not read other people\'s messages.'); ?>
<?php endif; ?>

<div class="user-inbox">

    <?php get_template_part( 'profile/inbox-tabs' ); ?>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="inbox">
            <?php pcs_get_inbox_tab( ( ! empty( $_GET['inbox-tab'] ) ? $_GET['inbox-tab'] : '' ) ); ?>
        </div>
    </div>

</div>
