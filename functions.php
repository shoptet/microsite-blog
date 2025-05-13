<?php

// Prevent Yoast SEO from removing the comment reply feature
add_filter( 'wpseo_remove_reply_to_com', '__return_false' );

function prepend_event_date ( $content ) {
  $options = get_fields( 'options' );

  if ( !isset($options[ 'event_category_id' ]) ) {
    return $content;
  }

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
 * Add query arguments to post count api
 */
add_filter( 'shoptet_post_count_query_args', function($query_args) {
  return [
    'blogArticlesCount' => [
      'post_type' => 'post',
      'post_status' => 'publish',
    ],
  ];
} );

/**
 * Add date of event to event category post and its excerpt
 */
add_filter( 'get_the_excerpt', 'prepend_event_date' );
add_filter( 'the_content', function ( $content ) {
  if ( is_singular() ) return prepend_event_date( $content );
  return $content;
} );

/**
 * Add post modified date next to publish date
 */
add_filter( 'entry_date', function ( $date, $post ) {
  $show_modified_date = get_field( 'show_modified_date', $post );
  if ($show_modified_date) {
    $modified_date = get_the_modified_date( 'd. m. Y', $post );
    $date .= ' <em>('. sprintf( __( 'Aktualizováno %s', 'shp-blog' ), $modified_date ) . ')</em>';
  }
  return $date;
}, 10, 2 );

/**
 * Hide a date of publish for event category post and its excerpt
 */
add_action( 'wp_head', function () {
  $options = get_fields( 'options' );

  if ( !isset($options[ 'event_category_id' ]) ) {
    return;
  }

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

  if ( !isset($options[ 'event_category_id' ]) ) {
    return;
  }

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

  if ( !isset($options[ 'event_category_id' ]) ) {
    return;
  }

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

add_filter( 'the_content', function( $content ) {
  if (get_field('show_author')) {
    ob_start();
    get_template_part('src/template-parts/post/content', 'author');
    $content .= ob_get_contents();
    ob_end_clean();
  }
  return $content;
} );

add_filter( 'shp_dl_page', function( $page ) {
  $page['category'] = get_the_first_category();
  $page['subCategory'] = get_the_first_subcategory();
  $page['type'] = get_datalayer_type();
  if (is_category()) {
    $page['title'] = single_cat_title('', false);
  } elseif (is_tag()) {
    $page['title'] = single_tag_title('', false);
  } elseif (is_author()) {
    $page['title'] = get_the_author();
  } elseif ( is_search() ) {
    $page['title'] = __( 'Search results for', 'shoptet' ) . ' ' . get_search_query();
  }
  return $page;
} );

function get_the_first_category($subcategories_only = false) {
  $category = 'not_available_DL';
  if (is_category() && $term = get_queried_object()) {
    if ($term->parent > 0) {
      $category = $subcategories_only ? $term->name : get_cat_name($term->parent);
    } else if ($term->parent == 0 && !$subcategories_only) {
      $category = $term->name;
    }
  } else if (is_single() && $categories = get_the_category()) {
    $categories = array_values(array_filter($categories, function ($c) use ($subcategories_only) {
      return $subcategories_only ? $c->parent > 0 : $c->parent == 0;
    }));
    if ($categories) {
      $category = $categories[0]->name;
    }
  }
  return $category;
}

function get_the_first_subcategory() {
  return get_the_first_category(true);
}

function get_datalayer_type() {
  $type = 'other';
  if (is_front_page()) {
    $type = 'home';
  } else if (is_category()) {
    $type = 'category';
  } else if (is_single()) {
    $type = 'article';
  }
  return $type;
}