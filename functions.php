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

////////////////////////////
// PAGING NAVIGATION
// SEE: https://mor10.com/add-proper-pagination-default-wordpress-themes/
////////////////////////////

if ( ! function_exists( 'yourtheme_paging_nav' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable.
 * Based on paging nav function from Twenty Fourteen
 */

function yourtheme_paging_nav() {
    // Don't print empty markup if there's only one page.
    if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
        return;
    }

    $paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
    $pagenum_link = html_entity_decode( get_pagenum_link() );
    $query_args   = array();
    $url_parts    = explode( '?', $pagenum_link );

    if ( isset( $url_parts[1] ) ) {
        wp_parse_str( $url_parts[1], $query_args );
    }

    $pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
    $pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

    $format  = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
    $format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';

    // Set up paginated links.
    $links = paginate_links( array(
        'base'     => $pagenum_link,
        'format'   => $format,
        'total'    => $GLOBALS['wp_query']->max_num_pages,
        'current'  => $paged,
        'mid_size' => 3,
        'add_args' => array_map( 'urlencode', $query_args ),
        'prev_text' => __( '&larr; Previous', 'yourtheme' ),
        'next_text' => __( 'Next &rarr;', 'yourtheme' ),
        'type'      => 'list',
    ) );

    if ( $links ) :

    ?>
    <nav class="navigation paging-navigation" role="navigation">
        <h1 class="screen-reader-text"><?php _e( 'Posts navigation', 'yourtheme' ); ?></h1>
            <?php echo $links; ?>
    </nav><!-- .navigation -->
    <?php
    endif;
}
endif;

////////////////////////////
// ID IN TOP ADMIN BAR
////////////////////////////

class id_in_menu_bar {

  function __construct( ) {
    add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100 );
  }

  function admin_bar_menu( $wp_admin_bar ) {
    $wp_admin_bar->add_menu( array(
      'id'    => 'id',
      'title' => 'ID:   ' . get_the_id(),
    ) );
  }
}

$display = new id_in_menu_bar();

////////////////////////////
// remove wp version param from any enqueued scripts
// from: http://wordpress.stackexchange.com/questions/132282/removing-wordpress-version-number-from-included-files
////////////////////////////

function vc_remove_wp_ver_css_js( $src ) {
    if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}
add_filter( 'style_loader_src', 'vc_remove_wp_ver_css_js', 9999 );
add_filter( 'script_loader_src', 'vc_remove_wp_ver_css_js', 9999 );