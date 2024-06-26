<?php
/**
 * Partial: Latest Recommendations Compact
 *
 * Renders the (buffered) HTML for the [fictioneer_latest_recommendations type="compact"] shortcode.
 *
 * @package WordPress
 * @subpackage Fictioneer
 * @since 4.0.0
 *
 * @internal $args['type']              Type argument passed from shortcode ('compact').
 * @internal $args['count']             Number of posts provided by the shortcode.
 * @internal $args['author']            Author provided by the shortcode.
 * @internal $args['order']             Order of posts. Default 'DESC'.
 * @internal $args['orderby']           Sorting of posts. Default 'date'.
 * @internal $args['post_ids']          Array of post IDs. Default empty.
 * @internal $args['author_ids']        Array of author IDs. Default empty.
 * @internal $args['excluded_authors']  Array of author IDs to exclude. Default empty.
 * @internal $args['excluded_cats']     Array of category IDs to exclude. Default empty.
 * @internal $args['excluded_tags']     Array of tag IDs to exclude. Default empty.
 * @internal $args['ignore_protected']  Whether to ignore protected posts. Default false.
 * @internal $args['taxonomies']        Array of taxonomy arrays. Default empty.
 * @internal $args['relation']          Relationship between taxonomies.
 * @internal $args['vertical']          Whether to show the vertical variant.
 * @internal $args['seamless']          Whether to render the image seamless. Default false (Customizer).
 * @internal $args['aspect_ratio']      Aspect ratio for the image. Only with vertical.
 * @internal $args['lightbox']          Whether the image is opened in the lightbox. Default true.
 * @internal $args['thumbnail']         Whether the image is rendered. Default true (Customizer).
 * @internal $args['classes']           String of additional CSS classes. Default empty.
 */


// No direct access!
defined( 'ABSPATH' ) OR exit;

// Setup
$show_taxonomies = ! get_option( 'fictioneer_hide_taxonomies_on_recommendation_cards' );

// Prepare query
$query_args = array (
  'fictioneer_query_name' => 'latest_recommendations_compact',
  'post_type' => 'fcn_recommendation',
  'post_status' => 'publish',
  'post__in' => $args['post_ids'], // May be empty!
  'order' => $args['order'],
  'orderby' => $args['orderby'],
  'posts_per_page' => $args['count'],
  'no_found_rows' => true
);

// Author?
if ( ! empty( $args['author'] ) ) {
  $query_args['author_name'] = $args['author'];
}

// Author IDs?
if ( ! empty( $args['author_ids'] ) ) {
  $query_args['author__in'] = $args['author_ids'];
}

// Taxonomies?
if ( ! empty( $args['taxonomies'] ) ) {
  $query_args['tax_query'] = fictioneer_get_shortcode_tax_query( $args );
}

// Excluded tags?
if ( ! empty( $args['excluded_tags'] ) ) {
  $query_args['tag__not_in'] = $args['excluded_tags'];
}

// Excluded categories?
if ( ! empty( $args['excluded_cats'] ) ) {
  $query_args['category__not_in'] = $args['excluded_cats'];
}

// Excluded authors?
if ( ! empty( $args['excluded_authors'] ) ) {
  $query_args['author__not_in'] = $args['excluded_authors'];
}

// Ignore protected?
if ( $args['ignore_protected'] ) {
  add_filter( 'posts_where', 'fictioneer_exclude_protected_posts' );
}

// Apply filters
$query_args = apply_filters( 'fictioneer_filter_shortcode_latest_recommendations_query_args', $query_args, $args );

// Query chapters
$entries = fictioneer_shortcode_query( $query_args );

// Remove temporary filters
remove_filter( 'posts_where', 'fictioneer_exclude_protected_posts' );

?>

<section class="small-card-block latest-recommendations _compact <?php echo $args['classes']; ?>">
  <?php if ( $entries->have_posts() ) : ?>

    <ul class="grid-columns _collapse-on-mobile">
      <?php while ( $entries->have_posts() ) : $entries->the_post(); ?>

        <?php
          // Setup
          $post_id = $post->ID;
          $title = fictioneer_get_safe_title( $post_id, 'shortcode-latest-recommendations-compact' );
          $one_sentence = get_post_meta( $post_id, 'fictioneer_recommendation_one_sentence', true );
          $fandoms = get_the_terms( $post, 'fcn_fandom' );
          $characters = get_the_terms( $post, 'fcn_character' );
          $genres = get_the_terms( $post, 'fcn_genre' );
          $tags = get_option( 'fictioneer_show_tags_on_recommendation_cards' ) ? get_the_tags( $post ) : false;
          $grid_or_vertical = $args['vertical'] ? '_vertical' : '_grid';
          $card_classes = [];

          // Extra classes
          if ( $show_taxonomies ) {
            $card_classes[] = '_info';
          }

          if ( get_theme_mod( 'card_style', 'default' ) !== 'default' ) {
            $card_classes[] = '_' . get_theme_mod( 'card_style' );
          }

          if ( $args['vertical'] ) {
            $card_classes[] = '_vertical';
          }

          if ( $args['seamless'] ) {
            $card_classes[] = '_seamless';
          }

          // Truncate factor
          $truncate_factor = $args['vertical'] ? '_4-4' : '_cq-3-4';

          // Card attributes
          $attributes = [];

          if ( $args['aspect_ratio'] ) {
            $attributes['style'] = '--card-image-aspect-ratio: ' . $args['aspect_ratio'];
          }

          $attributes = apply_filters( 'fictioneer_filter_card_attributes', $attributes, $post, 'shortcode-latest-recommendations-compact' );

          $card_attributes = '';

          foreach ( $attributes as $key => $value ) {
            $card_attributes .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
          }
        ?>

        <li class="post-<?php echo $post_id; ?> card watch-last-clicked _small _recommendation _compact _no-footer <?php echo implode( ' ', $card_classes ); ?>" <?php echo $card_attributes; ?>>
          <div class="card__body polygon">

            <?php if ( $show_taxonomies ) : ?>
              <button class="card__info-toggle toggle-last-clicked" aria-label="<?php esc_attr_e( 'Open info box', 'fictioneer' ); ?>"><i class="fa-solid fa-chevron-down"></i></button>
            <?php endif; ?>

            <div class="card__main <?php echo $grid_or_vertical; ?> _small">

              <?php
                do_action( 'fictioneer_shortcode_latest_recommendations_card_body', $post, $args );

                if ( $args['thumbnail'] ) {
                  fictioneer_output_small_card_thumbnail(
                    array(
                      'post_id' => $post_id,
                      'title' => $title,
                      'classes' => 'card__image cell-img',
                      'permalink' => get_permalink(),
                      'lightbox' => $args['lightbox'],
                      'vertical' => $args['vertical'],
                      'seamless' => $args['seamless'],
                      'aspect_ratio' => $args['aspect_ratio']
                    )
                  );
                }
              ?>

              <h3 class="card__title _small cell-title"><a href="<?php the_permalink(); ?>" class="truncate _1-1"><?php echo $title; ?></a></h3>

              <div class="card__content _small cell-desc">
                <div class="truncate <?php echo $truncate_factor; ?>">
                  <span class="card__by-author"><?php
                    printf(
                      _x( 'by %s —', 'Small card: by {Author} —.', 'fictioneer' ),
                      '<span class="author">' . get_post_meta( $post_id, 'fictioneer_recommendation_author', true ) . '</span>'
                    );
                  ?></span>
                  <span><?php
                    if ( ! empty( $one_sentence ) ) {
                      echo wp_strip_all_tags( $one_sentence, true );
                    } else {
                      echo fictioneer_first_paragraph_as_excerpt( get_the_content() );
                    }
                  ?></span>
                </div>
              </div>

            </div>

            <?php if ( $show_taxonomies ) : ?>
              <div class="card__overlay-infobox escape-last-click">
                <div class="card__tag-list _small">
                  <?php
                    if ( $fandoms || $characters || $genres || $tags ) {
                      $output = [];

                      if ( $fandoms ) {
                        foreach ( $fandoms as $fandom ) {
                          $output[] = '<a href="' . get_tag_link( $fandom ) . '" class="tag-pill _inline _fandom">' . $fandom->name . '</a>';
                        }
                      }

                      if ( $genres ) {
                        foreach ( $genres as $genre ) {
                          $output[] = '<a href="' . get_tag_link( $genre ) . '" class="tag-pill _inline _genre">' . $genre->name . '</a>';
                        }
                      }

                      if ( $tags ) {
                        foreach ( $tags as $tag ) {
                          $output[] = '<a href="' . get_tag_link( $tag ) . '" class="tag-pill _inline">' . $tag->name . '</a>';
                        }
                      }

                      if ( $characters ) {
                        foreach ( $characters as $character ) {
                          $output[] = '<a href="' . get_tag_link( $character ) . '" class="tag-pill _inline _character">' . $character->name . '</a>';
                        }
                      }

                      // Implode with three-per-em spaces around a bullet
                        echo implode( '&#8196;&bull;&#8196;', $output );
                    } else {
                      ?><span class="card__no-taxonomies"><?php _e( 'No taxonomies specified yet.', 'fictioneer' ); ?></span><?php
                    }
                  ?>
                </div>
              </div>
            <?php endif; ?>

          </div>
        </li>

      <?php endwhile; ?>
    </ul>

  <?php else : ?>

    <div class="no-results"><?php _e( 'Nothing to show.', 'fictioneer' ); ?></div>

  <?php endif; wp_reset_postdata(); ?>
</section>
