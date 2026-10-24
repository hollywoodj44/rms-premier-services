<?php
/**
 * The template for displaying all individual pages
 *
 * @package RMS_Premier_Services
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<section class="section" style="padding-top: 140px; min-height: 70vh;">
  <div class="container" style="max-width: 900px;">
    <?php while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <h1 class="section-title" style="margin-bottom: 24px; text-align: left;"><?php the_title(); ?></h1>
        <div class="entry-content" style="color: var(--off-white); line-height: 1.85; font-size: 1.05rem;">
          <?php the_content(); ?>
        </div>
      </article>
    <?php endwhile; ?>
  </div>
</section>

<?php
get_footer();
