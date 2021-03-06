<?php
/** This entire theme is based on TwentyTen from WordPress 3.1. Edited as I saw fit ****/

/** Tell WordPress to run twentyten_setup() when the 'after_setup_theme' hook is run. */
add_action( 'after_setup_theme', 'twentyten_setup' );

if ( ! function_exists( 'twentyten_setup' ) ):

function twentyten_setup() {
    
    // Post Format support. You can also use the legacy "gallery" or "asides" (note the plural) categories. More info at http://codex.wordpress.org/Post_Formats
	add_theme_support( 'post-formats', array( 'aside', 'audio', 'gallery', 'quote', 'link', 'image', 'status', 'chat', 'video' ) );
	add_theme_support( 'post-thumbnails' ); // This theme uses Featured Images
	add_theme_support( 'automatic-feed-links' ); // Add default posts and comments RSS feed links to <head>

	// This theme uses wp_nav_menu() in one location. Add more as needed
	register_nav_menus( array( 'primary' => 'Primary Navigation' ) );
}
endif;

/** Sets the post excerpt length to 40 characters. */
function twentyten_excerpt_length( $length ) { return 40; }
add_filter( 'excerpt_length', 'twentyten_excerpt_length' );

/** Returns a "Continue Reading" link for excerpts. */
function twentyten_continue_reading_link() {
	return '<a href="'. get_permalink() . '"> Continue reading <span class="meta-nav">&rarr;</span></a>';
}

/** Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and twentyten_continue_reading_link(). */
function twentyten_auto_excerpt_more( $more ) {
	return ' &hellip;' . twentyten_continue_reading_link();
}
add_filter( 'excerpt_more', 'twentyten_auto_excerpt_more' );

/** Adds a pretty "Continue Reading" link to custom post excerpts. */
function twentyten_custom_excerpt_more( $output ) {
	if ( has_excerpt() && ! is_attachment() ) {
		$output .= twentyten_continue_reading_link();
	}
	return $output;
}
add_filter( 'get_the_excerpt', 'twentyten_custom_excerpt_more' );
/* Get wp_nav_menu() fallback, wp_page_menu(), to show home link. */
function wpst_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'wpst_page_menu_args' );
/** Remove inline styles printed when the gallery shortcode is used. */
function twentyten_remove_gallery_css( $css ) {
	return preg_replace( "#<style type='text/css'>(.*?)</style>#s", '', $css );
}
add_filter( 'gallery_style', 'twentyten_remove_gallery_css' );

if ( ! function_exists( 'twentyten_comment' ) ) :
/** Template for comments and pingbacks. */
function twentyten_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'twentyeleven' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'twentyeleven' ), ' ' ); ?></p>
	<?php
			break;
		default :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<footer class="comment-meta">
				<div class="comment-author vcard">
					<?php
						$avatar_size = 68;
						if ( '0' != $comment->comment_parent )
							$avatar_size = 39;

						echo get_avatar( $comment, $avatar_size );

						/* translators: 1: comment author, 2: date and time */
						printf( __( '%1$s on %2$s <span class="says">said:</span>', 'twentyeleven' ),
							sprintf( '<span class="fn">%s</span>', get_comment_author_link() ),
							sprintf( '<a href="%1$s"><time pubdate datetime="%2$s">%3$s</time></a>',
								esc_url( get_comment_link( $comment->comment_ID ) ),
								get_comment_time( 'c' ),
								/* translators: 1: date, 2: time */
								sprintf( __( '%1$s at %2$s', 'twentyeleven' ), get_comment_date(), get_comment_time() )
							)
						);
					?>

					<?php edit_comment_link( __( '[Edit]', 'twentyeleven' ), ' ' ); ?>
				</div><!-- .comment-author .vcard -->

				<?php if ( $comment->comment_approved == '0' ) : ?>
					<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'twentyeleven' ); ?></em>
					<br />
				<?php endif; ?>

			</footer>

			<div class="comment-content"><?php comment_text(); ?></div>

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply &darr;', 'twentyeleven' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->

	<?php
			break;
	endswitch;
}
endif;

/** Prints HTML with meta information for the current post—date/time and author. */
function twentyten_posted_on() {
	printf( __( '<span class="sep">Posted on </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="by-author"> <span class="sep"> by </span> <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'twentyeleven' ),
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		sprintf( esc_attr__( 'View all posts by %s', 'twentyeleven' ), get_the_author() ),
		esc_html( get_the_author() )
	);
}

if ( ! function_exists( 'twentyten_posted_in' ) ) :
/** Prints HTML with meta information for the current post (category, tags and permalink). */
function twentyten_posted_in() {
	// Retrieves tag list of current post, separated by commas.
	$tag_list = get_the_tag_list( '', ', ' );
	if ( $tag_list ) {
		$posted_in = 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.';
	} elseif ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
		$posted_in = 'This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.';
	} else {
		$posted_in = 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.';
	}
	// Prints the string, replacing the placeholders.
	printf(
		$posted_in,
		get_the_category_list( ', ' ),
		$tag_list,
		get_permalink(),
		the_title_attribute( 'echo=0' )
	);
}
endif;

/*** Custom Post Types **********************************************/
//add_action( 'init', 'wpst_create_my_post_types' );

//Gallery is just an example. Change as needed.
function wpst_create_my_post_types() {
	register_post_type( 'GALLERY',
		array(
			'labels' => array(
			'name' => __( 'Galleries' ),
			'singular_name' => __( 'Gallery' ),
			'add_new' => __( 'New Gallery' ),
			'add_new_item' => __( 'Add New Gallery' ),
			'edit' => __( 'Change' ),
			'edit_item' => __( 'Change the Gallery' ),
			'new_item' => __( 'A New Gallery' ),
			'view' => __( 'See' ),
			'view_item' => __( 'See the Gallery' ),
			'search_items' => __( 'Search Galleries' ),
			'not_found' => __( 'No Gallery to display' ),
			'not_found_in_trash' => __( 'No Galleries discarded' ),
			'parent' => __( 'Parent Gallery' ),
			'_builtin' => false, // It's a custom post type, not built in!
			'rewrite' => array('slug' => 'gallery', 'with_front' => FALSE), // Permalinks format
			),
			'public' => true,
			'show_ui' => true,
			'description' => __( 'Galleries for displaying your work' ),
			'query_var' => true,
			'supports' => array( 'title', 'editor', 'custom-fields', 'thumbnail' ),
			//'menu_icon' => get_stylesheet_directory_uri() . '/images/images_icon.png',
		)
	);
}

// browser detection via body_class
add_filter('body_class','wpst_browser_body_class');

function wpst_browser_body_class($classes) {
    //WordPress global vars available.
    global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

    if($is_lynx)       $classes[] = 'lynx';
    elseif($is_gecko)  $classes[] = 'gecko';
    elseif($is_opera)  $classes[] = 'opera';
    elseif($is_NS4)    $classes[] = 'ns4';
    elseif($is_safari) $classes[] = 'safari';
    elseif($is_chrome) $classes[] = 'chrome';
    elseif($is_IE)     $classes[] = 'ie';
    else               $classes[] = 'unknown';

    if($is_iphone) $classes[] = 'iphone';
    
    //Adds a class of singular too when appropriate
    if ( is_singular() && ! is_home() ) $classes[] = 'singular';
    
    return $classes;
}
// Customize footer text
function wpst_remove_footer_admin () {
    //echo "Your own text";
} 
 
//add_filter('admin_footer_text', 'wpst_remove_footer_admin');

/*** Default Settings Cleanup and Adding Goodies **************************/

/*
// Remove feed urls
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'feed_links', 2 );
*/

//removes version number
remove_action('wp_head', 'wp_generator');

/* adds the favicon/appleicon to the wp_head() call*/
function wpst_blog_favicon() { echo '<link rel="shortcut icon" href="'.get_bloginfo('url').'/favicon.ico" />'; }
add_action('wp_head', 'wpst_blog_favicon');

function wpst_apple_icon() { echo '<link rel="apple-touch-icon" href="'.get_bloginfo('url').'/apple-touch-icon.png" />'; }
add_action('wp_head', 'wpst_apple_icon');

//Disable EditURI and WLWManifest
function wpst_remheadlink() {
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
}
add_action('init', 'wpst_remheadlink');

/*	Checks to see if we should blame nacin 
	@return bool true if we should blame nacin, false if we shouldn't */
function maybe_blame_nacin(){ 
    return true; 
} 

//removes Admin bar
wp_deregister_script('admin-bar');
wp_deregister_style('admin-bar');
remove_action('wp_footer','wp_admin_bar_render',1000);

// Includes the widgets.php file that defines all widget based functions. Done to clean up this file Uncomment to use.
require_once( TEMPLATEPATH . '/widgets.php' );
?>
