<?php
/**
 * The Header for RMS Premier Services Theme
 *
 * @package RMS_Premier_Services
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/svg+xml" href="<?php echo esc_url(get_template_directory_uri() . '/logo-icon.svg'); ?>" />
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

  <!-- ══════════════════ NAVBAR ══════════════════ -->
  <nav id="navbar">
    <div class="container nav-inner">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-logo" aria-label="<?php bloginfo('name'); ?> Home">
        <img src="<?php echo esc_url(get_template_directory_uri() . '/logo.svg?v=5'); ?>" alt="<?php bloginfo('name'); ?> Logo" class="nav-logo-img" />
      </a>
      <div class="nav-links">
        <a href="<?php echo is_front_page() ? '#about' : esc_url(home_url('/#about')); ?>">About</a>
        <a href="<?php echo is_front_page() ? '#services' : esc_url(home_url('/#services')); ?>">Services</a>
        <a href="<?php echo is_front_page() ? '#why' : esc_url(home_url('/#why')); ?>">Why Us</a>
        <a href="<?php echo is_front_page() ? '#market' : esc_url(home_url('/#market')); ?>">Who We Serve</a>
        <a href="<?php echo is_front_page() ? '#courses' : esc_url(home_url('/#courses')); ?>">Courses</a>
        <a href="<?php echo is_front_page() ? '#contact' : esc_url(home_url('/#contact')); ?>" class="btn btn-primary nav-cta">Get Started</a>
      </div>
      <div class="hamburger" id="hamburger" aria-label="Open menu">
        <span></span><span></span><span></span>
      </div>
    </div>
  </nav>

  <!-- Mobile Menu -->
  <div class="mobile-menu" id="mobileMenu">
    <span class="mobile-close" id="mobileClose">✕</span>
    <a href="<?php echo is_front_page() ? '#about' : esc_url(home_url('/#about')); ?>">About</a>
    <a href="<?php echo is_front_page() ? '#services' : esc_url(home_url('/#services')); ?>">Services</a>
    <a href="<?php echo is_front_page() ? '#why' : esc_url(home_url('/#why')); ?>">Why Us</a>
    <a href="<?php echo is_front_page() ? '#market' : esc_url(home_url('/#market')); ?>">Who We Serve</a>
    <a href="<?php echo is_front_page() ? '#courses' : esc_url(home_url('/#courses')); ?>">Courses</a>
    <a href="<?php echo is_front_page() ? '#contact' : esc_url(home_url('/#contact')); ?>" class="btn btn-primary">Get Started</a>
  </div>
