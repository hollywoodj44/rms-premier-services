<?php
/**
 * Consultation Inquiry & Email Delivery Handler
 *
 * Handles:
 * 1. Registering 'rms_consultation' Custom Post Type for lead retention
 * 2. AJAX form submission with security nonces & spam protection
 * 3. HTML email dispatch to business email (sthomas@rmspremierservices.com)
 * 4. Client auto-responder confirmation email
 * 5. Admin consultation inbox & settings page with instant Test Email tool
 *
 * @package RMS_Premier_Services
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * ─────────────────────────────────────────────────────────────
 * 1. REGISTER CONSULTATION POST TYPE & ADMIN INBOX
 * ─────────────────────────────────────────────────────────────
 */
function rms_register_consultation_cpt() {
    $labels = array(
        'name'               => __('Consultations', 'rms-premier-services'),
        'singular_name'      => __('Consultation Inquiry', 'rms-premier-services'),
        'menu_name'          => __('Consultations', 'rms-premier-services'),
        'all_items'          => __('All Inquiries', 'rms-premier-services'),
        'view_item'          => __('View Inquiry', 'rms-premier-services'),
        'search_items'       => __('Search Inquiries', 'rms-premier-services'),
        'not_found'          => __('No inquiries found', 'rms-premier-services'),
        'not_found_in_trash' => __('No inquiries found in Trash', 'rms-premier-services'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-businesswoman',
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'supports'           => array('title', 'editor'),
        'has_archive'        => false,
        'rewrite'            => false,
    );

    register_post_type('rms_consultation', $args);
}
add_action('init', 'rms_register_consultation_cpt');

/**
 * Add custom columns to Consultations admin list
 */
function rms_consultation_columns($columns) {
    $new_cols = array();
    $new_cols['cb']         = $columns['cb'];
    $new_cols['title']      = __('Client Name', 'rms-premier-services');
    $new_cols['rms_email']   = __('Email Address', 'rms-premier-services');
    $new_cols['rms_org']     = __('Organization', 'rms-premier-services');
    $new_cols['rms_service'] = __('Service of Interest', 'rms-premier-services');
    $new_cols['rms_status']  = __('Status', 'rms-premier-services');
    $new_cols['date']       = __('Date Received', 'rms-premier-services');
    return $new_cols;
}
add_filter('manage_rms_consultation_posts_columns', 'rms_consultation_columns');

/**
 * Populate custom column content
 */
function rms_consultation_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'rms_email':
            $email = get_post_meta($post_id, '_rms_email', true);
            if ($email) {
                printf('<a href="mailto:%1$s" style="font-weight:600; color:#4f46e5;">%1$s</a>', esc_attr($email));
            } else {
                echo '—';
            }
            break;

        case 'rms_org':
            $org = get_post_meta($post_id, '_rms_org_type_label', true);
            echo esc_html($org ?: '—');
            break;

        case 'rms_service':
            $service = get_post_meta($post_id, '_rms_service_label', true);
            echo esc_html($service ?: '—');
            break;

        case 'rms_status':
            $status = get_post_meta($post_id, '_rms_status', true) ?: 'new';
            $badge_style = 'padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block;';
            if ($status === 'new') {
                echo '<span style="' . $badge_style . ' background:#dcfce7; color:#15803d;">● New Lead</span>';
            } elseif ($status === 'contacted') {
                echo '<span style="' . $badge_style . ' background:#e0e7ff; color:#4338ca;">Contacted</span>';
            } elseif ($status === 'in_progress') {
                echo '<span style="' . $badge_style . ' background:#fef3c7; color:#b45309;">In Progress</span>';
            } else {
                echo '<span style="' . $badge_style . ' background:#f3f4f6; color:#4b5563;">Closed</span>';
            }
            break;
    }
}
add_action('manage_rms_consultation_posts_custom_column', 'rms_consultation_custom_column_content', 10, 2);

/**
 * Make custom columns sortable
 */
function rms_consultation_sortable_columns($columns) {
    $columns['rms_email']   = 'rms_email';
    $columns['rms_status']  = 'rms_status';
    return $columns;
}
add_filter('manage_edit-rms_consultation_sortable_columns', 'rms_consultation_sortable_columns');

/**
 * Add inquiry detail meta box
 */
function rms_add_consultation_meta_boxes() {
    add_meta_box(
        'rms_consultation_details',
        __('Consultation Request Information', 'rms-premier-services'),
        'rms_render_consultation_meta_box',
        'rms_consultation',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'rms_add_consultation_meta_boxes');

function rms_render_consultation_meta_box($post) {
    wp_nonce_field('rms_save_consultation_meta', 'rms_meta_box_nonce');

    $first_name = get_post_meta($post->ID, '_rms_first_name', true);
    $last_name  = get_post_meta($post->ID, '_rms_last_name', true);
    $email      = get_post_meta($post->ID, '_rms_email', true);
    $org_label  = get_post_meta($post->ID, '_rms_org_type_label', true);
    $srv_label  = get_post_meta($post->ID, '_rms_service_label', true);
    $status     = get_post_meta($post->ID, '_rms_status', true) ?: 'new';
    $ip         = get_post_meta($post->ID, '_rms_ip', true);
    $notes      = get_post_meta($post->ID, '_rms_admin_notes', true);

    $reply_subject = rawurlencode('Re: RMS Premier Services Consultation — ' . ($srv_label ?: 'Project Inquiry'));
    $reply_body    = rawurlencode("Hi {$first_name},\n\nThank you for reaching out to RMS Premier Services. I reviewed your consultation request regarding {$srv_label}...\n\nBest regards,\nSheryl Thomas, PMP\nRMS Premier Services\nsthomas@rmspremierservices.com");
    $mailto_link   = "mailto:{$email}?subject={$reply_subject}&body={$reply_body}";
    ?>
    <div style="padding: 15px 0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb;">
            <div>
                <h3 style="margin: 0 0 5px 0; font-size: 1.3em;"><?php echo esc_html($first_name . ' ' . $last_name); ?></h3>
                <a href="<?php echo esc_url($mailto_link); ?>" style="font-size: 15px; color: #4f46e5; font-weight:600; text-decoration: none;">✉ <?php echo esc_html($email); ?></a>
            </div>
            <div>
                <a href="<?php echo esc_url($mailto_link); ?>" class="button button-primary" style="background:#4f46e5; border-color:#4338ca; height:34px; line-height:32px; padding:0 16px;">
                    Reply to Client ↗
                </a>
            </div>
        </div>

        <table class="form-table" style="margin-top:0;">
            <tr>
                <th style="width: 180px;"><strong><?php _e('Organization Type:', 'rms-premier-services'); ?></strong></th>
                <td><?php echo esc_html($org_label ?: 'Not specified'); ?></td>
            </tr>
            <tr>
                <th><strong><?php _e('Service Requested:', 'rms-premier-services'); ?></strong></th>
                <td><span style="font-weight: 600; color: #1e293b;"><?php echo esc_html($srv_label ?: 'General Consultation'); ?></span></td>
            </tr>
            <tr>
                <th><strong><?php _e('Submission Date:', 'rms-premier-services'); ?></strong></th>
                <td><?php echo get_the_date('F j, Y, g:i a', $post); ?></td>
            </tr>
            <tr>
                <th><strong><?php _e('Lead Status:', 'rms-premier-services'); ?></strong></th>
                <td>
                    <select name="rms_status" style="min-width: 180px;">
                        <option value="new" <?php selected($status, 'new'); ?>>● New Lead</option>
                        <option value="contacted" <?php selected($status, 'contacted'); ?>>Contacted</option>
                        <option value="in_progress" <?php selected($status, 'in_progress'); ?>>In Progress</option>
                        <option value="closed" <?php selected($status, 'closed'); ?>>Closed / Archived</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><strong><?php _e('Internal Notes:', 'rms-premier-services'); ?></strong></th>
                <td>
                    <textarea name="rms_admin_notes" rows="4" style="width: 100%; max-width: 600px; padding:8px;" placeholder="Add private notes, follow-up dates, or client phone call logs..."><?php echo esc_textarea($notes); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><span style="color:#9ca3af; font-size:12px;"><?php _e('Sender IP:', 'rms-premier-services'); ?></span></th>
                <td><span style="color:#9ca3af; font-size:12px;"><?php echo esc_html($ip ?: 'N/A'); ?></span></td>
            </tr>
        </table>
    </div>
    <?php
}

/**
 * Save consultation meta box fields
 */
function rms_save_consultation_meta($post_id) {
    if (!isset($_POST['rms_meta_box_nonce']) || !wp_verify_nonce($_POST['rms_meta_box_nonce'], 'rms_save_consultation_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['rms_status'])) {
        update_post_meta($post_id, '_rms_status', sanitize_text_field($_POST['rms_status']));
    }
    if (isset($_POST['rms_admin_notes'])) {
        update_post_meta($post_id, '_rms_admin_notes', sanitize_textarea_field($_POST['rms_admin_notes']));
    }
}
add_action('save_post_rms_consultation', 'rms_save_consultation_meta');


/**
 * ─────────────────────────────────────────────────────────────
 * 2. ADMIN SETTINGS PAGE & TEST EMAIL
 * ─────────────────────────────────────────────────────────────
 */
function rms_register_settings_menu() {
    add_submenu_page(
        'edit.php?post_type=rms_consultation',
        __('Consultation Settings', 'rms-premier-services'),
        __('Settings', 'rms-premier-services'),
        'manage_options',
        'rms-consultation-settings',
        'rms_render_settings_page'
    );
}
add_action('admin_menu', 'rms_register_settings_menu');

function rms_register_settings() {
    register_setting('rms_settings_group', 'rms_consultation_email', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_email',
        'default'           => 'sthomas@rmspremierservices.com',
    ));
    register_setting('rms_settings_group', 'rms_consultation_cc', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ));
    register_setting('rms_settings_group', 'rms_auto_reply_enabled', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '1',
    ));
}
add_action('admin_init', 'rms_register_settings');

function rms_render_settings_page() {
    // Handle Send Test Email action
    $test_status_msg = '';
    if (isset($_POST['rms_send_test_email']) && check_admin_referer('rms_test_email_action')) {
        $to = rms_get_contact_email();
        $subject = 'Test Consultation Email — RMS Premier Services';
        $body = '<html><body style="font-family:Arial,sans-serif; background:#f4f6fb; padding:20px;">
            <div style="max-width:550px; margin:0 auto; background:#ffffff; border-radius:10px; padding:30px; border:1px solid #e2e8f0;">
                <h2 style="color:#4f46e5; margin-top:0;">✓ Email Delivery System Active</h2>
                <p>Hello Sheryl / RMS Premier Services Team,</p>
                <p>This is a verification test from your WordPress website. Your consultation email pipeline is working and ready to receive client requests!</p>
                <div style="background:#f8fafc; border-left:4px solid #4f46e5; padding:12px 16px; margin:20px 0;">
                    <strong>Destination:</strong> ' . esc_html($to) . '<br>
                    <strong>Timestamp:</strong> ' . current_time('mysql') . '
                </div>
                <p style="color:#64748b; font-size:13px;">RMS Premier Services · Healthcare &amp; IT Project Management</p>
            </div>
        </body></html>';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $sent = wp_mail($to, $subject, $body, $headers);
        if ($sent) {
            $test_status_msg = '<div class="notice notice-success is-dismissible"><p><strong>✓ Test email dispatched successfully to ' . esc_html($to) . '!</strong> Check your inbox (and spam folder) in the next 1-2 minutes.</p></div>';
        } else {
            $test_status_msg = '<div class="notice notice-error is-dismissible"><p><strong>⚠ wp_mail() returned false.</strong> Your server might need an SMTP plugin. See deliverability guide below.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1>RMS Consultation &amp; Email Settings</h1>
        <?php echo $test_status_msg; ?>

        <div style="display:flex; gap:30px; flex-wrap:wrap; margin-top:20px;">
            <div style="flex:1; min-width:320px; max-width:650px;">
                <div style="background:#fff; border:1px solid #ccd0d4; padding:24px; border-radius:8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('rms_settings_group');
                        do_settings_sections('rms_settings_group');
                        ?>
                        <h2 style="margin-top:0; font-size:1.2em; border-bottom:1px solid #eee; padding-bottom:10px;">Email Destinations</h2>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="rms_consultation_email">Business Email</label></th>
                                <td>
                                    <input type="email" id="rms_consultation_email" name="rms_consultation_email" value="<?php echo esc_attr(get_option('rms_consultation_email', 'sthomas@rmspremierservices.com')); ?>" class="regular-text" required />
                                    <p class="description">All client consultation requests will be immediately delivered to this address.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="rms_consultation_cc">CC / Additional Emails</label></th>
                                <td>
                                    <input type="text" id="rms_consultation_cc" name="rms_consultation_cc" value="<?php echo esc_attr(get_option('rms_consultation_cc', '')); ?>" class="regular-text" placeholder="assistant@company.com" />
                                    <p class="description">Optional: Comma-separated list of additional team emails to copy on leads.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Client Auto-Reply</th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="rms_auto_reply_enabled" value="1" <?php checked(get_option('rms_auto_reply_enabled', '1'), '1'); ?> />
                                        Send automatic confirmation receipt to prospective client
                                    </label>
                                    <p class="description">Confirms receipt of their project inquiry and informs them you will be in touch within 24 business hours.</p>
                                </td>
                            </tr>
                        </table>

                        <?php submit_button(__('Save Settings', 'rms-premier-services')); ?>
                    </form>
                </div>

                <!-- Test Email Card -->
                <div style="background:#fff; border:1px solid #ccd0d4; padding:24px; border-radius:8px; margin-top:24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h2 style="margin-top:0; font-size:1.2em; border-bottom:1px solid #eee; padding-bottom:10px;">Test Email System</h2>
                    <p>Click below to verify that WordPress can successfully dispatch emails to <strong><?php echo esc_html(rms_get_contact_email()); ?></strong>.</p>
                    <form method="post" action="">
                        <?php wp_nonce_field('rms_test_email_action'); ?>
                        <input type="submit" name="rms_send_test_email" class="button button-secondary" value="Send Test Email Now" />
                    </form>
                </div>
            </div>

            <!-- Deliverability & SMTP Help Box -->
            <div style="flex:1; min-width:300px; max-width:440px;">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-left:4px solid #4f46e5; padding:22px; border-radius:8px;">
                    <h3 style="margin-top:0; color:#1e293b;">💡 100% Email Deliverability Guide</h3>
                    <p style="font-size:13.5px; line-height:1.6; color:#475569;">
                        By default, WordPress uses PHP's built-in mail. Some web hosts (GoDaddy, Bluehost, Hostinger, Siteground) or mail providers (Google/Microsoft) can flag standard PHP mail as spam or delay delivery.
                    </p>
                    <p style="font-size:13.5px; line-height:1.6; color:#475569;">
                        <strong>Recommended 1-Minute Setup:</strong>
                    </p>
                    <ol style="font-size:13px; line-height:1.6; color:#475569; padding-left:18px;">
                        <li>Install the free <strong>WP Mail SMTP</strong> plugin (by WPForms) from <em>Plugins → Add New</em>.</li>
                        <li>Connect your business email provider (Google Workspace, Microsoft 365, SendLayer, or your hosting SMTP).</li>
                        <li>This ensures <strong>100% of client inquiries land directly in your Primary Inbox</strong> without ever going to spam!</li>
                    </ol>
                    <p style="font-size:12.5px; color:#64748b; margin-bottom:0;">
                        🔒 <em>Lead Backup: Even if your email server has a hiccup, every single lead is permanently stored under <strong>Consultations → All Inquiries</strong> in this WordPress dashboard.</em>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php
}


/**
 * ─────────────────────────────────────────────────────────────
 * 3. AJAX CONSULTATION SUBMISSION HANDLER
 * ─────────────────────────────────────────────────────────────
 */
function rms_handle_consultation_submission() {
    // 1. Verify Nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rms_consultation_nonce')) {
        wp_send_json_error(array(
            'message' => __('Security verification failed. Please refresh the page and try again.', 'rms-premier-services')
        ));
    }

    // 2. Honeypot Bot Check
    if (!empty($_POST['rms_hp'])) {
        // Silently succeed to trick spam bot
        wp_send_json_success(array(
            'message' => __('✓ Message sent! We\'ll be in touch within 24 business hours.', 'rms-premier-services')
        ));
    }

    // 3. Sanitize inputs
    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name  = sanitize_text_field($_POST['last_name'] ?? '');
    $email      = sanitize_email($_POST['email'] ?? '');
    $org_type   = sanitize_text_field($_POST['org_type'] ?? '');
    $service    = sanitize_text_field($_POST['service'] ?? '');
    $message    = sanitize_textarea_field($_POST['message'] ?? '');

    // Map organization slugs to readable labels
    $org_labels = array(
        'small-business'      => 'Small Business',
        'private-practice'   => 'Private Practice',
        'healthcare-org'     => 'Healthcare Organization',
        'telehealth-startup' => 'Telehealth Startup',
        'tech-company'       => 'Technology Company',
        'other'              => 'Other',
    );
    $org_label = $org_labels[$org_type] ?? ($org_type ? ucfirst(str_replace('-', ' ', $org_type)) : 'Not Specified');

    // Map service slugs to readable labels
    $service_labels = array(
        'pm-consulting'  => 'Project Management Consulting',
        'healthcare-it'  => 'Healthcare IT / EHR Implementation',
        'sop-writing'    => 'Technical Writing & SOPs',
        'workflow'       => 'Workflow Optimization',
        'rfp'            => 'RFP & Proposal Writing',
        'database'       => 'Database & Reporting',
        'courses'        => 'Online Courses & Coaching',
    );
    $service_label = $service_labels[$service] ?? ($service ? ucfirst(str_replace('-', ' ', $service)) : 'General Consultation');

    // 4. Validate
    if (empty($first_name) || empty($last_name) || empty($email) || !is_email($email) || empty($message)) {
        wp_send_json_error(array(
            'message' => __('Please complete all required fields with a valid email address.', 'rms-premier-services')
        ));
    }

    $full_name = trim($first_name . ' ' . $last_name);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

    // 5. Store lead permanently in WordPress Database
    $post_id = wp_insert_post(array(
        'post_type'    => 'rms_consultation',
        'post_title'   => sprintf('%s — %s', $full_name, $service_label),
        'post_content' => $message,
        'post_status'  => 'publish',
    ));

    if ($post_id && !is_wp_error($post_id)) {
        update_post_meta($post_id, '_rms_first_name', $first_name);
        update_post_meta($post_id, '_rms_last_name', $last_name);
        update_post_meta($post_id, '_rms_email', $email);
        update_post_meta($post_id, '_rms_org_type', $org_type);
        update_post_meta($post_id, '_rms_org_type_label', $org_label);
        update_post_meta($post_id, '_rms_service', $service);
        update_post_meta($post_id, '_rms_service_label', $service_label);
        update_post_meta($post_id, '_rms_status', 'new');
        update_post_meta($post_id, '_rms_ip', $ip_address);
    }

    // 6. Build High-Priority HTML Notification Email to Business
    $business_email = rms_get_contact_email();
    $admin_url_lead = $post_id ? admin_url('post.php?post=' . $post_id . '&action=edit') : admin_url('edit.php?post_type=rms_consultation');

    $subject = sprintf('[Consultation Request] %s — %s', $full_name, $service_label);

    $reply_subject = rawurlencode('Re: RMS Premier Services Consultation — ' . $service_label);
    $mailto_reply  = "mailto:{$email}?subject={$reply_subject}";

    $email_html = '
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>' . esc_html($subject) . '</title>
    </head>
    <body style="margin:0; padding:20px; background-color:#09090f; font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif; color:#e2e4f0;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px; background-color:#141425; border-radius:12px; border:1px solid rgba(99,102,241,0.25); overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.5);">
        
        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); padding:28px 32px; border-bottom:1px solid rgba(99,102,241,0.3);">
            <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.12em; color:#a5b4fc; font-weight:700; margin-bottom:6px;">
              RMS Premier Services · Client Consultation
            </div>
            <h1 style="margin:0; font-size:22px; color:#ffffff; font-weight:700;">
              New Consultation Request
            </h1>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:32px;">
            <p style="margin-top:0; font-size:15px; color:#cbd5e1; line-height:1.5;">
              You have received a new consultation inquiry from the <strong>RMS Premier Services</strong> website.
            </p>

            <!-- Lead Details Table -->
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#0f0f1a; border-radius:8px; border:1px solid rgba(255,255,255,0.08); margin:20px 0;">
              <tr>
                <td style="padding:12px 18px; border-bottom:1px solid rgba(255,255,255,0.06); width:35%; color:#94a3b8; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Client Name</td>
                <td style="padding:12px 18px; border-bottom:1px solid rgba(255,255,255,0.06); color:#ffffff; font-size:15px; font-weight:600;">' . esc_html($full_name) . '</td>
              </tr>
              <tr>
                <td style="padding:12px 18px; border-bottom:1px solid rgba(255,255,255,0.06); color:#94a3b8; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Email Address</td>
                <td style="padding:12px 18px; border-bottom:1px solid rgba(255,255,255,0.06); color:#818cf8; font-size:15px; font-weight:600;">
                  <a href="' . esc_url($mailto_reply) . '" style="color:#818cf8; text-decoration:none;">' . esc_html($email) . ' ↗</a>
                </td>
              </tr>
              <tr>
                <td style="padding:12px 18px; border-bottom:1px solid rgba(255,255,255,0.06); color:#94a3b8; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Organization</td>
                <td style="padding:12px 18px; border-bottom:1px solid rgba(255,255,255,0.06); color:#ffffff; font-size:14px;">' . esc_html($org_label) . '</td>
              </tr>
              <tr>
                <td style="padding:12px 18px; color:#94a3b8; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Service Needed</td>
                <td style="padding:12px 18px; color:#38bdf8; font-size:14px; font-weight:600;">' . esc_html($service_label) . '</td>
              </tr>
            </table>

            <!-- Message Box -->
            <div style="margin:24px 0 10px 0;">
              <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; font-weight:700; margin-bottom:8px;">
                Project Description &amp; Requirements:
              </div>
              <div style="background:#09090f; border-left:4px solid #6366f1; border-radius:4px; padding:16px 20px; font-size:14.5px; line-height:1.6; color:#f1f5f9; white-space:pre-wrap;">' . esc_html($message) . '</div>
            </div>

            <!-- Action Buttons -->
            <div style="margin-top:30px; text-align:center;">
              <a href="' . esc_url($mailto_reply) . '" style="background:#6366f1; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; padding:12px 24px; border-radius:8px; display:inline-block; margin-right:12px;">
                Reply Directly to ' . esc_html($first_name) . ' ↗
              </a>
              <a href="' . esc_url($admin_url_lead) . '" style="background:#1e1b4b; color:#c7d2fe; font-size:14px; font-weight:600; text-decoration:none; padding:12px 20px; border-radius:8px; border:1px solid rgba(99,102,241,0.4); display:inline-block;">
                View Lead in WP Admin
              </a>
            </div>

          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background-color:#09090f; padding:20px 32px; border-top:1px solid rgba(255,255,255,0.06); text-align:center; font-size:12px; color:#64748b;">
            RMS Premier Services · PMP®-Certified Healthcare &amp; IT Project Management<br>
            Delivered securely via your WordPress Consultation System on ' . current_time('F j, Y \a\t g:i a') . '
          </td>
        </tr>

      </table>
    </body>
    </html>';

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . sprintf('%s <%s>', $full_name, $email),
    );

    // Add CC if configured
    $cc_option = get_option('rms_consultation_cc');
    if (!empty($cc_option)) {
        $cc_emails = array_map('trim', explode(',', $cc_option));
        foreach ($cc_emails as $cc_item) {
            if (is_email($cc_item)) {
                $headers[] = 'Cc: ' . $cc_item;
            }
        }
    }

    // Send email to business
    $mail_sent = wp_mail($business_email, $subject, $email_html, $headers);

    // 7. Send Auto-Reply to Prospective Client (if enabled)
    $auto_reply_enabled = get_option('rms_auto_reply_enabled', '1');
    if ($auto_reply_enabled === '1') {
        $client_subject = 'Consultation Request Received — RMS Premier Services';
        $client_body = '
        <!DOCTYPE html>
        <html>
        <head><meta charset="utf-8"></head>
        <body style="margin:0; padding:20px; background-color:#09090f; font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif; color:#e2e4f0;">
          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:580px; background-color:#141425; border-radius:12px; border:1px solid rgba(99,102,241,0.25); overflow:hidden;">
            <tr>
              <td style="background:linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); padding:28px 32px; border-bottom:1px solid rgba(99,102,241,0.3);">
                <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.12em; color:#a5b4fc; font-weight:700; margin-bottom:6px;">RMS Premier Services</div>
                <h1 style="margin:0; font-size:22px; color:#ffffff; font-weight:700;">Thank You for Contacting Us</h1>
              </td>
            </tr>
            <tr>
              <td style="padding:32px;">
                <p style="font-size:15px; line-height:1.6; color:#cbd5e1; margin-top:0;">Dear ' . esc_html($first_name) . ',</p>
                <p style="font-size:15px; line-height:1.6; color:#cbd5e1;">
                  Thank you for reaching out to <strong>RMS Premier Services</strong> regarding <strong>' . esc_html($service_label) . '</strong>.
                </p>
                <p style="font-size:15px; line-height:1.6; color:#cbd5e1;">
                  We have received your project details and a senior PMP-certified consultant will review your inquiry and follow up within <strong>24 business hours</strong>.
                </p>
                <div style="background:#0f0f1a; border-left:3px solid #6366f1; padding:14px 18px; margin:24px 0; border-radius:4px; font-size:13.5px; color:#94a3b8;">
                  <strong>Need immediate assistance?</strong><br>
                  You can reach us directly at <a href="mailto:' . esc_attr($business_email) . '" style="color:#818cf8; text-decoration:none;">' . esc_html($business_email) . '</a>.
                </div>
                <p style="font-size:14px; color:#cbd5e1; margin-bottom:0;">
                  Warm regards,<br>
                  <strong style="color:#ffffff;">The RMS Premier Services Team</strong><br>
                  <span style="font-size:12px; color:#94a3b8;">PMP-Certified · HIPAA-Compliant · Nationwide Remote Services</span>
                </p>
              </td>
            </tr>
          </table>
        </body>
        </html>';

        $client_headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: RMS Premier Services <' . $business_email . '>',
        );
        wp_mail($email, $client_subject, $client_body, $client_headers);
    }

    // Always succeed from user perspective since lead is saved in DB
    wp_send_json_success(array(
        'message' => __('✓ Message sent! We\'ll be in touch within 24 business hours.', 'rms-premier-services')
    ));
}
add_action('wp_ajax_rms_submit_consultation', 'rms_handle_consultation_submission');
add_action('wp_ajax_nopriv_rms_submit_consultation', 'rms_handle_consultation_submission');
