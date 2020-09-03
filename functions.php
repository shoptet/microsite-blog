<?php

/**
 * Load translations
 */
add_action( 'after_setup_theme', function () {
  $domain = 'shoptet';
  $locale = apply_filters( 'theme_locale', determine_locale(), $domain );
  $mofile = $domain . '-' . $locale . '.mo';
  $path = get_template_directory() . '/languages/' . $mofile;
  load_textdomain( $domain, $path );
} );

function prepend_event_date ( $content ) {
  $options = get_fields( 'options' );
  $event_category_id = $options[ 'event_category_id' ];

  if ( ! has_category( $event_category_id ) ) return $content;

  $date_format = get_option( 'date_format' );

  $date_html = '<p class="' . ( is_singular() ? 'h3' : '' ) . '">';
  $date_html .= '<strong>' . __( 'Datum konání:', 'shp-blog' ) . '</strong>';

  // From date
  if ( $date_from = get_field( 'event_date_from' ) ) {
    $date_from = strtotime( $date_from );
    $date_html .= ' ' . date_i18n( $date_format, $date_from );
  } else {
    return $content;
  }
  
  // To date
  if ( $date_to = get_field( 'event_date_to' ) ) {
    $date_to = strtotime( $date_to );
    $date_html .= ' – ' . date_i18n( $date_format, $date_to );
  }

  $date_html .= '</p>';

  return $date_html . $content;
}

/**
 * Add date of event to event category post and its excerpt
 */
add_filter( 'get_the_excerpt', 'prepend_event_date' );
add_filter( 'the_content', function ( $content ) {
  if ( is_singular() ) return prepend_event_date( $content );
  return $content;
} );

/**
 * Hide a date of publish for event category post and its excerpt
 */
add_action( 'wp_head', function () {
  $options = get_fields( 'options' );
  $event_category_id = $options[ 'event_category_id' ];
  ?>
  <style>
    .category-<?php echo $event_category_id; ?> .entry-date {
      display: none;
    }
  </style>

  <?php if ( has_category( $event_category_id ) ): ?>
  <style>
    .entry-date {
      display: none;
    }
  </style>
  <?php endif;
} );

/**
 * Sort posts in the event category by a date of event
 */
add_action( 'pre_get_posts', function( $wp_query ) {
    
  if ( ! $wp_query->is_main_query() || is_admin() ) return;
  
  $options = get_fields( 'options' );
  $event_category_id = $options[ 'event_category_id' ];
  
  if ( ! $event_category_id || ! $wp_query->is_category( $event_category_id ) ) return;

  $wp_query->set( 'meta_key', 'event_date_from' );
  $wp_query->set( 'orderby', [ 'meta_value_num' => 'DESC', 'post_date' => 'DESC' ] );

} );

/**
 * Remove the event category from recent blog posts
 */
add_action( 'pre_get_posts', function( $wp_query ) {
    
  if ( ! $wp_query->is_home() || is_admin() || $wp_query->get( 'post_type' ) !== 'post' ) return;

  $options = get_fields( 'options' );
  $event_category_id = $options[ 'event_category_id' ];

  if ( ! $event_category_id ) return;

  $tax_query = [
    'taxonomy' => 'category',
    'terms' => $event_category_id,
    'operator' => 'NOT IN',
  ];

  $wp_query->set( 'tax_query', [ $tax_query ] );
  
} );

/**
 * Add ACF theme setting page to admin
 */
if( function_exists( 'acf_add_options_page' ) ) {
	acf_add_options_page( [
		'menu_title' => __( 'Šablona', 'shp-blog' ),
		'menu_slug' => 'theme-settings',
		'capability' => 'edit_posts',
		'position' => 61,
		'icon_url' => 'dashicons-welcome-widgets-menus',
	] );
}