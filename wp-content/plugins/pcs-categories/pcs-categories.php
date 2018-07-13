<?php

/*
 * Plugin Name: PCS Categories
 * Plugin URI: http://pcssharetreatments.com
 * Description: Utility functions for easier working with categories.
 * Author: Hrvoje Gazibara
 * Version: 1.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * Category Breadcrumbs
 */
function pcs_get_category_breadcrumbs($category, $separator = ' &ndash; ') {
	if ( is_null( $category ) ) {
		return '';
	}
    $breadcrumbs = get_category_parents( $category->term_id, true, $separator );
    if ( substr( $breadcrumbs, -strlen($separator) ) === $separator ) {
        return substr( $breadcrumbs, 0, strlen($breadcrumbs) - strlen($separator) );
    }
    return $breadcrumbs;
}

function pcs_show_category_breadcrumbs($category, $should_remove_last_link = true, $separator = ' &ndash; ') {
    $category_breadcrumbs = pcs_get_category_breadcrumbs( $category, $separator );
	echo $should_remove_last_link ? pcs_remove_last_breadcrumb_link( $category_breadcrumbs ) : $category_breadcrumbs;
}

function pcs_get_last_breadcrumb_link($breadcrumbs) {
	preg_match_all( '/href="(?P<href>.+?)"/', $breadcrumbs, $matches );
	return array_slice( $matches['href'], -1 )[0];
}

function pcs_remove_last_breadcrumb_link($breadcrumbs) {
	return preg_replace('/(?!^)<a(?:.*?)>(.*?)<\/a>(?=$)/', '\\1', $breadcrumbs);
}

/**
 * Subcategories Box
 */
function pcs_get_current_category_slug() {
    $category = pcs_get_current_category();
    return is_null( $category ) ? null : $category->slug;
}

function pcs_get_current_category_id() {
    $category = pcs_get_current_category();
    return is_null( $category ) ? null : $category->term_id;
}

function pcs_get_current_category() {
    $cat = get_query_var( 'cat' );
    return get_category( $cat );
}

function pcs_get_parent_category($category) {
    if ( ! $category->parent ) {
        return $category;
    }

    return get_term_by( 'id', $category->parent, 'category' );
}

function pcs_get_subcategories_list($category, $current_category = null) {
    if ( ! $category ) {
        return '';
    }

	$menu = wp_get_nav_menu_object( MAIN_NAVIGATION_MENU );
    $menu_items = wp_get_nav_menu_items( $menu->term_id );
	list( $menu_parents, $menu_children ) = pcs_build_menu_parent_child_relationship( $menu_items );

	$menu_category = pcs_find_category_in_menu( $category, $menu_parents );

	if ( is_null( $menu_category ) || empty( $menu_children[$menu_category->ID] ) ) {
		return '';
	}

    $markup = [];
    $markup[] = '<ul class="category-list">';

    foreach ( $menu_children[$menu_category->ID] as $subcategory ) {
        $markup[] = sprintf(
            '<li><a href="%s"%s>%s</a></li>',
            $subcategory->url,
            get_subcategory_class( $subcategory, $current_category ),
            $subcategory->title
        );
    }

    $markup[] = '</ul>';

    return implode("\n", $markup);
}

function pcs_find_category_in_menu($category, $items) {
	foreach ( $items as $item ) {
		if ( $item->title === $category->name ) {
			return $item;
		}
	}

	return null;
}

function get_subcategory_class($subcategory, $current_category) {
    return $subcategory->title === $current_category->name ? ' class="subcat-active" ' : '';
}

function pcs_show_subcategories_list($category, $current_category = null) {
    echo pcs_get_subcategories_list( $category, $current_category );
}
