<?php
/**
 * Template Name: Index
 *
 * @package WordPress
 * @subpackage Fictioneer
 * @since 5.14.0
 */


// Setup
$post_id = get_the_ID();

// Header
get_header();

// Query all stories
$args = array(
  'post_type' => 'fcn_story',
  'post_status' => ['publish'],
  'posts_per_page' => -1,
  'order' => 'ASC',
  'orderby' => 'name',
  'update_post_term_cache' => false, // Improve performance
  'no_found_rows' => true // Improve performance
);

$stories = new WP_Query( $args );

// Sort stories
$sorted_stories = [];

if ( $stories->have_posts() ) {
  // Loop through posts...
  foreach ( $stories->posts as $story ) {
    $story_id = $story->ID;

    // Skip hidden
    if ( get_post_meta( $story_id, 'fictioneer_story_hidden', true ) ) {
      continue;
    }

    // Relevant data
    $title = trim( fictioneer_get_safe_title( $story_id, 'story_index' ) );
    $first_char = strtolower( mb_substr( $title, 0, 1, 'UTF-8' ) );

    // Normalize for numbers and other non-alphabetical characters
    if ( is_numeric( $first_char ) ) {
      $first_char = '#'; // Group under '#'
    }

    // Add index if necessary
    if ( ! isset( $sorted_stories[ $first_char ] ) ) {
      $sorted_stories[ $first_char ] = [];
    }

    $sorted_stories[ $first_char ][] = array(
      'id' => $story_id,
      'title' => $title,
      'link' => get_post_meta( $story_id, 'fictioneer_story_redirect_link', true ) ?: get_permalink( $story_id )
    );
  }

  // Sort by index
  ksort( $sorted_stories );
}

// Last key
end( $sorted_stories );
$last_key = key( $sorted_stories );
reset( $sorted_stories );

?>

<main id="main" class="main singular index">

  <?php do_action( 'fictioneer_main', 'singular-index' ); ?>

  <div class="main__wrapper">

    <?php do_action( 'fictioneer_main_wrapper' ); ?>

    <?php while ( have_posts() ) : the_post(); ?>

      <?php
        // Setup
        $title = fictioneer_get_safe_title( $post_id, 'singular' );
        $this_breadcrumb = [ $title, get_the_permalink() ];
      ?>

      <article id="singular-<?php echo $post_id; ?>" class="singular__article padding-left padding-right padding-top padding-bottom">

        <header class="singular__header hidden">
          <h1 class="singular__title"><?php echo $title; ?></h1>
        </header>

        <section class="singular__content content-section"><?php the_content(); ?></section>

        <section class="index-letters">
          <?php foreach ( $sorted_stories as $index => $stories ) : ?>
            <a href="<?php echo esc_attr( "#letter-{$index}" ); ?>" class="index-letters__letter"><?php echo strtoupper( $index ); ?></a>
            <?php if ( $last_key !== $index ) : ?>
              <span class="index-letters__separator">&bull;</span>
            <?php endif; ?>
          <?php endforeach; ?>
        </section>

        <?php foreach ( $sorted_stories as $index => $stories ) : ?>
          <section class="glossary">

            <h2 id="<?php echo esc_attr( "letter-{$index}" ); ?>"><?php echo strtoupper( $index ); ?></h2>

            <div class="glossary__columns">

              <?php foreach ( $stories as $story ) : ?>
                <div class="glossary__entry">
                  <div class="glossary__entry-head">
                    <a href="<?php echo $story['link']; ?>" class="glossary__entry-name _full"><?php
                      echo $story['title'];
                    ?></a>
                  </div>
                </div>
              <?php endforeach; ?>

            </div>

          </section>
        <?php endforeach; ?>

        <footer class="singular__footer"><?php do_action( 'fictioneer_singular_footer' ); ?></footer>

      </article>

    <?php endwhile; ?>

  </div>

</main>

<?php
  // Footer arguments
  $footer_args = array(
    'post_type' => 'page',
    'post_id' => $post_id,
    'breadcrumbs' => array(
      [fcntr( 'frontpage' ), get_home_url()]
    )
  );

  // Add current breadcrumb
  $footer_args['breadcrumbs'][] = $this_breadcrumb;

  // Get footer with breadcrumbs
  get_footer( null, $footer_args );
?>
