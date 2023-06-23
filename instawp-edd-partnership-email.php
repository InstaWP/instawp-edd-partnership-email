<?php
/*
Plugin Name: InstaWP EDD Partnership Email
Description: A WordPress plugin that sends email notifications for new orders in Easy Digital Downloads.
Version: 1.0
Author: InstaWP
Author URI: https://instawp.com
*/

// Add a custom menu item in the admin panel
function custom_email_notifications_menu() {
    add_submenu_page(
        'tools.php',
        'InstaWP Ads Email',
        'InstaWP Ads Email',
        'manage_options',
        'instawp-ads-email',
        'custom_email_notifications_settings_page'
    );
}
add_action('admin_menu', 'custom_email_notifications_menu');

// Create the settings page
function custom_email_notifications_settings_page() {
    // Check if Easy Digital Downloads plugin is active
    if (!is_plugin_active('easy-digital-downloads/easy-digital-downloads.php')) {
        echo '<div class="notice notice-warning"><p><strong>Warning:</strong> The Easy Digital Downloads plugin is not installed or activated. Please install and activate it to use this plugin.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>InstaWP Ads Email Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('custom_email_notifications_settings');
            do_settings_sections('custom_email_notifications_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register plugin settings and fields
function custom_email_notifications_register_settings() {
    add_settings_section(
        'custom_email_notifications_section',
        'Email Settings',
        'custom_email_notifications_section_callback',
        'custom_email_notifications_settings'
    );

    add_settings_field(
        'plugin_name',
        'Plugin Name',
        'plugin_name_field_callback',
        'custom_email_notifications_settings',
        'custom_email_notifications_section'
    );

    add_settings_field(
        'coupon_code',
        'Coupon Code',
        'coupon_code_field_callback',
        'custom_email_notifications_settings',
        'custom_email_notifications_section'
    );

    add_settings_field(
        'instawp_url',
        'Instawp URL',
        'instawp_url_field_callback',
        'custom_email_notifications_settings',
        'custom_email_notifications_section'
    );

    add_settings_field(
        'email_subject',
        'Email Subject',
        'email_subject_field_callback',
        'custom_email_notifications_settings',
        'custom_email_notifications_section'
    );

    add_settings_field(
        'email_contents',
        'Email Contents',
        'email_contents_field_callback',
        'custom_email_notifications_settings',
        'custom_email_notifications_section'
    );

    register_setting('custom_email_notifications_settings', 'plugin_name');
    register_setting('custom_email_notifications_settings', 'coupon_code');
    register_setting('custom_email_notifications_settings', 'instawp_url');
    register_setting('custom_email_notifications_settings', 'email_subject');
    register_setting('custom_email_notifications_settings', 'email_contents');
}
add_action('admin_init', 'custom_email_notifications_register_settings');

// Section callback
function custom_email_notifications_section_callback() {
    echo '<p>Configure the email settings for your plugin:</p>';
}

// Plugin name field callback
function plugin_name_field_callback() {
    $value = get_option('plugin_name');
    echo '<input type="text" name="plugin_name" value="' . esc_attr($value) . '" />';
}

// Coupon code field callback
function coupon_code_field_callback() {
    $value = get_option('coupon_code');
    echo '<input type="text" name="coupon_code" value="' . esc_attr($value) . '" />';
}

// Instawp URL field callback
function instawp_url_field_callback() {
    $value = get_option('instawp_url');
    echo '<input type="text" name="instawp_url" value="' . esc_attr($value) . '" />';
}

// Email subject field callback
function email_subject_field_callback() {
    $value = get_option('email_subject');
    echo '<input type="text" name="email_subject" value="' . esc_attr($value) . '" />';
}

// Email contents field callback
function email_contents_field_callback() {
    $value = get_option('email_contents');
    wp_editor($value, 'email_contents', array('textarea_name' => 'email_contents'));
}

// Send email notification on new order
function custom_email_notifications_send_email($payment_id) {
    
	

    $plugin_name = get_option('plugin_name');
    $coupon_code = get_option('coupon_code');
    $instawp_url = get_option('instawp_url');
    $email_subject = get_option('email_subject');
    $email_contents = get_option('email_contents');

    $payment = new EDD_Payment($payment_id);
	if(!isset($payment->email)) {
		return;
	}
    $name = $payment->first_name; // Change 'first_name' to 'name' if 'name' field is used
    $subject = $email_subject;
	$subject = str_replace('{plugin_name}', $plugin_name, $email_subject);
    $message = $email_contents;
    $message = str_replace('{name}', $name, $message); // Replace '{email}' with '{name}'
    $message = str_replace('{plugin_name}', $plugin_name, $message);
    $message = str_replace('{coupon_code}', $coupon_code, $message);
    $message = str_replace('{instawp_url}', $instawp_url, $message);

    wp_mail($payment->email, $subject, $message);
}
add_action('edd_complete_purchase', 'custom_email_notifications_send_email');
