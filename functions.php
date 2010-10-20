<?php
/**
 * Rather than lumping all theme functions into a single file, this functions file is used for 
 * initializing the theme framework, which activates files in the order that it needs. Users
 * should create a child theme and make changes to its functions.php file (not this one).
 *
 * @package Hybrid
 * @subpackage Functions
 */

/* Load the Hybrid class. */
require_once( TEMPLATEPATH . '/library/hybrid.php' );

/* Initialize the Hybrid framework. */
$hybrid = new Hybrid();

/* Do theme setup on the 'after_setup_theme' hook. */
add_action( 'after_setup_theme', 'hybrid_setup_theme' );

/**
 * Function for setting up all the Hybrid parent theme default actions and supported features.  This structure 
 * should be followed when creating custom parent themes with the Hybrid Core framework.
 *
 * @since 0.9
 */
function hybrid_setup_theme() {

	/* Get the theme prefix. */
	$prefix = hybrid_get_prefix();

	/* Add support for automatic feed links. */
	add_theme_support( 'automatic-feed-links' );

	/* Add support for the core template hierarchy. */
	add_theme_support( 'hybrid-core-template-hierarchy' );

	/* Add support for deprecated functions. */
	add_theme_support( 'hybrid-core-deprecated' );

	/* Add support for the core sidebars. */
	add_theme_support( 'hybrid-core-sidebars' );

	/* Add support for the core widgets. */
	add_theme_support( 'hybrid-core-widgets' );

	/* Add support for the core shortcodes. */
	add_theme_support( 'hybrid-core-shortcodes' );

	/* Add support for the core menus. */
	if ( hybrid_get_setting( 'use_menus' ) )
		add_theme_support( 'hybrid-core-menus' );

	/* Add support for the core post meta box. */
	add_theme_support( 'hybrid-core-post-meta-box' );

	/* Add support for the core SEO feature. */
	if ( !hybrid_get_setting( 'seo_plugin' ) )
		add_theme_support( 'hybrid-core-seo' );

	/* Add support for the core drop-downs script. */
	if ( hybrid_get_setting( 'superfish_js' ) )
		add_theme_support( 'hybrid-core-drop-downs' );

	/* Add support for the core print stylesheet. */
	if ( hybrid_get_setting( 'print_style' ) )
		add_action( 'template_redirect', 'hybrid_theme_enqueue_style' );

	/* Add support for core theme settings meta boxes. */
	add_theme_support( 'hybrid-core-theme-settings' );
	add_theme_support( 'hybrid-core-meta-box-general' );
	add_theme_support( 'hybrid-core-meta-box-footer' );

	/* Add support for the breadcrumb trail extension. */
	add_theme_support( 'breadcrumb-trail' );

	/* Add support for the custom field series extension. */
	add_theme_support( 'custom-field-series' );

	/* Add support for the Get the Image extension. */
	add_theme_support( 'get-the-image' );

	/* Add support for the Post Stylesheets extension. */
	add_theme_support( 'post-stylesheets' );

	/* If no child theme is active, add support for the Post Layouts and Pagination extensions. */
	if ( 'hybrid' == get_stylesheet() ) {
		add_theme_support( 'post-layouts' );
		add_theme_support( 'loop-pagination' );
	}

	/* Register sidebars. */
	add_action( 'init', 'hybrid_theme_register_sidebars' );

	/* Header actions. */
	add_action( "{$prefix}_header", 'hybrid_site_title' );
	add_action( "{$prefix}_header", 'hybrid_site_description' );

	/* Load the correct menu. */
	if ( hybrid_get_setting( 'use_menus' ) )
		add_action( "{$prefix}_after_header", 'hybrid_get_primary_menu' );
	else
		add_action( "{$prefix}_after_header", 'hybrid_page_nav' );

	/* Add the primary and secondary sidebars after the container. */
	add_action( "{$prefix}_after_container", 'hybrid_get_primary' );
	add_action( "{$prefix}_after_container", 'hybrid_get_secondary' );

	/* Add the breadcrumb trail and before content sidebar before the content. */
	add_action( "{$prefix}_before_content", 'hybrid_breadcrumb' );
	add_action( "{$prefix}_before_content", 'hybrid_get_utility_before_content' );

	/* Add the title, byline, and entry meta before and after the entry. */
	add_action( "{$prefix}_before_entry", 'hybrid_entry_title' );
	add_action( "{$prefix}_before_entry", 'hybrid_byline' );
	add_action( "{$prefix}_after_entry", 'hybrid_entry_meta' );

	/* Add the after singular sidebar and custom field series extension after singular views. */
	add_action( "{$prefix}_after_singular", 'hybrid_get_utility_after_singular' );
	add_action( "{$prefix}_after_singular", 'custom_field_series' );

	/* Add the after content sidebar and navigation links after the content. */
	add_action( "{$prefix}_after_content", 'hybrid_get_utility_after_content' );
	add_action( "{$prefix}_after_content", 'hybrid_navigation_links' );

	/* Add the subsidiary sidebar and footer insert to the footer. */
	add_action( "{$prefix}_before_footer", 'hybrid_get_subsidiary' );
	add_action( "{$prefix}_footer", 'hybrid_footer_insert' );

	/* Add the comment avatar and comment meta before individual comments. */
	add_action( "{$prefix}_before_comment", 'hybrid_avatar' );
	add_action( "{$prefix}_before_comment", 'hybrid_comment_meta' );

	/* Add Hybrid theme-specific body classes. */
	add_filter( 'body_class', 'hybrid_theme_body_class' );

	/* Add elements to the <head> area. */
	//add_action( "{$prefix}_head", 'hybrid_meta_content_type' );
	//add_action( 'wp_head', 'hybrid_favicon' );

	/* Feed links. */
	add_filter( 'feed_link', 'hybrid_feed_link', 1, 2 );
	add_filter( 'category_feed_link', 'hybrid_other_feed_link' );
	add_filter( 'author_feed_link', 'hybrid_other_feed_link' );
	add_filter( 'tag_feed_link', 'hybrid_other_feed_link' );
	add_filter( 'search_feed_link', 'hybrid_other_feed_link' );

	/* Remove WP and plugin functions. */
	add_action( 'wp_print_styles', 'hybrid_disable_styles' );

	add_action( "load-appearance_page_theme-settings", 'hybrid_theme_create_settings_meta_boxes', 11 );

		/* Add same filters to user description as term descriptions. */
		add_filter( 'get_the_author_description', 'wptexturize' );
		add_filter( 'get_the_author_description', 'convert_chars' );
		add_filter( 'get_the_author_description', 'wpautop' );

		//add_action( 'wp_head', 'hybrid_head_pingback' );
}

/**
 * Function to load CSS at an appropriate time. Adds print.css if user chooses to use it. 
 * Users should load their own CSS using wp_enqueue_style() in their child theme's 
 * functions.php file.
 *
 * @since 0.9
 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_style
 */
function hybrid_theme_enqueue_style() {
	global $wp_query;

	/* If is admin, don't load styles. */
	if ( is_admin() )
		return;

	/* Get the theme prefix. */
	$prefix = hybrid_get_prefix();

	/* Load the print stylesheet. */
	wp_enqueue_style( "{$prefix}-print", esc_url( apply_atomic( 'print_style', THEME_URI . '/css/print.css' ) ), false, 0.7, 'print' );
}

function hybrid_theme_create_settings_meta_boxes() {
	$domain = hybrid_get_textdomain();
	$prefix = hybrid_get_prefix();
	/* Creates a meta box for the general theme settings. */
	add_meta_box( "{$prefix}-general-settings-meta-box", __( 'General settings', $domain ), 'hybrid_general_settings_meta_box', 'appearance_page_theme-settings', 'normal', 'high' );
}

/**
 * Adds a general settings suite suitable for the average theme, which includes a print stylesheet,
 * drop-downs JavaScript option, and the ability to change the feed URL.
 *
 * @since 0.7
 */
function hybrid_general_settings_meta_box() {
	$domain = hybrid_get_textdomain(); ?>

	<table class="form-table">

		<tr>
			<th><label for="<?php echo hybrid_settings_field_id( 'print_style' ); ?>"><?php _e( 'Stylesheets:', $domain ); ?></label></th>
			<td>
				<input id="<?php echo hybrid_settings_field_id( 'print_style' ); ?>" name="<?php echo hybrid_settings_field_name( 'print_style' ); ?>" type="checkbox" <?php if ( hybrid_get_setting( 'print_style' ) ) echo 'checked="checked"'; ?> value="true" /> 
				<label for="<?php echo hybrid_settings_field_id( 'print_style' ); ?>"><?php _e( 'Select this to have the theme automatically include a print stylesheet.', $domain ); ?></label>
			</td>
		</tr>
		<tr>
			<th><label for="<?php echo hybrid_settings_field_id( 'superfish_js' ); ?>"><?php _e( 'JavaScript:', $domain ); ?></label></th>
			<td>
				<input id="<?php echo hybrid_settings_field_id( 'superfish_js' ); ?>" name="<?php echo hybrid_settings_field_name( 'superfish_js' ); ?>" type="checkbox" <?php if ( hybrid_get_setting( 'superfish_js' ) ) echo 'checked="checked"'; ?> value="true" /> 
				<label for="<?php echo hybrid_settings_field_id( 'superfish_js' ); ?>"><?php _e( 'Include the drop-down menu JavaScript.', $domain ); ?></label>
			</td>
		</tr>
		<?php if ( 'hybrid' == get_template() ) { // Only show if 'hybrid' is the template ?>
		<tr>
			<th><label for="<?php echo hybrid_settings_field_id( 'use_menus' ); ?>"><?php _e( 'Menus:', $domain ); ?></label></th>
			<td>
				<input id="<?php echo hybrid_settings_field_id( 'use_menus' ); ?>" name="<?php echo hybrid_settings_field_name( 'use_menus' ); ?>" type="checkbox" <?php if ( hybrid_get_setting( 'use_menus' ) ) echo 'checked="checked"'; ?> value="true" /> 
				<label for="<?php echo hybrid_settings_field_id( 'use_menus' ); ?>"><?php _e( 'Use the WordPress 3.0+ menu system? Child themes built prior to <em>Hybrid</em> 0.8 may need to be updated to use this.', $domain ); ?></label>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th><label for="<?php echo hybrid_settings_field_id( 'feed_url' ); ?>"><?php _e( 'Feeds:', $domain ); ?></label></th>
			<td>
				<input id="<?php echo hybrid_settings_field_id( 'feed_url' ); ?>" name="<?php echo hybrid_settings_field_name( 'feed_url' ); ?>" type="text" value="<?php echo hybrid_get_setting( 'feed_url' ); ?>" size="30" /><br />
				<?php _e( 'If you have an alternate feed address, such as one from <a href="http://feedburner.com" title="Feedburner">Feedburner</a>, you can enter it here to have the theme redirect your feed links.', $domain ); ?><br /><br />
				<input id="<?php echo hybrid_settings_field_id( 'feeds_redirect' ); ?>" name="<?php echo hybrid_settings_field_name( 'feeds_redirect' ); ?>" type="checkbox" <?php if ( hybrid_get_setting( 'feeds_redirect' ) ) echo 'checked="checked"'; ?> value="true" /> 
				<label for="<?php echo hybrid_settings_field_id( 'feeds_redirect' ); ?>"><?php _e( 'Direct category, tag, search, and author feeds to your alternate feed address?', $domain ); ?></label>
			</td>
		</tr>
		<tr>
			<th><label for="<?php echo hybrid_settings_field_id( 'seo_plugin' ); ?>"><acronym title="<?php _e( 'Search Engine Optimization', $domain ); ?>"><?php _e( 'SEO:', $domain ); ?></acronym></label></th>
			<td>
				<input id="<?php echo hybrid_settings_field_id( 'seo_plugin' ); ?>" name="<?php echo hybrid_settings_field_name( 'seo_plugin' ); ?>" type="checkbox" <?php if ( hybrid_get_setting( 'seo_plugin' ) ) echo 'checked="checked"'; ?> value="true" /> 
				<label for="<?php echo hybrid_settings_field_id( 'seo_plugin' ); ?>"><?php _e( 'Are you using an <acronym title="Search Engine Optimization">SEO</acronym> plugin? Select this to disable the theme\'s meta and indexing features.', $domain ); ?></label>
			</td>
			</tr>

	</table><!-- .form-table --><?php
}

/**
 * Function for adding extra sidebars.
 *
 * @since 0.9
 */
function hybrid_theme_register_sidebars() {

	$domain = hybrid_get_textdomain();

	register_sidebar( array( 'name' => __( 'Widgets Template', $domain ), 'id' => 'widgets-template', 'description' => __( 'Used as the content of the Widgets page template.', $domain ), 'before_widget' => '<div id="%1$s" class="widget %2$s widget-%2$s"><div class="widget-inside">', 'after_widget' => '</div></div>', 'before_title' => '<h3 class="widget-title">', 'after_title' => '</h3>' ) );
	register_sidebar( array( 'name' => __( '404 Template', $domain ), 'id' => 'error-404-template', 'description' => __( 'Replaces the default 404 error page content.', $domain ), 'before_widget' => '<div id="%1$s" class="widget %2$s widget-%2$s"><div class="widget-inside">', 'after_widget' => '</div></div>', 'before_title' => '<h3 class="widget-title">', 'after_title' => '</h3>' ) );
}

/**
 * Function for adding Hybrid theme <body> classes.
 *
 * @since 0.9
 */
function hybrid_theme_body_class( $classes ) {
	global $wp_query, $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome;

	/* Singular post classes (deprecated). */
	if ( is_singular() ) {

		if ( is_page() )
			$classes[] = "page-{$wp_query->post->ID}"; // Use singular-page-ID

		elseif ( is_singular( 'post' ) )
			$classes[] = "single-{$wp_query->post->ID}"; // Use singular-post-ID
	}
	elseif ( is_tax() || is_category() || is_tag() ) {
		$term = $wp_query->get_queried_object();
		$classes[] = "taxonomy-{$term->taxonomy}";
		$classes[] = "taxonomy-{$term->taxonomy}-" . sanitize_html_class( $term->slug, $term->term_id );
	}

	/* Browser detection. */
	$browsers = array( 'gecko' => $is_gecko, 'opera' => $is_opera, 'lynx' => $is_lynx, 'ns4' => $is_NS4, 'safari' => $is_safari, 'chrome' => $is_chrome, 'msie' => $is_IE );
	foreach ( $browsers as $key => $value ) {
		if ( $value ) {
			$classes[] = $key;
			break;
		}
	}

	/* Hybrid theme widgets detection. */
	foreach ( array( 'primary', 'secondary', 'subsidiary' ) as $sidebar )
		$classes[] = ( is_active_sidebar( $sidebar ) ) ? "{$sidebar}-active" : "{$sidebar}-inactive";

	if ( in_array( 'primary-inactive', $classes ) && in_array( 'secondary-inactive', $classes ) && in_array( 'subsidiary-inactive', $classes ) )
		$classes[] = 'no-widgets';

	return $classes;
}

/**
 * Displays the breadcrumb trail.  Calls the get_the_breadcrumb() function.
 * Use the get_the_breadcrumb_args filter hook.  The hybrid_breadcrumb_args 
 * filter is deprecated.
 *
 * @deprecated 0.5 Theme still needs this function.
 * @todo Find an elegant way to transition to breadcrumb_trail() 
 * in child themes and filter breadcrumb_trail_args instead.
 *
 * @since 0.1
 */
function hybrid_breadcrumb() {
	if ( current_theme_supports( 'breadcrumb-trail' ) )
		breadcrumb_trail( array( 'front_page' => false, 'singular_post_taxonomy' => 'category' ) );
}

/**
 * Filters main feed links for the site.  This changes the feed links  to the user's 
 * alternate feed URL.  This change only happens if the user chooses it from the 
 * theme settings.
 *
 * @since 0.4
 * @param string $output
 * @param string $feed
 * @return string $output
 */
function hybrid_feed_link( $output, $feed ) {

	$url = esc_url( hybrid_get_setting( 'feed_url' ) );

	if ( $url ) {
		$outputarray = array( 'rss' => $url, 'rss2' => $url, 'atom' => $url, 'rdf' => $url, 'comments_rss2' => '' );
		$outputarray[$feed] = $url;
		$output = $outputarray[$feed];
	}

	return $output;
}

/**
 * Filters the category, author, and tag feed links.  This changes all of these feed 
 * links to the user's alternate feed URL.  This change only happens if the user chooses 
 * it from the theme settings.
 *
 * @since 0.4
 * @param string $link
 * @return string $link
 */
function hybrid_other_feed_link( $link ) {

	if ( hybrid_get_setting( 'feeds_redirect' ) && $url = hybrid_get_setting( 'feed_url' ) )
		$link = esc_url( $url );

	return $link;
}

/**
 * Displays the default entry title.  Wraps the title in the appropriate header tag. 
 * Use the hybrid_entry_title filter to customize.
 *
 * @since 0.5
 */
function hybrid_entry_title( $title = '' ) {
	if ( !$title )
		$title =  hybrid_entry_title_shortcode();

	echo apply_atomic_shortcode( 'entry_title', $title );
}

/**
 * Default entry byline for posts.  Shows the author, date, and edit link.  Use the 
 * hybrid_byline filter to customize.
 *
 * @since 0.5
 */
function hybrid_byline( $byline = '' ) {
	global $post;

	if ( $byline )
		$byline = '<p class="byline">' . $byline . '</p>';

	elseif ( 'post' == $post->post_type && 'link_category' !== get_query_var( 'taxonomy' ) )
		$byline = '<p class="byline">' . __( '<span class="byline-prep byline-prep-author">By</span> [entry-author] <span class="byline-prep byline-prep-published">on</span> [entry-published] [entry-edit-link before="| "]', hybrid_get_textdomain() ) . '</p>';

	echo apply_atomic_shortcode( 'byline', $byline );
}

/**
 * Displays the default entry metadata.  Shows the category, tag, and comments 
 * link.  Use the hybrid_entry_meta filter to customize.
 *
 * @since 0.5
 */
function hybrid_entry_meta( $metadata = '' ) {
	global $post;

	$domain = hybrid_get_textdomain();

	if ( $metadata )
		$metadata = '<p class="entry-meta">' . $metadata . '</p>';

	elseif ( 'post' == $post->post_type )
		$metadata = '<p class="entry-meta">[entry-terms taxonomy="category" before="' . __( 'Posted in', $domain ) . ' "] [entry-terms taxonomy="post_tag" before="| ' . __( 'Tagged', $domain ) . ' "] [entry-comments-link before="| "]</p>';

	elseif ( is_page() && current_user_can( 'edit_pages' ) )
		$metadata = '<p class="entry-meta">[entry-edit-link]</p>';

	echo apply_atomic_shortcode( 'entry_meta', $metadata, $post->ID );
}

/**
 * Function for displaying a comment's metadata.
 *
 * @since 0.7.0
 * @param string $metadata Custom metadata to use.
 * @global $comment The global comment object.
 * @global $post The global post object.
 */
function hybrid_comment_meta( $metadata = '' ) {
	global $comment, $post;

	if ( !$metadata )
		$metadata = '[comment-author] [comment-published] [comment-permalink before="| "] [comment-edit-link before="| "] [comment-reply-link before="| "]';

	$metadata = '<div class="comment-meta comment-meta-data">' . $metadata . '</div>';

	echo do_shortcode( apply_filters( hybrid_get_prefix() . '_comment_meta', $metadata ) );
}

/**
 * Disables stylesheets for particular plugins to allow the theme to easily write its own
 * styles for the plugins' features.
 *
 * @since 0.7
 * @link http://wordpress.org/extend/plugins/wp-pagenavi
 */
function hybrid_disable_styles() {
	/* Deregister the WP PageNavi plugin style. */
	wp_deregister_style( 'wp-pagenavi' );
}

/**
 * Checks for a user-uploaded favicon in the child theme's /images folder.  If it 
 * exists, display the <link> element for it.
 *
 * @since 0.4
 */
function hybrid_favicon() {
	$favicon = '';

	if ( file_exists( CHILD_THEME_DIR . '/images/favicon.ico' ) )
		$favicon =  '<link rel="shortcut icon" type="image/x-icon" href="' . CHILD_THEME_URI . '/images/favicon.ico" />' . "\n";
	echo apply_atomic( 'favicon', $favicon );
}

/**
 * Loads the navigation-links.php template file for use on archives, single posts,
 * and attachments. Developers can overwrite this individual template within
 * their custom child themes.
 *
 * @since 0.2
 * @uses get_template_part() Checks for template in child and parent theme.
 */
function hybrid_navigation_links() {
	get_template_part( 'navigation-links' );
}

/**
 * Displays the footer insert from the theme settings page. Users can also use shortcodes in their footer 
 * area, which will be displayed with this function.
 *
 * @since 0.2.1
 * @uses do_shortcode() Allows users to add shortcodes to their footer.
 * @uses stripslashes() Strips any slashes added from the admin form.
 * @uses hybrid_get_setting() Grabs the 'footer_insert' theme setting.
 */
function hybrid_footer_insert() {
	$footer_insert = do_shortcode( hybrid_get_setting( 'footer_insert' ) );

	if ( !empty( $footer_insert ) )
		echo '<div class="footer-insert">' . $footer_insert . '</div>';
}

/* Disables widget areas. */
add_filter( 'sidebars_widgets', 'remove_sidebars' );

/**
 * Removes all widget areas on the No Widgets page template. We're only going to run 
 * it on the No Widgets template. Users that need additional templates without widgets 
 * should create a simliar function in their child theme.
 *
 * @since 0.5.0
 * @uses sidebars_widgets Filter to remove all widget areas
 */
function remove_sidebars( $sidebars_widgets ) {
	global $wp_query;

	if ( is_singular() ) {
		$template = get_post_meta( $wp_query->post->ID, "_wp_{$wp_query->post->post_type}_template", true );
		if ( 'no-widgets.php' == $template || "{$wp_query->post->post_type}-no-widgets.php" == $template )
			$sidebars_widgets = array( false );
	}
	return $sidebars_widgets;
}

/**
 * Loads the Primary widget area. Users can overwrite 'sidebar-primary.php'.
 *
 * @since 0.2.2
 * @uses get_sidebar() Checks for the template in the child and parent theme.
 */
function hybrid_get_primary() {
	get_sidebar( 'primary' );
}

/**
 * Loads the Secondary widget area. Users can overwrite 'sidebar-secondary.php'.
 *
 * @since 0.2.2
 * @uses get_sidebar() Checks for the template in the child and parent theme.
 */
function hybrid_get_secondary() {
	get_sidebar( 'secondary' );
}

/**
 * Loads the Subsidiary widget area. Users can overwrite 'sidebar-subsidiary.php'.
 *
 * @since 0.3.1
 * @uses get_sidebar() Checks for the template in the child and parent theme.
 */
function hybrid_get_subsidiary() {
	get_sidebar( 'subsidiary' );
}

/**
 * Loads the Utility: Before Content widget area. Users can overwrite 
 * 'sidebar-before-content.php' in child themes.
 *
 * @since 0.4.0
 * @uses get_sidebar() Checks for the template in the child and parent theme.
 */
function hybrid_get_utility_before_content() {
	get_sidebar( 'before-content' );
}

/**
 * Loads the Utility: After Content widget area. Users can overwrite 
 * 'sidebar-after-content.php' in child themes.
 *
 * @since 0.4.0
 * @uses get_sidebar() Checks for the template in the child and parent theme.
 */
function hybrid_get_utility_after_content() {
	get_sidebar( 'after-content' );
}

/**
 * Loads the Utility: After Singular widget area. Users can overwrite 
 * 'sidebar-after-singular.php' in child themes.
 *
 * @since 0.7.0
 * @uses get_sidebar() Checks for the template in the child and parent theme.
 */
function hybrid_get_utility_after_singular() {
	get_sidebar( 'after-singular' );
}

/**
 * Loads the 'Primary Menu' template file.  Users can overwrite menu-primary.php in their child
 * theme folder.
 *
 * @since 0.8.0
 * @uses get_template_part() Checks for template in child and parent theme.
 * @link http://codex.wordpress.org/Function_Reference/get_template_part
 */
function hybrid_get_primary_menu() {
	get_template_part( 'menu', 'primary' );
}

?>