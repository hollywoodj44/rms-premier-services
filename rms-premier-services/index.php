<?php
/**
 * The Main Template File (WordPress fallback)
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
  <div class="container">
    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="margin-bottom: 48px;">
          <h1 class="section-title" style="margin-bottom: 16px;"><?php the_title(); ?></h1>
          <div class="entry-content" style="color: var(--off-white); line-height: 1.8; font-size: 1.05rem;">
            <?php the_content(); ?>
          </div>
        </article>
      <?php endwhile; ?>
    <?php else : ?>
      <div style="text-align:center; padding: 60px 0;">
        <h1 class="section-title">Welcome to <span>RMS Premier Services</span></h1>
        <p style="color:var(--gray-light); margin: 20px auto 32px; max-width: 600px;">
          Healthcare &amp; IT Project Management Consulting.
        </p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">Return Home ↗</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php
get_footer();
