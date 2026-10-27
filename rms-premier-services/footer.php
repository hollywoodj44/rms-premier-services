<?php
/**
 * The Footer for RMS Premier Services Theme
 *
 * @package RMS_Premier_Services
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$contact_email = rms_get_contact_email();
?>
  <!-- ══════════════════ FOOTER ══════════════════ -->
  <footer>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo-link" aria-label="<?php bloginfo('name'); ?> Home">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/logo.svg?v=6'); ?>" alt="<?php bloginfo('name'); ?> Logo" class="footer-logo-img" />
          </a>
          <p>A modern, flexible, and cost-effective Business, Healthcare &amp; IT consulting firm—delivering compliant, scalable projects without the overhead.</p>
          <p style="margin-top: 14px;">
            <a href="mailto:<?php echo esc_attr($contact_email); ?>" style="color: var(--indigo); font-weight: 500; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
              ✉ <?php echo esc_html($contact_email); ?>
            </a>
          </p>
        </div>
        <div class="footer-col">
          <h4>Services</h4>
          <ul>
            <li><a href="<?php echo is_front_page() ? '#services' : esc_url(home_url('/#services')); ?>">Project Management</a></li>
            <li><a href="<?php echo is_front_page() ? '#services' : esc_url(home_url('/#services')); ?>">Healthcare IT / EHR</a></li>
            <li><a href="<?php echo is_front_page() ? '#services' : esc_url(home_url('/#services')); ?>">Technical Writing &amp; SOPs</a></li>
            <li><a href="<?php echo is_front_page() ? '#services' : esc_url(home_url('/#services')); ?>">Workflow Optimization</a></li>
            <li><a href="<?php echo is_front_page() ? '#services' : esc_url(home_url('/#services')); ?>">RFP &amp; Proposal Writing</a></li>
            <li><a href="<?php echo is_front_page() ? '#services' : esc_url(home_url('/#services')); ?>">Database &amp; Reporting</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Company</h4>
          <ul>
            <li><a href="<?php echo is_front_page() ? '#about' : esc_url(home_url('/#about')); ?>">About Us</a></li>
            <li><a href="<?php echo is_front_page() ? '#why' : esc_url(home_url('/#why')); ?>">Why RMS Premier</a></li>
            <li><a href="<?php echo is_front_page() ? '#market' : esc_url(home_url('/#market')); ?>">Who We Serve</a></li>
            <li><a href="<?php echo is_front_page() ? '#courses' : esc_url(home_url('/#courses')); ?>">Courses &amp; Coaching</a></li>
            <li><a href="<?php echo is_front_page() ? '#contact' : esc_url(home_url('/#contact')); ?>">Contact Us</a></li>
            <li><a href="mailto:<?php echo esc_attr($contact_email); ?>">Email Us Directly</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> <span class="teal">RMS Premier Services</span>. All rights reserved.</p>
        <p>HIPAA-Aware · PMP-Certified · Nationwide Remote Services</p>
      </div>
    </div>
  </footer>

  <?php wp_footer(); ?>
</body>
</html>
