<?php

/* Here are some handy dandy utility helper functions */

/* dump(); - makes for easy debugging. <?php dump($post); ?> */
function dump($obj) {
	echo "<pre>";
	print_r($obj);
	echo "</pre>";
}

// UNHIDE KITCHEN SINK
function unhide_kitchensink( $args ) { 
	$args['wordpress_adv_hidden'] = false; return $args;
}

add_filter( 'tiny_mce_before_init', 'unhide_kitchensink' );

// STOP LINKING MY IMAGES, DAMMIT

function no_imagelink_setup() {
	$image_set = get_option( 'image_default_link_type' );

	if ($image_set !== 'none') {
		update_option('image_default_link_type', 'none');
	}
}
add_action('admin_init', 'no_imagelink_setup', 10);

// view counts

function getPostViews($postID){
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "0 Views";
    } elseif ($count == 1) {
    	return $count . ' View';
    } else {
    	return $count . ' Views';
    }
    
}
function setPostViews($postID) {
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}
// Remove issues with prefetching adding extra views
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

////////////////////////////
// CUSTOM ADMIN FOOTER
////////////////////////////
 
function remove_footer_admin () 
{
    echo '<span id="footer-thankyou">Developed by <a href="http://www.WHATEVER.com" target="_blank">YOUR NAME</a></span>';
}
add_filter('admin_footer_text', 'remove_footer_admin');

////////////////////////////
// USER ROLE AS BODY CLASS (ADMIN AND FRONT)
////////////////////////////

if ( is_user_logged_in() ) {
    add_filter('body_class','add_role_to_body'); // Front
    add_filter('admin_body_class','add_role_to_body'); // Admin
}
function add_role_to_body($classes) {
    $current_user = new WP_User(get_current_user_id());
    $user_role = array_shift($current_user->roles);
    if (is_admin()) {
        $classes .= ' role-'. $user_role;
    } else {
        $classes[] = ' role-'. $user_role;
    }
    return $classes;
}

////////////////////////////
// CUSTOM LOGIN PAGE
////////////////////////////

function my_custom_login() {
echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('stylesheet_directory') . '/login/login-styles.css" />'; // !!!!!!!!!!!!!!!!!!!
}
add_action('login_head', 'my_custom_login');

function login_error_override()
{
    return 'Incorrect login details.';
}
add_filter('login_errors', 'login_error_override');

function my_login_head() {
remove_action('login_head', 'wp_shake_js', 12);
}
add_action('login_head', 'my_login_head');

function my_login_logo_url() {
return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

function my_login_logo_url_title() {
return 'Your Site Name and Info';
}
add_filter( 'login_headertitle', 'my_login_logo_url_title' );