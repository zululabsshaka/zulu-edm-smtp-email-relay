<?php
/*
Plugin Name: Zulu eDM WP SMTP Email Plugin
Version: 1.1.3
Plugin URI: https://github.com/zululabsshaka/zulu-edm-smtp-email-relay
Author: zululabs
Author URI: http://github.com/zululabsshaka/
Description: Use this SMTP relay plugin to send emails using the Zulu eDM Trusted Sender Network email service. This service is a scalable and robust
email delivery network that ensures all email traffic meets stringent email protocols.



 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!defined('ABSPATH')){
    exit;
}

class ZULU_EDM_SMTP_MAILER {

    var $plugin_version = '1.1.3';
    var $phpmailer_version = '6.0.5';
    var $plugin_url;
    var $plugin_path;

    function __construct() {
        define('ZULU_EDM_SMTP_MAILER_VERSION', $this->plugin_version);
        define('ZULU_EDM_SMTP_MAILER_SITE_URL', site_url());
        define('ZULU_EDM_SMTP_MAILER_HOME_URL', home_url());
        define('ZULU_EDM_SMTP_MAILER_URL', $this->plugin_url());
        define('ZULU_EDM_SMTP_MAILER_PATH', $this->plugin_path());
        //$this->plugin_includes();
        $this->loader_operations();
    }

    function loader_operations() {
        if (is_admin()) {
            add_filter('plugin_action_links', array($this, 'add_plugin_action_links'), 10, 2);
        }
        add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
        add_action('admin_menu', array($this, 'options_menu'));
        add_action('admin_notices', 'zulu_edm_smtp_mailer_admin_notice');
    }

    function plugins_loaded_handler()
    {
        load_plugin_textdomain('zulu-edm-smtp-mailer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
    }

    function plugin_url() {
        if ($this->plugin_url)
            return $this->plugin_url;
        return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
    }

    function plugin_path() {
        if ($this->plugin_path)
            return $this->plugin_path;
        return $this->plugin_path = untrailingslashit(plugin_dir_path(__FILE__));
    }

    function add_plugin_action_links($links, $file) {
        if ($file == plugin_basename(dirname(__FILE__) . '/main.php')) {
            $links[] = '<a href="options-general.php?page=zulu-edm-smtp-mailer-settings">'.__('Settings', 'zulu-edm-smtp-mailer').'</a>';
        }
        return $links;
    }

    function options_menu() {
        add_options_page(__('Zulu eDM SMTP Gateway', 'zulu-edm-smtp-mailer'), __('Zulu eDM SMTP Gateway', 'zulu-edm-smtp-mailer'), 'manage_options', 'zulu-edm-smtp-mailer-settings', array($this, 'options_page'));
    }

    function options_page() {
        $plugin_tabs = array(
            'zulu-edm-smtp-mailer-settings' => __('Zulu SMTP Account', 'zulu-edm-smtp-mailer'),
            'zulu-edm-smtp-mailer-settings&action=test-email' => __('Email Test', 'zulu-edm-smtp-mailer'),
            'zulu-edm-smtp-mailer-settings&action=server-info' => __('Server Info', 'zulu-edm-smtp-mailer'),
            'zulu-edm-smtp-mailer-settings&action=smtp-tools' => __('Reputation Tools', 'zulu-edm-smtp-mailer'),
            'zulu-edm-smtp-mailer-settings&action=credits' => __('Credits', 'zulu-edm-smtp-mailer'),
        );
        $url = "https://support.zululabs.com/index.php?/Knowledgebase/Article/View/wordpress-plugin-zulu-edm-wp-smtp-email-plugin/";
        $link_text = sprintf(wp_kses(__('Please visit the <a target="_blank" href="%s">Zulu eDM WP SMTP Gateway</a> support page for instructions.', 'zulu-edm-smtp-mailer'), array('a' => array('href' => array(), 'target' => array()))), esc_url($url));
        echo '<div class="wrap"><h2>Zulu eDM SMTP Gateway v ' . ZULU_EDM_SMTP_MAILER_VERSION . '</h2>';
        echo '<div class="update-nag">'.$link_text.'</div>';
        if (isset($_GET['page'])) {
            $current = $_GET['page'];
            if (isset($_GET['action'])) {
                $current .= "&action=" . $_GET['action'];
            }
        }
        $content = '';
        $content .= '<h2 class="nav-tab-wrapper">';
        foreach ($plugin_tabs as $location => $tabname) {
            if ($current == $location) {
                $class = ' nav-tab-active';
            } else {
                $class = '';
            }
            $content .= '<a class="nav-tab' . $class . '" href="?page=' . $location . '">' . $tabname . '</a>';
        }
        $content .= '</h2>';
        echo $content;

        if(isset($_GET['action']) && $_GET['action'] == 'test-email'){
            $this->test_email_settings();
        }
        else if(isset($_GET['action']) && $_GET['action'] == 'server-info'){
            $this->server_info_settings();
        }
        else if(isset($_GET['action']) && $_GET['action'] == 'smtp-tools'){
            $this->emailTools();
        }
      else if(isset($_GET['action']) && $_GET['action'] == 'credits'){
       $this->credits();
       }
        else{
            $this->general_settings();
        }
        echo '</div>';
    }

    function test_email_settings(){
        if(isset($_POST['zulu_edm_smtp_mailer_send_test_email'])){
            $to = '';
            if(isset($_POST['zulu_edm_smtp_mailer_to_email']) && !empty($_POST['zulu_edm_smtp_mailer_to_email'])){
                $to = sanitize_text_field($_POST['zulu_edm_smtp_mailer_to_email']);
            }
            $subject = '';
            if(isset($_POST['zulu_edm_smtp_mailer_email_subject']) && !empty($_POST['zulu_edm_smtp_mailer_email_subject'])){
                $subject = sanitize_text_field($_POST['zulu_edm_smtp_mailer_email_subject']);
            }
            $message = '';
            if(isset($_POST['zulu_edm_smtp_mailer_email_body']) && !empty($_POST['zulu_edm_smtp_mailer_email_body'])){
                $message = sanitize_text_field($_POST['zulu_edm_smtp_mailer_email_body']);
            }
            wp_mail($to, $subject, $message);
        }
        ?>
        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <?php wp_nonce_field('zulu_edm_smtp_mailer_test_email'); ?>

            <table class="form-table">

                <tbody>

                <tr valign="top">
                    <th class="" scope="row"><label for="zulu_edm_smtp_mailer_to_email"><?php _e('To', 'zulu-edm-smtp-mailer');?></label></th>
                    <td><input name="zulu_edm_smtp_mailer_to_email" type="text" id="zulu_edm_smtp_mailer_to_email" value="" class="regular-text">
                        <p class="description"><?php _e('Recipient email ', 'zulu-edm-smtp-mailer');?></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="zulu_edm_smtp_mailer_email_subject"><?php _e('Subject', 'zulu-edm-smtp-mailer');?></label></th>
                    <td><input name="zulu_edm_smtp_mailer_email_subject" type="hidden" id="zulu_edm_smtp_mailer_email_subject" value="Test: Zulu eDM SMTP" class="regular-text">
                        <p class="description"><?php _e('Test: Zulu eDM SMTP', 'zulu-edm-smtp-mailer');?></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="zulu_edm_smtp_mailer_email_body"><?php _e('Message', 'zulu-edm-smtp-mailer');?></label></th>
                    <td><input type="hidden" name="zulu_edm_smtp_mailer_email_body" id="zulu_edm_smtp_mailer_email_body" value="This Test email was sent from your WordPress Site.... Shaka">
                        <p class="description"><?php _e('This Test email was sent from your WordPress Site.... Shaka', 'zulu-edm-smtp-mailer');?></p></td>
                </tr>

                </tbody>

            </table>

            <p class="submit"><input type="submit" name="zulu_edm_smtp_mailer_send_test_email" id="zulu_edm_smtp_mailer_send_test_email" class="button button-primary" value="<?php _e('Test Email Settings', 'zulu-edm-smtp-mailer');?>"></p>
        </form>

        <?php
    }

    function server_info_settings()
    {
        $server_info = '';
        $server_info .= sprintf('OS: %s%s', php_uname(), PHP_EOL);
        $server_info .= sprintf('PHP version: %s%s', PHP_VERSION, PHP_EOL);
        $server_info .= sprintf('WordPress version: %s%s', get_bloginfo('version'), PHP_EOL);
        $server_info .= sprintf('WordPress multisite: %s%s', (is_multisite() ? 'Yes' : 'No'), PHP_EOL);
        $openssl_status = 'Available';
        $openssl_text = '';
        if(!extension_loaded('openssl') && !defined('OPENSSL_ALGO_SHA1')){
            $openssl_status = 'Not available';
            $openssl_text = ' (openssl extension is required in order to use any kind of encryption like TLS or SSL)';
        }
        $server_info .= sprintf('openssl: %s%s%s', $openssl_status, $openssl_text, PHP_EOL);
        $server_info .= sprintf('allow_url_fopen: %s%s', (ini_get('allow_url_fopen') ? 'Enabled' : 'Disabled'), PHP_EOL);
        $stream_socket_client_status = 'Not Available';
        $fsockopen_status = 'Not Available';
        $socket_enabled = false;
        if(function_exists('stream_socket_client')){
            $stream_socket_client_status = 'Available';
            $socket_enabled = true;
        }
        if(function_exists('fsockopen')){
            $fsockopen_status = 'Available';
            $socket_enabled = true;
        }
        $socket_text = '';
        if(!$socket_enabled){
            $socket_text = ' (In order to make a SMTP connection your server needs to have either stream_socket_client or fsockopen)';
        }
        $server_info .= sprintf('stream_socket_client: %s%s', $stream_socket_client_status, PHP_EOL);
        $server_info .= sprintf('fsockopen: %s%s%s', $fsockopen_status, $socket_text, PHP_EOL);
        ?>
        <textarea rows="10" cols="50" class="large-text code"><?php echo $server_info;?></textarea>
        <?php
    }

    function emailTools()
    {
        $email_tools = '<div class="large-text code"><h3>Email Reputation Tools</h3><p>Free tools to configure email domains for reputation management and cyber email security.</p>';
        $email_tools = $email_tools . '<ul><li><a href="http://trustedsenderscore.com" target="_blank">Domain Trust Score</a></li>';
        $email_tools = $email_tools . '<li><a href="https://zuluedm.com/trusted-sender/1.0/" target="_blank">Email DNS Check</a></li>';
        $email_tools = $email_tools . '<li><a href="https://zuluedm.com/free/dkim-wizard/doman-key-generator" target="_blank">DKIM Wizard</a></li>';
        $email_tools = $email_tools . '<li><a href="https://zuluedm.com/trusted-sender/1.0/free-email-developer-tools.php" target="_blank">Encoding Tools</a></li>';
        $email_tools = $email_tools . '<li><a href="https://zuluedm.com/meaningful-google-analytics/edm-utm-link-tracking.php" target="_blank">Link Tracking Builder</a></li>';
        $email_tools = $email_tools . '<li><a href="https://zuluedm.com/meaningful-google-analytics/" target="_blank">Imporve Website &amp; Email Conversion Reports with our Google Analytics Enhancement</a></li>';
        $email_tools = $email_tools . '<li><a href="https://zuluedm.com/trusted-sender/1.0/free-ssl-check.php" target="_blank">SSL Check</a></li></ul></div>';
        echo $email_tools;
       }

    function credits()
    {

        $credits = '<div class="large-text code"><h3>Legal Notices</h3><p>This plugin is governed by the Zulu eDM Legal Notices listed below. Also listed are third party source code credits used';
        $credits = $credits . 'to produce this free plugin. Each piece of open source used is covered by their own agreements.</p>';
        $credits = $credits . '<ul><li><a href="https://zuluedm.com/legal-notices" target="_blank">Legal Notices</a></li>';
        $credits = $credits . '<li><a href="https://zuluedm.com/ip-addresses-sms-numbers/" target="_blank">IP Addresses</a></li>';
        $credits = $credits . '<li><a href="https://blacklists.zuluedm.com/" target="_blank">Our IP Addresses Health</a></li>';
        $credits = $credits . '<li><a href="https://support.zululabs.com" target="_blank">Zulu Support</a></li>';
        $credits = $credits . '<li><a href="https://github.com/PHPMailer/PHPMailer" target="_blank">Credit: PHPMailer</a></li>';
        $credits = $credits . '<li><a href="https://github.com/nategood/httpful" target="_blank">Credit: HTTPful</a></li></ul></div>';
        echo $credits;

    }



    function general_settings() {

        if (isset($_POST['zulu_edm_smtp_mailer_update_settings'])) {
            $nonce = $_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'zulu_edm_smtp_mailer_general_settings')) {
                wp_die('Error! Nonce Security Check Failed! please save the settings again.');
            }
            $smtp_host = 'pmta.zululabs.com';
            if(isset($_POST['smtp_host']) && !empty($_POST['smtp_host'])){
                $smtp_host = sanitize_text_field($_POST['smtp_host']);
            }
            $smtp_auth = 'true';
            if(isset($_POST['smtp_auth']) && !empty($_POST['smtp_auth'])){
                $smtp_auth = sanitize_text_field($_POST['smtp_auth']);
            }
            $smtp_username = '';
            if(isset($_POST['smtp_username']) && !empty($_POST['smtp_username'])){
                $smtp_username = sanitize_text_field($_POST['smtp_username']);
            }
            $smtp_password = '';
            if(isset($_POST['smtp_password']) && !empty($_POST['smtp_password'])){
                //echo "password: ".$_POST['smtp_password'];
                $smtp_password = sanitize_text_field($_POST['smtp_password']);
                $smtp_password = wp_unslash($smtp_password); // This removes slash (automatically added by WordPress) from the password when apostrophe is present
                $smtp_password = base64_encode($smtp_password);
            }
            $type_of_encryption = 'PLAIN';
            if(isset($_POST['type_of_encryption']) && !empty($_POST['type_of_encryption'])){
                $type_of_encryption = sanitize_text_field($_POST['type_of_encryption']);
            }
            $smtp_port = '25';
            if(isset($_POST['smtp_port']) && !empty($_POST['smtp_port'])){
                $smtp_port = sanitize_text_field($_POST['smtp_port']);
            }
            $from_email = '';
            if(isset($_POST['from_email']) && !empty($_POST['from_email'])){
                $from_email = sanitize_email($_POST['from_email']);
            }
            $from_name = '';
            if(isset($_POST['from_name']) && !empty($_POST['from_name'])){
                $from_name = sanitize_text_field(stripslashes($_POST['from_name']));
            }
            $disable_ssl_verification = '';
            if(isset($_POST['disable_ssl_verification']) && !empty($_POST['disable_ssl_verification'])){
                $disable_ssl_verification = sanitize_text_field($_POST['disable_ssl_verification']);
            }
            $options = array();
            $options['smtp_host'] = $smtp_host;
            $options['smtp_auth'] = $smtp_auth;
            $options['smtp_username'] = $smtp_username;
            $options['smtp_password'] = $smtp_password;
            $options['type_of_encryption'] = $type_of_encryption;
            $options['smtp_port'] = $smtp_port;
            $options['from_email'] = $from_email;
            $options['from_name'] = $from_name;
            $options['disable_ssl_verification'] = $disable_ssl_verification;
            zulu_edm_smtp_mailer_update_option($options);
            echo '<div id="message" class="updated fade"><p><strong>';
            echo __('Settings Saved!', 'zulu-edm-smtp-mailer');
            echo '</strong></p></div>';
        }

        $options = zulu_edm_smtp_mailer_get_option();
        if(!is_array($options)){
            $options = array();
            $options['smtp_host'] = 'pmta.zululabs.com';
            $options['smtp_auth'] = 'true';
            $options['smtp_username'] = '';
            $options['smtp_password'] = '';
            $options['type_of_encryption'] = 'PLAIN';
            $options['smtp_port'] = '25';
            $options['from_email'] = '';
            $options['from_name'] = '';
            $options['disable_ssl_verification'] = 'true';
        }

        // Avoid warning notice since this option was added later
        if(!isset($options['disable_ssl_verification'])){
            $options['disable_ssl_verification'] = 'true';
        }

        $smtp_password = '';
        if(isset($options['smtp_password']) && !empty($options['smtp_password'])){
            $smtp_password = base64_decode($options['smtp_password']);
        }
        ?>

        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <?php wp_nonce_field('zulu_edm_smtp_mailer_general_settings'); ?>

            <table class="form-table">

                <tbody>

                <tr valign="top">
                    <th scope="row"><label for="smtp_host"><?php _e('SMTP Host', 'zulu-edm-smtp-mailer');?></label></th>
                    <td><input name="smtp_host" id="smtp_host" value="<?php echo $options['smtp_host']; ?>" class="regular-text" disabled>
                        <p class="description"><?php _e('The SMTP server which will be used to send email.', 'zulu-edm-smtp-mailer');?></p></td>
                </tr>

                <tr>
                    <th scope="row"><label for="smtp_auth"><?php _e('Zulu eDM Trusted Sender Authentication', 'zulu-edm-smtp-mailer');?></label></th>
                    <td>
                        <input name="smtp_auth" id="smtp_auth" value="true" class="regular-text code" disabled>
                        <p class="description"><?php _e('<a target="_blank" href="https://zuluedm.com/trusted-sender/1.0/zuluedmsettings.php?utm_source=Zulu%20eDM&utm_medium=WPPlugin&utm_campaign=Trusted%20Sender">Visit Zulu eDM Trusted Sender</a> 
                        To Register.', 'zulu-edm-smtp-mailer');?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="smtp_username"><?php _e('Zulu eDM SMTP Username', 'zulu-edm-smtp-mailer');?></label></th>
                    <td><input name="smtp_username" type="text" id="smtp_username" value="<?php echo $options['smtp_username']; ?>" class="regular-text code">
                        <p class="description"><?php _e('Your SMTP Username.', 'zulu-edm-smtp-mailer');?></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="smtp_password"><?php _e('Zulu eDM SMTP Password', 'zulu-edm-smtp-mailer');?></label></th>
                    <td><input name="smtp_password" type="password" id="smtp_password" value="<?php echo $smtp_password; ?>" class="regular-text code">
                        <p class="description"><?php _e('Your SMTP Password.', 'zulu-edm-smtp-mailer');?></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="smtp_port"><?php _e('SMTP Port', 'zulu-edm-smtp-mailer');?></label></th>
                    <td><input name="smtp_port" id="smtp_port" value="25" class="regular-text code" disabled>
                        <p class="description"><?php _e('The port which will be used when sending an email (587/465/25).', 'zulu-edm-smtp-mailer');?></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="from_email"><?php _e('From Email Address', 'zulu-edm-smtp-mailer');?></label></th>
                    <td><input name="from_email" type="text" id="from_email" value="<?php echo $options['from_email']; ?>" class="regular-text code">
                        <p class="description"><?php _e('An email address that is using the domain that is approved with Zulu eDM and your DMARC record.', 'zulu-edm-smtp-mailer');?></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="from_name"><?php _e('From Name', 'zulu-edm-smtp-mailer');?></label></th>
                    <td><input name="from_name" type="text" id="from_name" value="<?php echo $options['from_name']; ?>" class="regular-text code">
                        <p class="description"><?php _e('The name which will be used as the From Name if it is not supplied to the mail function.', 'zulu-edm-smtp-mailer');?></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="disable_ssl_verification"><?php _e('Disable SSL Certificate Verification', 'zulu-edm-smtp-mailer');?></label></th>
                    <td><input name="disable_ssl_verification" id="disable_ssl_verification" value="true" disabled>
                        <p class="description"><?php _e('If your webserver has TLS support we will automatically encrypt the email', 'zulu-edm-smtp-mailer');?></p></td>
                </tr>

                </tbody>

            </table>

            <p class="submit"><input type="submit" name="zulu_edm_smtp_mailer_update_settings" id="zulu_edm_smtp_mailer_update_settings" class="button button-primary" value="<?php _e('Save Changes', 'zulu-edm-smtp-mailer')?>"></p>
        </form>

        <?php
    }
}

function zulu_edm_smtp_mailer_get_option(){
    $options = get_option('zulu_edm_smtp_mailer_options');
    return $options;
}

function zulu_edm_smtp_mailer_update_option($options){
    update_option('zulu_edm_smtp_mailer_options', $options);
}

function zulu_edm_smtp_mailer_admin_notice() {
    if(!is_zulu_edm_smtp_mailer_configured()){
        ?>
        <div class="error">
            <p><?php _e('Zulu eDM SMTP Gateway plugin cannot send email until you enter your credentials in the settings.', 'zulu-edm-smtp-mailer'); ?></p>
        </div>
        <?php
    }
}

function is_zulu_edm_smtp_mailer_configured() {
    $options = zulu_edm_smtp_mailer_get_option();
    $smtp_configured = true;
    if(!isset($options['smtp_host']) || empty($options['smtp_host'])){
        $smtp_configured = false;
    }
    if(!isset($options['smtp_auth']) || empty($options['smtp_auth'])){
        $smtp_configured = false;
    }
    if(isset($options['smtp_auth']) && $options['smtp_auth'] == "true"){
        if(!isset($options['smtp_username']) || empty($options['smtp_username'])){
            $smtp_configured = false;
        }
        if(!isset($options['smtp_password']) || empty($options['smtp_password'])){
            $smtp_configured = false;
        }
    }
    if(!isset($options['type_of_encryption']) || empty($options['type_of_encryption'])){
        $smtp_configured = false;
    }
    if(!isset($options['smtp_port']) || empty($options['smtp_port'])){
        $smtp_configured = false;
    }
    if(!isset($options['from_email']) || empty($options['from_email'])){
        $smtp_configured = false;
    }
    if(!isset($options['from_name']) || empty($options['from_name'])){
        $smtp_configured = false;
    }
    return $smtp_configured;
}

$GLOBALS['zulu_edm_smtp_mailer'] = new ZULU_EDM_SMTP_MAILER();

if(!function_exists('wp_mail') && is_zulu_edm_smtp_mailer_configured()){

    function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
        // Compact the input, apply the filters, and extract them back out

        /**
         * Filters the wp_mail() arguments.
         *
         * @since 2.2.0
         *
         * @param array $args A compacted array of wp_mail() arguments, including the "to" email,
         *                    subject, message, headers, and attachments values.
         */
        $atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );

        if ( isset( $atts['to'] ) ) {
            $to = $atts['to'];
        }

        if ( !is_array( $to ) ) {
            $to = explode( ',', $to );
        }

        if ( isset( $atts['subject'] ) ) {
            $subject = $atts['subject'];
        }

        if ( isset( $atts['message'] ) ) {
            $message = $atts['message'];
        }

        if ( isset( $atts['headers'] ) ) {
            $headers = $atts['headers'];
        }

        if ( isset( $atts['attachments'] ) ) {
            $attachments = $atts['attachments'];
        }

        if ( ! is_array( $attachments ) ) {
            $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
        }

        $options = zulu_edm_smtp_mailer_get_option();

        global $phpmailer;

        // (Re)create it, if it's gone missing
        if ( ! ( $phpmailer instanceof PHPMailer ) ) {
            require 'src/PHPMailer.php';
            require 'src/Exception.php';
            require 'src/SMTP.php';
            $phpmailer = new PHPMailer( true );
        }

        // Headers
        $cc = $bcc = $reply_to = array();

        if ( empty( $headers ) ) {
            $headers = array();
        } else {
            if ( !is_array( $headers ) ) {
                // Explode the headers out, so this function can take both
                // string headers and an array of headers.
                $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
            } else {
                $tempheaders = $headers;
            }
            $headers = array();

            // If it's actually got contents
            if ( !empty( $tempheaders ) ) {
                // Iterate through the raw headers
                foreach ( (array) $tempheaders as $header ) {
                    if ( strpos($header, ':') === false ) {
                        if ( false !== stripos( $header, 'boundary=' ) ) {
                            $parts = preg_split('/boundary=/i', trim( $header ) );
                            $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                        }
                        continue;
                    }
                    // Explode them out
                    list( $name, $content ) = explode( ':', trim( $header ), 2 );

                    // Cleanup crew
                    $name    = trim( $name    );
                    $content = trim( $content );

                    switch ( strtolower( $name ) ) {
                        // Mainly for legacy -- process a From: header if it's there
                        case 'from':
                            $bracket_pos = strpos( $content, '<' );
                            if ( $bracket_pos !== false ) {
                                // Text before the bracketed email is the "From" name.
                                if ( $bracket_pos > 0 ) {
                                    $from_name = substr( $content, 0, $bracket_pos - 1 );
                                    $from_name = str_replace( '"', '', $from_name );
                                    $from_name = trim( $from_name );
                                }

                                $from_email = substr( $content, $bracket_pos + 1 );
                                $from_email = str_replace( '>', '', $from_email );
                                $from_email = trim( $from_email );

                                // Avoid setting an empty $from_email.
                            } elseif ( '' !== trim( $content ) ) {
                                $from_email = trim( $content );
                            }
                            break;
                        case 'content-type':
                            if ( strpos( $content, ';' ) !== false ) {
                                list( $type, $charset_content ) = explode( ';', $content );
                                $content_type = trim( $type );
                                if ( false !== stripos( $charset_content, 'charset=' ) ) {
                                    $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                                } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                                    $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                                    $charset = '';
                                }

                                // Avoid setting an empty $content_type.
                            } elseif ( '' !== trim( $content ) ) {
                                $content_type = trim( $content );
                            }
                            break;
                        case 'cc':
                            $cc = array_merge( (array) $cc, explode( ',', $content ) );
                            break;
                        case 'bcc':
                            $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                            break;
                        case 'reply-to':
                            $reply_to = array_merge( (array) $reply_to, explode( ',', $content ) );
                            break;
                        default:
                            // Add it to our grand headers array
                            $headers[trim( $name )] = trim( $content );
                            break;
                    }
                }
            }
        }

        // Empty out the values that may be set
        $phpmailer->clearAllRecipients();
        $phpmailer->clearAttachments();
        $phpmailer->clearCustomHeaders();
        $phpmailer->clearReplyTos();

        // From email and name
        // If we don't have a name from the input headers
        if ( !isset( $from_name ) ){
            $from_name = $options['from_name'];//'WordPress';
        }


        if ( !isset( $from_email ) ) {
            // Get the site domain and get rid of www.
            $sitename = strtolower( $_SERVER['SERVER_NAME'] );
            if ( substr( $sitename, 0, 4 ) == 'www.' ) {
                $sitename = substr( $sitename, 4 );
            }

            $from_email = $options['from_email'];//'wordpress@' . $sitename;
        }

        /**
         * Filters the email address to send from.
         *
         * @since 2.2.0
         *
         * @param string $from_email Email address to send from.
         */
        $from_email = apply_filters( 'wp_mail_from', $from_email );

        /**
         * Filters the name to associate with the "from" email address.
         *
         * @since 2.3.0
         *
         * @param string $from_name Name associated with the "from" email address.
         */
        $from_name = apply_filters( 'wp_mail_from_name', $from_name );

        try {
            $phpmailer->setFrom( $from_email, $from_name, false );
        } catch ( phpmailerException $e ) {
            $mail_error_data = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

            /** This filter is documented in wp-includes/pluggable.php */
            do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_error_data ) );

            return false;
        }

        // Set mail's subject and body
        $phpmailer->Subject = $subject;
        $phpmailer->Body    = $message;

        // Set destination addresses, using appropriate methods for handling addresses
        $address_headers = compact( 'to', 'cc', 'bcc', 'reply_to' );

        foreach ( $address_headers as $address_header => $addresses ) {
            if ( empty( $addresses ) ) {
                continue;
            }

            foreach ( (array) $addresses as $address ) {
                try {
                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                    $recipient_name = '';

                    if ( preg_match( '/(.*)<(.+)>/', $address, $matches ) ) {
                        if ( count( $matches ) == 3 ) {
                            $recipient_name = $matches[1];
                            $address        = $matches[2];
                        }
                    }

                    switch ( $address_header ) {
                        case 'to':
                            $phpmailer->addAddress( $address, $recipient_name );
                            break;
                        case 'cc':
                            $phpmailer->addCc( $address, $recipient_name );
                            break;
                        case 'bcc':
                            $phpmailer->addBcc( $address, $recipient_name );
                            break;
                        case 'reply_to':
                            $phpmailer->addReplyTo( $address, $recipient_name );
                            break;
                    }
                } catch ( phpmailerException $e ) {
                    continue;
                }
            }
        }

        // Tell PHPMailer to use SMTP
        $phpmailer->isSMTP(); //$phpmailer->IsMail();
        // Set the hostname of the mail server
        $phpmailer->Host = $options['smtp_host'];
        // Whether to use SMTP authentication
        if(isset($options['smtp_auth']) && $options['smtp_auth'] == "true"){
            $phpmailer->SMTPAuth = true;
            $phpmailer->AuthType = 'PLAIN';
            // SMTP username
            $phpmailer->Username = $options['smtp_username'];
            // SMTP password
            $phpmailer->Password = base64_decode($options['smtp_password']);
        }
        // Whether to use encryption
        /*   $type_of_encryption = $options['type_of_encryption'];
           if($type_of_encryption=="none"){
               $type_of_encryption = '';
           }
        */
        $phpmailer->SMTPSecure = $type_of_encryption;
        // SMTP port
        $phpmailer->Port = $options['smtp_port'];

        // Whether to enable TLS encryption automatically if a server supports it
        $phpmailer->SMTPAutoTLS = false;
        //enable debug when sending a test mail
        if(isset($_POST['zulu_edm_smtp_mailer_send_test_email'])){
            $phpmailer->SMTPDebug = 4;
            // Ask for HTML-friendly debug output
            $phpmailer->Debugoutput = 'html';
        }

        //disable ssl certificate verification if checked
        if(isset($options['disable_ssl_verification']) && !empty($options['disable_ssl_verification'])){
            $phpmailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        }

        // Set Content-Type and charset
        // If we don't have a content-type from the input headers
        if ( !isset( $content_type ) )
            $content_type = 'text/plain';

        /**
         * Filters the wp_mail() content type.
         *
         * @since 2.3.0
         *
         * @param string $content_type Default wp_mail() content type.
         */
        $content_type = apply_filters( 'wp_mail_content_type', $content_type );

        $phpmailer->ContentType = $content_type;

        // Set whether it's plaintext, depending on $content_type
        if ( 'text/html' == $content_type )
            $phpmailer->isHTML( true );

        // If we don't have a charset from the input headers
        if ( !isset( $charset ) )
            $charset = get_bloginfo( 'charset' );

        // Set the content-type and charset

        /**
         * Filters the default wp_mail() charset.
         *
         * @since 2.3.0
         *
         * @param string $charset Default email charset.
         */
        $phpmailer->CharSet = apply_filters( 'wp_mail_charset', $charset );

        // Set custom headers
        if ( !empty( $headers ) ) {
            foreach ( (array) $headers as $name => $content ) {
                $phpmailer->addCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
            }

            if ( false !== stripos( $content_type, 'multipart' ) && ! empty($boundary) )
                $phpmailer->addCustomHeader( sprintf( "Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary ) );
        }

        if ( !empty( $attachments ) ) {
            foreach ( $attachments as $attachment ) {
                try {
                    $phpmailer->addAttachment($attachment);
                } catch ( phpmailerException $e ) {
                    continue;
                }
            }
        }

        /**
         * Fires after PHPMailer is initialized.
         *
         * @since 2.2.0
         *
         * @param PHPMailer $phpmailer The PHPMailer instance (passed by reference).
         */
        do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );

        // Send!
        try {
            return $phpmailer->send();
        } catch ( phpmailerException $e ) {

            $mail_error_data = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

            /**
             * Fires after a phpmailerException is caught.
             *
             * @since 4.4.0
             *
             * @param WP_Error $error A WP_Error object with the phpmailerException message, and an array
             *                        containing the mail recipient, subject, message, headers, and attachments.
             */
            do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_error_data ) );

            return false;
        }
    }

}
