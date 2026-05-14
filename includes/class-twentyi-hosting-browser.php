<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TwentyI_Hosting_Browser {

    const OPTION_API_KEY                = 'twentyi_hosting_browser_api_key';
    const OPTION_BRAND_NAME             = 'twentyi_hosting_browser_brand_name';
    const OPTION_STACKCP_LOGIN_URL      = 'twentyi_hosting_browser_stackcp_login_url';
    const OPTION_SUPPORT_EMAIL          = 'twentyi_hosting_browser_support_email';
    const OPTION_FRONTEND_ENABLED       = 'twentyi_hosting_browser_frontend_enabled';
    const OPTION_FRONTEND_REQUIRE_LOGIN = 'twentyi_hosting_browser_frontend_require_login';
    const OPTION_FRONTEND_MODE                  = 'twentyi_hosting_browser_frontend_mode';
    const OPTION_FRONTEND_PACKAGE_TYPE          = 'twentyi_hosting_browser_frontend_package_type';
    const OPTION_FRONTEND_PACKAGE_SELECT        = 'twentyi_hosting_browser_frontend_package_select';
    const OPTION_FRONTEND_ALLOWED_PACKAGE_TYPES = 'twentyi_hosting_browser_frontend_allowed_package_types';
    const OPTION_FRONTEND_SUCCESS               = 'twentyi_hosting_browser_frontend_success_message';
    const OPTION_BOOTSTRAP_ENABLED              = 'twentyi_hosting_browser_bootstrap_enabled';
    const OPTION_BOOTSTRAP_SECRET               = 'twentyi_hosting_browser_bootstrap_secret';
    const OPTION_BOOTSTRAP_ROLE                 = 'twentyi_hosting_browser_bootstrap_role';
    const OPTION_BOOTSTRAP_SEND_RESET           = 'twentyi_hosting_browser_bootstrap_send_reset';
    const OPTION_BOOTSTRAP_MAX_ATTEMPTS         = 'twentyi_hosting_browser_bootstrap_max_attempts';
    const OPTION_BOOTSTRAP_RETRY_DELAY          = 'twentyi_hosting_browser_bootstrap_retry_delay';
    const OPTION_BOOTSTRAP_USE_TEMP_URL         = 'twentyi_hosting_browser_bootstrap_use_temp_url';
    const OPTION_BOOTSTRAP_TEMP_URL_DOMAIN      = 'twentyi_hosting_browser_bootstrap_temp_url_domain';
    const OPTION_WELCOME_EMAIL_ENABLED          = 'twentyi_hosting_browser_welcome_email_enabled';
    const OPTION_WELCOME_EMAIL_SUBJECT          = 'twentyi_hosting_browser_welcome_email_subject';
    const OPTION_WELCOME_EMAIL_BODY             = 'twentyi_hosting_browser_welcome_email_body';
    const OPTION_WELCOME_EMAIL_ADMIN_COPY       = 'twentyi_hosting_browser_welcome_email_admin_copy';
    const OPTION_WELCOME_EMAIL_PASSWORD_MODE    = 'twentyi_hosting_browser_welcome_email_password_mode';
    const OPTION_CLIENT_DASHBOARD_ENABLED       = 'twentyi_hosting_browser_client_dashboard_enabled';
    const OPTION_CLIENT_DASHBOARD_ALLOW_RESEND  = 'twentyi_hosting_browser_client_dashboard_allow_resend';
    const OPTION_CLIENT_DASHBOARD_ALLOW_MAILBOX = 'twentyi_hosting_browser_client_dashboard_allow_mailbox';
    const OPTION_CLIENT_DASHBOARD_MAILBOX_LIMIT = 'twentyi_hosting_browser_client_dashboard_mailbox_limit';
    const OPTION_WC_BILLING_SYNC_ENABLED       = 'twentyi_hosting_browser_wc_billing_sync_enabled';
    const OPTION_WC_BILLING_BLOCK_ACTIONS      = 'twentyi_hosting_browser_wc_billing_block_actions';
    const OPTION_ACTIVITY_LOG           = 'twentyi_hosting_browser_activity_log';
    const CACHE_PACKAGES                = 'twentyi_hosting_browser_packages';
    const CACHE_PACKAGE_TYPES           = 'twentyi_hosting_browser_package_types';
    const CACHE_STACK_USERS             = 'twentyi_hosting_browser_stack_users';
    const CACHE_PACKAGE_DETAIL_PREFIX   = 'twentyi_hosting_browser_package_';
    const CACHE_PACKAGES_TTL            = 300;
    const MENU_SLUG                     = 'twentyi-hosting-browser';
    const CREATE_SLUG                   = 'twentyi-hosting-browser-create';
    const PACKAGE_SLUG                  = 'twentyi-hosting-browser-package';
    const STACK_USERS_SLUG              = 'twentyi-hosting-browser-stack-users';
    const REQUESTS_SLUG                 = 'twentyi-hosting-browser-requests';
    const ACTIVITY_LOG_SLUG             = 'twentyi-hosting-browser-activity-log';
    const SETTINGS_SLUG                 = 'twentyi-hosting-browser-settings';
    const REQUEST_POST_TYPE             = 'twentyi_site_request';
    const CRON_BOOTSTRAP_HOOK           = 'twentyi_hosting_browser_process_admin_bootstrap';

    /**
     * @var self|null
     */
    protected static $instance = null;

    /**
     * Singleton.
     *
     * @return self
     */
    public static function instance() {
        if ( null === static::$instance ) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Bootstrap hooks.
     */
    private function __construct() {
        add_action( 'init', [ $this, 'register_request_post_type' ] );
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_shortcode( 'twentyi_website_request', [ $this, 'render_frontend_request_shortcode' ] );
        add_shortcode( 'twentyi_client_dashboard', [ $this, 'render_client_dashboard_shortcode' ] );
        add_action( 'admin_post_twentyi_hosting_browser_refresh', [ $this, 'handle_refresh' ] );
        add_action( 'admin_post_twentyi_hosting_browser_create_package', [ $this, 'handle_create_package' ] );
        add_action( 'admin_post_twentyi_hosting_browser_package_action', [ $this, 'handle_package_action' ] );
        add_action( 'admin_post_twentyi_hosting_browser_create_stack_user', [ $this, 'handle_create_stack_user' ] );
        add_action( 'admin_post_twentyi_hosting_browser_frontend_request', [ $this, 'handle_frontend_request' ] );
        add_action( 'admin_post_nopriv_twentyi_hosting_browser_frontend_request', [ $this, 'handle_frontend_request' ] );
        add_action( 'admin_post_twentyi_hosting_browser_request_action', [ $this, 'handle_website_request_action' ] );
        add_action( 'admin_post_twentyi_hosting_browser_clear_activity_log', [ $this, 'handle_clear_activity_log' ] );
        add_action( 'admin_post_twentyi_hosting_browser_client_dashboard_action', [ $this, 'handle_client_dashboard_action' ] );
        add_action( 'admin_post_twentyi_hosting_browser_wc_order_action', [ $this, 'handle_woocommerce_order_action' ] );
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'render_woocommerce_product_package_fields' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_woocommerce_product_package_fields' ] );
        add_action( 'woocommerce_after_order_notes', [ $this, 'render_woocommerce_checkout_fields' ] );
        add_action( 'woocommerce_checkout_process', [ $this, 'validate_woocommerce_checkout_fields' ] );
        add_action( 'woocommerce_checkout_update_order_meta', [ $this, 'save_woocommerce_checkout_fields' ] );
        add_action( 'woocommerce_payment_complete', [ $this, 'maybe_provision_woocommerce_order' ] );
        add_action( 'woocommerce_order_status_processing', [ $this, 'maybe_provision_woocommerce_order' ] );
        add_action( 'woocommerce_order_status_completed', [ $this, 'maybe_provision_woocommerce_order' ] );
        add_action( 'woocommerce_order_status_changed', [ $this, 'handle_woocommerce_order_status_changed' ], 10, 4 );
        add_action( 'woocommerce_subscription_status_updated', [ $this, 'handle_woocommerce_subscription_status_updated' ], 10, 3 );
        add_action( 'add_meta_boxes', [ $this, 'register_woocommerce_order_metabox' ] );
        add_action( self::CRON_BOOTSTRAP_HOOK, [ $this, 'process_wordpress_admin_bootstrap_job' ], 10, 1 );
    }

    /**
     * Register menu pages.
     */
    public function register_admin_menu() {
        add_menu_page(
            __( '20i Hosting', 'twentyi-hosting-browser' ),
            __( '20i Hosting', 'twentyi-hosting-browser' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'render_websites_page' ],
            'dashicons-admin-site-alt3',
            58
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Websites', 'twentyi-hosting-browser' ),
            __( 'Websites', 'twentyi-hosting-browser' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'render_websites_page' ]
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Add Website', 'twentyi-hosting-browser' ),
            __( 'Add Website', 'twentyi-hosting-browser' ),
            'manage_options',
            self::CREATE_SLUG,
            [ $this, 'render_create_package_page' ]
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Package Details', 'twentyi-hosting-browser' ),
            __( 'Package Details', 'twentyi-hosting-browser' ),
            'manage_options',
            self::PACKAGE_SLUG,
            [ $this, 'render_package_page' ]
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'StackCP Users', 'twentyi-hosting-browser' ),
            __( 'StackCP Users', 'twentyi-hosting-browser' ),
            'manage_options',
            self::STACK_USERS_SLUG,
            [ $this, 'render_stack_users_page' ]
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Website Requests', 'twentyi-hosting-browser' ),
            __( 'Website Requests', 'twentyi-hosting-browser' ),
            'manage_options',
            self::REQUESTS_SLUG,
            [ $this, 'render_website_requests_page' ]
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Activity Log', 'twentyi-hosting-browser' ),
            __( 'Activity Log', 'twentyi-hosting-browser' ),
            'manage_options',
            self::ACTIVITY_LOG_SLUG,
            [ $this, 'render_activity_log_page' ]
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Settings', 'twentyi-hosting-browser' ),
            __( 'Settings', 'twentyi-hosting-browser' ),
            'manage_options',
            self::SETTINGS_SLUG,
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Register settings.
     */
    public function register_settings() {
        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_API_KEY,
            [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize_api_key' ],
                'default'           => '',
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_BRAND_NAME,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_STACKCP_LOGIN_URL,
            [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize_login_url' ],
                'default'           => '',
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_SUPPORT_EMAIL,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_email',
                'default'           => '',
            ]
        );


        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_FRONTEND_ENABLED,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 0,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_FRONTEND_REQUIRE_LOGIN,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 0,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_FRONTEND_MODE,
            [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize_frontend_mode' ],
                'default'           => 'review',
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_FRONTEND_PACKAGE_TYPE,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_FRONTEND_PACKAGE_SELECT,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 0,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_FRONTEND_ALLOWED_PACKAGE_TYPES,
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_frontend_allowed_package_types' ],
                'default'           => [],
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_FRONTEND_SUCCESS,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'default'           => '',
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_BOOTSTRAP_ENABLED,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 0,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_BOOTSTRAP_SECRET,
            [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize_bootstrap_secret' ],
                'default'           => '',
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_BOOTSTRAP_ROLE,
            [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize_bootstrap_role' ],
                'default'           => 'administrator',
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_BOOTSTRAP_SEND_RESET,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 1,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_BOOTSTRAP_MAX_ATTEMPTS,
            [
                'type'              => 'integer',
                'sanitize_callback' => [ $this, 'sanitize_positive_int' ],
                'default'           => 12,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_BOOTSTRAP_RETRY_DELAY,
            [
                'type'              => 'integer',
                'sanitize_callback' => [ $this, 'sanitize_positive_int' ],
                'default'           => 180,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_BOOTSTRAP_USE_TEMP_URL,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 1,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_BOOTSTRAP_TEMP_URL_DOMAIN,
            [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize_bootstrap_temp_url_domain' ],
                'default'           => 'stackstaging.com',
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_WELCOME_EMAIL_ENABLED,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 1,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_WELCOME_EMAIL_SUBJECT,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_WELCOME_EMAIL_BODY,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'default'           => '',
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_WELCOME_EMAIL_ADMIN_COPY,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 0,
            ]
        );


        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_WELCOME_EMAIL_PASSWORD_MODE,
            [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize_welcome_email_password_mode' ],
                'default'           => 'reset_link',
            ]
        );


        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_CLIENT_DASHBOARD_ENABLED,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 1,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_CLIENT_DASHBOARD_ALLOW_RESEND,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 1,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_CLIENT_DASHBOARD_ALLOW_MAILBOX,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 1,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_CLIENT_DASHBOARD_MAILBOX_LIMIT,
            [
                'type'              => 'integer',
                'sanitize_callback' => [ $this, 'sanitize_positive_int' ],
                'default'           => 3,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_WC_BILLING_SYNC_ENABLED,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 1,
            ]
        );

        register_setting(
            'twentyi_hosting_browser_settings_group',
            self::OPTION_WC_BILLING_BLOCK_ACTIONS,
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 1,
            ]
        );

        add_settings_section(
            'twentyi_hosting_browser_main_section',
            __( '20i API Settings', 'twentyi-hosting-browser' ),
            function () {
                echo '<p>' . esc_html__( 'Add your 20i general API key here. The plugin will base64-encode it before sending it as a Bearer token.', 'twentyi-hosting-browser' ) . '</p>';
            },
            self::SETTINGS_SLUG
        );

        add_settings_field(
            self::OPTION_API_KEY,
            __( '20i API Key', 'twentyi-hosting-browser' ),
            [ $this, 'render_api_key_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_main_section'
        );

        add_settings_field(
            self::OPTION_BRAND_NAME,
            __( 'Brand Name', 'twentyi-hosting-browser' ),
            [ $this, 'render_brand_name_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_main_section'
        );

        add_settings_field(
            self::OPTION_STACKCP_LOGIN_URL,
            __( 'StackCP Login URL', 'twentyi-hosting-browser' ),
            [ $this, 'render_stackcp_login_url_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_main_section'
        );

        add_settings_field(
            self::OPTION_SUPPORT_EMAIL,
            __( 'Support Email', 'twentyi-hosting-browser' ),
            [ $this, 'render_support_email_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_main_section'
        );


        add_settings_section(
            'twentyi_hosting_browser_frontend_section',
            __( 'Frontend Website Request Form', 'twentyi-hosting-browser' ),
            function () {
                echo '<p>' . esc_html__( 'Use the shortcode [twentyi_website_request] on a page to let clients submit website requests from the frontend.', 'twentyi-hosting-browser' ) . '</p>';
            },
            self::SETTINGS_SLUG
        );

        add_settings_field(
            self::OPTION_FRONTEND_ENABLED,
            __( 'Enable Frontend Form', 'twentyi-hosting-browser' ),
            [ $this, 'render_frontend_enabled_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_frontend_section'
        );

        add_settings_field(
            self::OPTION_FRONTEND_REQUIRE_LOGIN,
            __( 'Require Login', 'twentyi-hosting-browser' ),
            [ $this, 'render_frontend_require_login_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_frontend_section'
        );

        add_settings_field(
            self::OPTION_FRONTEND_MODE,
            __( 'Provisioning Mode', 'twentyi-hosting-browser' ),
            [ $this, 'render_frontend_mode_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_frontend_section'
        );

        add_settings_field(
            self::OPTION_FRONTEND_PACKAGE_TYPE,
            __( 'Default Package Type', 'twentyi-hosting-browser' ),
            [ $this, 'render_frontend_package_type_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_frontend_section'
        );

        add_settings_field(
            self::OPTION_FRONTEND_PACKAGE_SELECT,
            __( 'Client Package Selection', 'twentyi-hosting-browser' ),
            [ $this, 'render_frontend_package_select_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_frontend_section'
        );

        add_settings_field(
            self::OPTION_FRONTEND_ALLOWED_PACKAGE_TYPES,
            __( 'Frontend Package Options', 'twentyi-hosting-browser' ),
            [ $this, 'render_frontend_allowed_package_types_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_frontend_section'
        );

        add_settings_field(
            self::OPTION_FRONTEND_SUCCESS,
            __( 'Success Message', 'twentyi-hosting-browser' ),
            [ $this, 'render_frontend_success_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_frontend_section'
        );

        add_settings_section(
            'twentyi_hosting_browser_bootstrap_section',
            __( 'Blueprint Admin Bootstrap', 'twentyi-hosting-browser' ),
            function () {
                echo '<p>' . esc_html__( 'After a frontend request creates a WordPress Blueprint package, the plugin can poll the cloned site and ask the Blueprint Bootstrap Helper plugin to create the client as a WordPress admin.', 'twentyi-hosting-browser' ) . '</p>';
            },
            self::SETTINGS_SLUG
        );

        add_settings_field(
            self::OPTION_BOOTSTRAP_ENABLED,
            __( 'Enable Admin Bootstrap', 'twentyi-hosting-browser' ),
            [ $this, 'render_bootstrap_enabled_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_bootstrap_section'
        );

        add_settings_field(
            self::OPTION_BOOTSTRAP_SECRET,
            __( 'Bootstrap Secret', 'twentyi-hosting-browser' ),
            [ $this, 'render_bootstrap_secret_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_bootstrap_section'
        );

        add_settings_field(
            self::OPTION_BOOTSTRAP_ROLE,
            __( 'Created User Role', 'twentyi-hosting-browser' ),
            [ $this, 'render_bootstrap_role_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_bootstrap_section'
        );

        add_settings_field(
            self::OPTION_BOOTSTRAP_SEND_RESET,
            __( 'Send Login Email', 'twentyi-hosting-browser' ),
            [ $this, 'render_bootstrap_send_reset_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_bootstrap_section'
        );

        add_settings_field(
            self::OPTION_BOOTSTRAP_MAX_ATTEMPTS,
            __( 'Maximum Attempts', 'twentyi-hosting-browser' ),
            [ $this, 'render_bootstrap_max_attempts_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_bootstrap_section'
        );

        add_settings_field(
            self::OPTION_BOOTSTRAP_RETRY_DELAY,
            __( 'Retry Delay', 'twentyi-hosting-browser' ),
            [ $this, 'render_bootstrap_retry_delay_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_bootstrap_section'
        );

        add_settings_field(
            self::OPTION_BOOTSTRAP_USE_TEMP_URL,
            __( 'Use Temporary URL First', 'twentyi-hosting-browser' ),
            [ $this, 'render_bootstrap_use_temp_url_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_bootstrap_section'
        );

        add_settings_field(
            self::OPTION_BOOTSTRAP_TEMP_URL_DOMAIN,
            __( 'Temporary URL Domain', 'twentyi-hosting-browser' ),
            [ $this, 'render_bootstrap_temp_url_domain_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_bootstrap_section'
        );

        add_settings_section(
            'twentyi_hosting_browser_welcome_email_section',
            __( 'Client Welcome Email', 'twentyi-hosting-browser' ),
            function () {
                echo '<p>' . esc_html__( 'Send a polished client welcome email after the WordPress admin user has been created on the cloned Blueprint site.', 'twentyi-hosting-browser' ) . '</p>';
            },
            self::SETTINGS_SLUG
        );

        add_settings_field(
            self::OPTION_WELCOME_EMAIL_ENABLED,
            __( 'Enable Welcome Email', 'twentyi-hosting-browser' ),
            [ $this, 'render_welcome_email_enabled_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_welcome_email_section'
        );

        add_settings_field(
            self::OPTION_WELCOME_EMAIL_SUBJECT,
            __( 'Email Subject', 'twentyi-hosting-browser' ),
            [ $this, 'render_welcome_email_subject_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_welcome_email_section'
        );

        add_settings_field(
            self::OPTION_WELCOME_EMAIL_BODY,
            __( 'Email Body', 'twentyi-hosting-browser' ),
            [ $this, 'render_welcome_email_body_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_welcome_email_section'
        );

        add_settings_field(
            self::OPTION_WELCOME_EMAIL_ADMIN_COPY,
            __( 'Admin Copy', 'twentyi-hosting-browser' ),
            [ $this, 'render_welcome_email_admin_copy_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_welcome_email_section'
        );


        add_settings_field(
            self::OPTION_WELCOME_EMAIL_PASSWORD_MODE,
            __( 'Password in Welcome Email', 'twentyi-hosting-browser' ),
            [ $this, 'render_welcome_email_password_mode_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_welcome_email_section'
        );


        add_settings_section(
            'twentyi_hosting_browser_client_dashboard_section',
            __( 'Frontend Client Dashboard', 'twentyi-hosting-browser' ),
            function () {
                echo '<p>' . esc_html__( 'Use the shortcode [twentyi_client_dashboard] to let logged-in clients view the website information linked to their email address.', 'twentyi-hosting-browser' ) . '</p>';
            },
            self::SETTINGS_SLUG
        );

        add_settings_field(
            self::OPTION_CLIENT_DASHBOARD_ENABLED,
            __( 'Enable Client Dashboard', 'twentyi-hosting-browser' ),
            [ $this, 'render_client_dashboard_enabled_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_client_dashboard_section'
        );

        add_settings_field(
            self::OPTION_CLIENT_DASHBOARD_ALLOW_RESEND,
            __( 'Client Resend Welcome Email', 'twentyi-hosting-browser' ),
            [ $this, 'render_client_dashboard_resend_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_client_dashboard_section'
        );


        add_settings_field(
            self::OPTION_CLIENT_DASHBOARD_ALLOW_MAILBOX,
            __( 'Client Mailbox Creation', 'twentyi-hosting-browser' ),
            [ $this, 'render_client_dashboard_mailbox_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_client_dashboard_section'
        );

        add_settings_field(
            self::OPTION_CLIENT_DASHBOARD_MAILBOX_LIMIT,
            __( 'Client Mailbox Limit', 'twentyi-hosting-browser' ),
            [ $this, 'render_client_dashboard_mailbox_limit_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_client_dashboard_section'
        );

        add_settings_section(
            'twentyi_hosting_browser_wc_billing_section',
            __( 'WooCommerce Billing Status Sync', 'twentyi-hosting-browser' ),
            function () {
                echo '<p>' . esc_html__( 'Mirror WooCommerce order/subscription billing status onto created website requests. This is a safe status layer: it does not delete or suspend 20i hosting packages.', 'twentyi-hosting-browser' ) . '</p>';
            },
            self::SETTINGS_SLUG
        );

        add_settings_field(
            self::OPTION_WC_BILLING_SYNC_ENABLED,
            __( 'Enable Billing Sync', 'twentyi-hosting-browser' ),
            [ $this, 'render_wc_billing_sync_enabled_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_wc_billing_section'
        );

        add_settings_field(
            self::OPTION_WC_BILLING_BLOCK_ACTIONS,
            __( 'Block Client Actions', 'twentyi-hosting-browser' ),
            [ $this, 'render_wc_billing_block_actions_field' ],
            self::SETTINGS_SLUG,
            'twentyi_hosting_browser_wc_billing_section'
        );
    }

    /**
     * Sanitize API key.
     *
     * @param string $value Raw value.
     * @return string
     */
    public function sanitize_api_key( $value ) {
        return trim( (string) $value );
    }

    /**
     * Sanitize StackCP login URL.
     *
     * @param string $value Raw value.
     * @return string
     */
    public function sanitize_login_url( $value ) {
        $value = trim( (string) $value );

        if ( '' === $value ) {
            return '';
        }

        return esc_url_raw( $value );
    }

    /**
     * Sanitize a checkbox setting.
     *
     * @param mixed $value Raw value.
     * @return int
     */
    public function sanitize_checkbox( $value ) {
        return ! empty( $value ) ? 1 : 0;
    }

    /**
     * Sanitize bootstrap shared secret.
     *
     * @param string $value Raw value.
     * @return string
     */
    public function sanitize_bootstrap_secret( $value ) {
        $value = trim( (string) $value );
        $value = preg_replace( '/[^A-Za-z0-9_\-:.]/', '', $value );

        return (string) $value;
    }

    /**
     * Sanitize the 20i temporary URL base domain.
     *
     * @param string $value Raw value.
     * @return string
     */
    public function sanitize_bootstrap_temp_url_domain( $value ) {
        $domain = $this->sanitize_domain( $value );

        return '' !== $domain ? $domain : 'stackstaging.com';
    }

    /**
     * Sanitize bootstrap role.
     *
     * @param string $value Raw value.
     * @return string
     */
    public function sanitize_bootstrap_role( $value ) {
        $value   = sanitize_key( (string) $value );
        $allowed = [ 'administrator', 'editor', 'shop_manager' ];

        return in_array( $value, $allowed, true ) ? $value : 'administrator';
    }

    /**
     * Sanitize a positive integer setting.
     *
     * @param mixed $value Raw value.
     * @return int
     */
    public function sanitize_positive_int( $value ) {
        return max( 1, absint( $value ) );
    }

    /**
     * Sanitize frontend provisioning mode.
     *
     * @param string $value Raw value.
     * @return string
     */
    public function sanitize_frontend_mode( $value ) {
        $value = sanitize_key( (string) $value );

        return in_array( $value, [ 'review', 'auto' ], true ) ? $value : 'review';
    }


    /**
     * Sanitize welcome email password mode.
     *
     * @param string $value Raw value.
     * @return string
     */
    public function sanitize_welcome_email_password_mode( $value ) {
        $value = sanitize_key( (string) $value );

        return in_array( $value, [ 'reset_link', 'temporary_password' ], true ) ? $value : 'reset_link';
    }

    /**
     * Sanitize frontend allowed package type ids.
     *
     * @param mixed $value Raw value.
     * @return array<int,string>
     */
    public function sanitize_frontend_allowed_package_types( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }

        $raw_types = array_map( 'sanitize_text_field', wp_unslash( $value ) );
        $types     = [];

        foreach ( $raw_types as $raw_type ) {
            foreach ( explode( ',', (string) $raw_type ) as $type ) {
                $type = trim( $type );
                if ( '' !== $type ) {
                    $types[] = $type;
                }
            }
        }

        return array_values( array_unique( $types ) );
    }

    /**
     * Register private request post type.
     */
    public function register_request_post_type() {
        register_post_type(
            self::REQUEST_POST_TYPE,
            [
                'labels'              => [
                    'name'          => __( 'Website Requests', 'twentyi-hosting-browser' ),
                    'singular_name' => __( 'Website Request', 'twentyi-hosting-browser' ),
                ],
                'public'              => false,
                'show_ui'             => false,
                'show_in_menu'        => false,
                'capability_type'     => 'post',
                'supports'            => [ 'title' ],
                'exclude_from_search' => true,
                'rewrite'             => false,
                'query_var'           => false,
            ]
        );
    }

    /**
     * Render API key field.
     */
    public function render_api_key_field() {
        $value = $this->get_api_key();
        ?>
        <input
            type="password"
            name="<?php echo esc_attr( self::OPTION_API_KEY ); ?>"
            id="<?php echo esc_attr( self::OPTION_API_KEY ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            class="regular-text"
            autocomplete="off"
        />
        <p class="description">
            <?php esc_html_e( 'Paste your general API key from My20i. Do not paste the base64-encoded Bearer value.', 'twentyi-hosting-browser' ); ?>
        </p>
        <?php
    }

    /**
     * Render brand field.
     */
    public function render_brand_name_field() {
        ?>
        <input
            type="text"
            name="<?php echo esc_attr( self::OPTION_BRAND_NAME ); ?>"
            id="<?php echo esc_attr( self::OPTION_BRAND_NAME ); ?>"
            value="<?php echo esc_attr( $this->get_brand_name() ); ?>"
            class="regular-text"
        />
        <p class="description">
            <?php esc_html_e( 'Used in the client handoff templates. Defaults to your WordPress site name if left blank.', 'twentyi-hosting-browser' ); ?>
        </p>
        <?php
    }

    /**
     * Render StackCP login URL field.
     */
    public function render_stackcp_login_url_field() {
        ?>
        <input
            type="url"
            name="<?php echo esc_attr( self::OPTION_STACKCP_LOGIN_URL ); ?>"
            id="<?php echo esc_attr( self::OPTION_STACKCP_LOGIN_URL ); ?>"
            value="<?php echo esc_attr( $this->get_stackcp_login_url() ); ?>"
            class="regular-text code"
            placeholder="https://stackcp.com"
        />
        <p class="description">
            <?php esc_html_e( 'Optional custom branded StackCP login URL. Defaults to https://stackcp.com if left blank.', 'twentyi-hosting-browser' ); ?>
        </p>
        <?php
    }

    /**
     * Render support email field.
     */
    public function render_support_email_field() {
        ?>
        <input
            type="email"
            name="<?php echo esc_attr( self::OPTION_SUPPORT_EMAIL ); ?>"
            id="<?php echo esc_attr( self::OPTION_SUPPORT_EMAIL ); ?>"
            value="<?php echo esc_attr( $this->get_support_email() ); ?>"
            class="regular-text"
        />
        <p class="description">
            <?php esc_html_e( 'Optional support email shown in client handoff details.', 'twentyi-hosting-browser' ); ?>
        </p>
        <?php
    }

    /**
     * Render frontend enabled field.
     */
    public function render_frontend_enabled_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_FRONTEND_ENABLED ); ?>" value="1" <?php checked( $this->is_frontend_form_enabled() ); ?> />
            <?php esc_html_e( 'Allow the public shortcode form to accept website requests.', 'twentyi-hosting-browser' ); ?>
        </label>
        <p class="description"><code>[twentyi_website_request]</code></p>
        <?php
    }

    /**
     * Render frontend login requirement field.
     */
    public function render_frontend_require_login_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_FRONTEND_REQUIRE_LOGIN ); ?>" value="1" <?php checked( $this->frontend_requires_login() ); ?> />
            <?php esc_html_e( 'Only logged-in users can submit the form.', 'twentyi-hosting-browser' ); ?>
        </label>
        <?php
    }

    /**
     * Render frontend mode field.
     */
    public function render_frontend_mode_field() {
        $mode = $this->get_frontend_mode();
        ?>
        <select name="<?php echo esc_attr( self::OPTION_FRONTEND_MODE ); ?>" id="<?php echo esc_attr( self::OPTION_FRONTEND_MODE ); ?>">
            <option value="review" <?php selected( $mode, 'review' ); ?>><?php esc_html_e( 'Save for admin review', 'twentyi-hosting-browser' ); ?></option>
            <option value="auto" <?php selected( $mode, 'auto' ); ?>><?php esc_html_e( 'Auto-create hosting package', 'twentyi-hosting-browser' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Review mode is recommended. Auto-create should only be used on a protected/client-only page.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render frontend package type field.
     */
    public function render_frontend_package_type_field() {
        $value         = $this->get_frontend_package_type();
        $package_types = $this->get_package_types();
        ?>
        <?php if ( ! is_wp_error( $package_types ) && ! empty( $package_types ) ) : ?>
            <select name="<?php echo esc_attr( self::OPTION_FRONTEND_PACKAGE_TYPE ); ?>" id="<?php echo esc_attr( self::OPTION_FRONTEND_PACKAGE_TYPE ); ?>">
                <option value=""><?php esc_html_e( 'Choose on approval / no default', 'twentyi-hosting-browser' ); ?></option>
                <?php foreach ( $package_types as $package_type ) : ?>
                    <option value="<?php echo esc_attr( $package_type['id'] ); ?>" <?php selected( $value, $package_type['id'] ); ?>><?php echo esc_html( $package_type['label'] ); ?></option>
                <?php endforeach; ?>
            </select>
        <?php else : ?>
            <input type="text" name="<?php echo esc_attr( self::OPTION_FRONTEND_PACKAGE_TYPE ); ?>" id="<?php echo esc_attr( self::OPTION_FRONTEND_PACKAGE_TYPE ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <?php endif; ?>
        <p class="description"><?php esc_html_e( 'Used as the pre-selected frontend package and as the fallback when client package selection is disabled.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render frontend package selection toggle field.
     */
    public function render_frontend_package_select_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_FRONTEND_PACKAGE_SELECT ); ?>" value="1" <?php checked( $this->frontend_package_selection_enabled() ); ?> />
            <?php esc_html_e( 'Allow clients to choose a package type on the frontend website request form.', 'twentyi-hosting-browser' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'When enabled, the submitted package choice is saved with the request and used for approval or auto-create mode.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render frontend allowed package types field.
     */
    public function render_frontend_allowed_package_types_field() {
        $selected      = $this->get_frontend_allowed_package_types();
        $package_types = $this->get_package_types();
        ?>
        <input type="hidden" name="<?php echo esc_attr( self::OPTION_FRONTEND_ALLOWED_PACKAGE_TYPES ); ?>[]" value="" />
        <?php if ( ! is_wp_error( $package_types ) && ! empty( $package_types ) ) : ?>
            <select name="<?php echo esc_attr( self::OPTION_FRONTEND_ALLOWED_PACKAGE_TYPES ); ?>[]" id="<?php echo esc_attr( self::OPTION_FRONTEND_ALLOWED_PACKAGE_TYPES ); ?>" multiple size="8" style="min-width:340px;">
                <?php foreach ( $package_types as $package_type ) : ?>
                    <option value="<?php echo esc_attr( $package_type['id'] ); ?>" <?php selected( in_array( $package_type['id'], $selected, true ) ); ?>><?php echo esc_html( $package_type['label'] ); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e( 'Choose which 20i package types clients can select. Leave all unselected to show every available package type.', 'twentyi-hosting-browser' ); ?></p>
        <?php else : ?>
            <input type="text" name="<?php echo esc_attr( self::OPTION_FRONTEND_ALLOWED_PACKAGE_TYPES ); ?>[]" id="<?php echo esc_attr( self::OPTION_FRONTEND_ALLOWED_PACKAGE_TYPES ); ?>" value="<?php echo esc_attr( implode( ',', $selected ) ); ?>" class="regular-text" />
            <p class="description"><?php esc_html_e( 'Package types could not be loaded. Add one or more package type ids, separated by commas.', 'twentyi-hosting-browser' ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Render frontend success message field.
     */
    public function render_frontend_success_field() {
        ?>
        <textarea name="<?php echo esc_attr( self::OPTION_FRONTEND_SUCCESS ); ?>" id="<?php echo esc_attr( self::OPTION_FRONTEND_SUCCESS ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $this->get_frontend_success_message() ); ?></textarea>
        <p class="description"><?php esc_html_e( 'Shown after a client submits the website request form.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render bootstrap enabled field.
     */
    public function render_bootstrap_enabled_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_BOOTSTRAP_ENABLED ); ?>" value="1" <?php checked( $this->bootstrap_enabled() ); ?> />
            <?php esc_html_e( 'Create the frontend requester as a WordPress user after the Blueprint clone responds.', 'twentyi-hosting-browser' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'Install the companion Blueprint Bootstrap Helper plugin inside your WordPress Blueprint and use the same secret below.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render bootstrap secret field.
     */
    public function render_bootstrap_secret_field() {
        $secret = $this->get_bootstrap_secret();
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_BOOTSTRAP_SECRET ); ?>" id="<?php echo esc_attr( self::OPTION_BOOTSTRAP_SECRET ); ?>" value="<?php echo esc_attr( $secret ); ?>" class="large-text code" autocomplete="off" />
        <p class="description"><?php esc_html_e( 'Copy this exact secret into the Blueprint Bootstrap Helper plugin on your Blueprint site before cloning. Keep it private.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render bootstrap role field.
     */
    public function render_bootstrap_role_field() {
        $role = $this->get_bootstrap_role();
        ?>
        <select name="<?php echo esc_attr( self::OPTION_BOOTSTRAP_ROLE ); ?>" id="<?php echo esc_attr( self::OPTION_BOOTSTRAP_ROLE ); ?>">
            <option value="administrator" <?php selected( $role, 'administrator' ); ?>><?php esc_html_e( 'Administrator', 'twentyi-hosting-browser' ); ?></option>
            <option value="editor" <?php selected( $role, 'editor' ); ?>><?php esc_html_e( 'Editor', 'twentyi-hosting-browser' ); ?></option>
            <option value="shop_manager" <?php selected( $role, 'shop_manager' ); ?>><?php esc_html_e( 'Shop Manager', 'twentyi-hosting-browser' ); ?></option>
        </select>
        <?php
    }

    /**
     * Render bootstrap reset email field.
     */
    public function render_bootstrap_send_reset_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_BOOTSTRAP_SEND_RESET ); ?>" value="1" <?php checked( $this->bootstrap_sends_reset_email() ); ?> />
            <?php esc_html_e( 'Ask the cloned site to email the client a password setup/login link.', 'twentyi-hosting-browser' ); ?>
        </label>
        <?php
    }

    /**
     * Render bootstrap max attempts field.
     */
    public function render_bootstrap_max_attempts_field() {
        ?>
        <input type="number" min="1" max="50" name="<?php echo esc_attr( self::OPTION_BOOTSTRAP_MAX_ATTEMPTS ); ?>" id="<?php echo esc_attr( self::OPTION_BOOTSTRAP_MAX_ATTEMPTS ); ?>" value="<?php echo esc_attr( $this->get_bootstrap_max_attempts() ); ?>" class="small-text" />
        <p class="description"><?php esc_html_e( 'How many times WP-Cron should try the cloned site before marking the admin bootstrap as failed.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render bootstrap retry delay field.
     */
    public function render_bootstrap_retry_delay_field() {
        ?>
        <input type="number" min="60" max="3600" name="<?php echo esc_attr( self::OPTION_BOOTSTRAP_RETRY_DELAY ); ?>" id="<?php echo esc_attr( self::OPTION_BOOTSTRAP_RETRY_DELAY ); ?>" value="<?php echo esc_attr( $this->get_bootstrap_retry_delay() ); ?>" class="small-text" />
        <span><?php esc_html_e( 'seconds', 'twentyi-hosting-browser' ); ?></span>
        <?php
    }

    /**
     * Render temporary URL first field.
     */
    public function render_bootstrap_use_temp_url_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_BOOTSTRAP_USE_TEMP_URL ); ?>" value="1" <?php checked( $this->bootstrap_uses_temporary_url() ); ?> />
            <?php esc_html_e( 'Try the 20i temporary URL before the live domain when creating the cloned site WordPress admin.', 'twentyi-hosting-browser' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'Recommended for Blueprint packages because the StackStaging URL is usually available before DNS is pointed at the live domain.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render temporary URL domain field.
     */
    public function render_bootstrap_temp_url_domain_field() {
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_BOOTSTRAP_TEMP_URL_DOMAIN ); ?>" id="<?php echo esc_attr( self::OPTION_BOOTSTRAP_TEMP_URL_DOMAIN ); ?>" value="<?php echo esc_attr( $this->get_bootstrap_temp_url_domain() ); ?>" class="regular-text code" placeholder="stackstaging.com" />
        <p class="description">
            <?php esc_html_e( 'Default: stackstaging.com. If your 20i package type uses a branded custom temporary URL domain, enter that base domain here.', 'twentyi-hosting-browser' ); ?>
        </p>
        <?php
    }

    /**
     * Render welcome email enabled field.
     */
    public function render_welcome_email_enabled_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_WELCOME_EMAIL_ENABLED ); ?>" value="1" <?php checked( $this->welcome_email_enabled() ); ?> />
            <?php esc_html_e( 'Automatically email the client once their WordPress admin account has been created.', 'twentyi-hosting-browser' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'The Blueprint helper can still send the password setup email. This welcome email gives the client their site links and next steps.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render welcome email subject field.
     */
    public function render_welcome_email_subject_field() {
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_WELCOME_EMAIL_SUBJECT ); ?>" id="<?php echo esc_attr( self::OPTION_WELCOME_EMAIL_SUBJECT ); ?>" value="<?php echo esc_attr( $this->get_welcome_email_subject_template() ); ?>" class="large-text" />
        <p class="description"><?php esc_html_e( 'Available placeholders include {brand_name}, {business_name}, {client_name}, {domain}, {site_url}, {temporary_url}, {wp_login_url}, {client_email}, {wp_temp_password}, {password_note}, {support_email}, and {package_id}.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render welcome email body field.
     */
    public function render_welcome_email_body_field() {
        ?>
        <textarea name="<?php echo esc_attr( self::OPTION_WELCOME_EMAIL_BODY ); ?>" id="<?php echo esc_attr( self::OPTION_WELCOME_EMAIL_BODY ); ?>" rows="14" class="large-text code"><?php echo esc_textarea( $this->get_welcome_email_body_template() ); ?></textarea>
        <p class="description"><?php esc_html_e( 'Plain text email. Placeholders are replaced when the email is sent.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render welcome email admin copy field.
     */
    public function render_welcome_email_admin_copy_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_WELCOME_EMAIL_ADMIN_COPY ); ?>" value="1" <?php checked( $this->welcome_email_sends_admin_copy() ); ?> />
            <?php esc_html_e( 'BCC the site admin on client welcome emails.', 'twentyi-hosting-browser' ); ?>
        </label>
        <?php
    }


    /**
     * Render welcome email password mode field.
     */
    public function render_welcome_email_password_mode_field() {
        $mode = $this->get_welcome_email_password_mode();
        ?>
        <select name="<?php echo esc_attr( self::OPTION_WELCOME_EMAIL_PASSWORD_MODE ); ?>" id="<?php echo esc_attr( self::OPTION_WELCOME_EMAIL_PASSWORD_MODE ); ?>">
            <option value="reset_link" <?php selected( $mode, 'reset_link' ); ?>><?php esc_html_e( 'Password reset/setup link only', 'twentyi-hosting-browser' ); ?></option>
            <option value="temporary_password" <?php selected( $mode, 'temporary_password' ); ?>><?php esc_html_e( 'Include temporary password once', 'twentyi-hosting-browser' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Temporary passwords are generated by the cloned site and only used for the welcome email. They are not shown in the client dashboard or activity log.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render client dashboard enabled field.
     */
    public function render_client_dashboard_enabled_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_CLIENT_DASHBOARD_ENABLED ); ?>" value="1" <?php checked( $this->client_dashboard_enabled() ); ?> />
            <?php esc_html_e( 'Enable the frontend client website dashboard shortcode.', 'twentyi-hosting-browser' ); ?>
        </label>
        <p class="description"><code>[twentyi_client_dashboard]</code> <?php esc_html_e( 'shows logged-in clients the website requests/packages linked to their account email.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render client dashboard resend field.
     */
    public function render_client_dashboard_resend_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_CLIENT_DASHBOARD_ALLOW_RESEND ); ?>" value="1" <?php checked( $this->client_dashboard_allows_welcome_resend() ); ?> />
            <?php esc_html_e( 'Allow logged-in clients to resend the welcome email from their dashboard.', 'twentyi-hosting-browser' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'Clients can only trigger this for requests that match their own WordPress account email.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }


    /**
     * Render client dashboard mailbox toggle field.
     */
    public function render_client_dashboard_mailbox_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_CLIENT_DASHBOARD_ALLOW_MAILBOX ); ?>" value="1" <?php checked( $this->client_dashboard_allows_mailbox_creation() ); ?> />
            <?php esc_html_e( 'Allow logged-in clients to create mailboxes for their own package domain from the dashboard.', 'twentyi-hosting-browser' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'Clients can only create mailboxes for website requests linked to their account email and only after the 20i package has been created.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render client dashboard mailbox limit field.
     */
    public function render_client_dashboard_mailbox_limit_field() {
        ?>
        <input type="number" min="1" step="1" name="<?php echo esc_attr( self::OPTION_CLIENT_DASHBOARD_MAILBOX_LIMIT ); ?>" value="<?php echo esc_attr( $this->get_client_dashboard_mailbox_limit() ); ?>" class="small-text" />
        <p class="description"><?php esc_html_e( 'Maximum mailboxes each client can create per website request through the frontend dashboard. Admin-created mailboxes are not counted here.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render WooCommerce billing sync enabled field.
     */
    public function render_wc_billing_sync_enabled_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_WC_BILLING_SYNC_ENABLED ); ?>" value="1" <?php checked( $this->wc_billing_sync_enabled() ); ?> />
            <?php esc_html_e( 'Track WooCommerce order/subscription status for linked 20i website requests.', 'twentyi-hosting-browser' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'This adds billing status visibility in the admin order box, Website Requests, and the frontend client dashboard.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render WooCommerce billing action-block field.
     */
    public function render_wc_billing_block_actions_field() {
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_WC_BILLING_BLOCK_ACTIONS ); ?>" value="1" <?php checked( $this->wc_billing_blocks_client_actions() ); ?> />
            <?php esc_html_e( 'Disable client dashboard actions, such as mailbox creation, when billing/subscription status needs attention.', 'twentyi-hosting-browser' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'Safe mode only: this does not delete, suspend, or modify the 20i hosting package.', 'twentyi-hosting-browser' ); ?></p>
        <?php
    }

    /**
     * Render website requests page.
     */
    public function render_website_requests_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $notice        = isset( $_GET['request_notice'] ) ? sanitize_key( wp_unslash( $_GET['request_notice'] ) ) : '';
        $notice_text   = isset( $_GET['request_message'] ) ? sanitize_text_field( wp_unslash( $_GET['request_message'] ) ) : '';
        $package_types = $this->get_package_types();
        $requests      = get_posts(
            [
                'post_type'      => self::REQUEST_POST_TYPE,
                'post_status'    => [ 'pending', 'publish', 'draft', 'private' ],
                'posts_per_page' => 100,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]
        );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Website Requests', 'twentyi-hosting-browser' ); ?></h1>
            <p><?php esc_html_e( 'Review website requests submitted from the frontend shortcode form, then approve them to create 20i hosting packages.', 'twentyi-hosting-browser' ); ?></p>

            <?php if ( $notice_text ) : ?>
                <div class="notice notice-<?php echo 'success' === $notice ? 'success' : 'error'; ?> inline"><p><?php echo esc_html( $notice_text ); ?></p></div>
            <?php endif; ?>

            <div class="notice notice-info inline"><p>
                <?php esc_html_e( 'Frontend shortcode:', 'twentyi-hosting-browser' ); ?> <code>[twentyi_website_request]</code>
            </p></div>

            <?php if ( empty( $requests ) ) : ?>
                <p><?php esc_html_e( 'No website requests yet.', 'twentyi-hosting-browser' ); ?></p>
            <?php else : ?>
                <table class="widefat striped fixed">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Date', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Client', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Domain', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Contact', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Package', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'twentyi-hosting-browser' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $requests as $request ) : ?>
                            <?php
                            $request_id   = (int) $request->ID;
                            $business     = (string) get_post_meta( $request_id, 'business_name', true );
                            $contact_name = (string) get_post_meta( $request_id, 'contact_name', true );
                            $domain       = (string) get_post_meta( $request_id, 'domain_name', true );
                            $email        = (string) get_post_meta( $request_id, 'email', true );
                            $phone        = (string) get_post_meta( $request_id, 'phone', true );
                            $brief        = (string) get_post_meta( $request_id, 'website_brief', true );
                            $status       = (string) get_post_meta( $request_id, 'status', true );
                            $package_id   = (string) get_post_meta( $request_id, 'package_id', true );
                            $error        = (string) get_post_meta( $request_id, 'last_error', true );
                            $package_type     = (string) get_post_meta( $request_id, 'package_type', true );
                            $bootstrap_status = (string) get_post_meta( $request_id, 'wp_admin_bootstrap_status', true );
                            $bootstrap_error  = (string) get_post_meta( $request_id, 'wp_admin_bootstrap_error', true );
                            $bootstrap_attempts = (int) get_post_meta( $request_id, 'wp_admin_bootstrap_attempts', true );
                            $welcome_status = (string) get_post_meta( $request_id, 'welcome_email_status', true );
                            $welcome_error  = (string) get_post_meta( $request_id, 'welcome_email_error', true );
                            $welcome_sent   = (string) get_post_meta( $request_id, 'welcome_email_sent_at', true );
                            $status           = $status ?: 'pending';
                            $approve_url      = wp_nonce_url(
                                add_query_arg(
                                    [
                                        'action'         => 'twentyi_hosting_browser_request_action',
                                        'request_action' => 'approve',
                                        'request_id'     => $request_id,
                                    ],
                                    admin_url( 'admin-post.php' )
                                ),
                                'twentyi_hosting_browser_request_action_' . $request_id
                            );
                            $reject_url   = wp_nonce_url(
                                add_query_arg(
                                    [
                                        'action'         => 'twentyi_hosting_browser_request_action',
                                        'request_action' => 'reject',
                                        'request_id'     => $request_id,
                                    ],
                                    admin_url( 'admin-post.php' )
                                ),
                                'twentyi_hosting_browser_request_action_' . $request_id
                            );
                            $delete_url   = wp_nonce_url(
                                add_query_arg(
                                    [
                                        'action'         => 'twentyi_hosting_browser_request_action',
                                        'request_action' => 'delete',
                                        'request_id'     => $request_id,
                                    ],
                                    admin_url( 'admin-post.php' )
                                ),
                                'twentyi_hosting_browser_request_action_' . $request_id
                            );
                            $bootstrap_url = wp_nonce_url(
                                add_query_arg(
                                    [
                                        'action'         => 'twentyi_hosting_browser_request_action',
                                        'request_action' => 'bootstrap',
                                        'request_id'     => $request_id,
                                    ],
                                    admin_url( 'admin-post.php' )
                                ),
                                'twentyi_hosting_browser_request_action_' . $request_id
                            );
                            $welcome_url = wp_nonce_url(
                                add_query_arg(
                                    [
                                        'action'         => 'twentyi_hosting_browser_request_action',
                                        'request_action' => 'welcome',
                                        'request_id'     => $request_id,
                                    ],
                                    admin_url( 'admin-post.php' )
                                ),
                                'twentyi_hosting_browser_request_action_' . $request_id
                            );
                            ?>
                            <tr>
                                <td><?php echo esc_html( get_the_date( '', $request ) ); ?></td>
                                <td>
                                    <strong><?php echo esc_html( $business ?: $request->post_title ); ?></strong>
                                    <?php if ( $brief ) : ?>
                                        <details style="margin-top:6px;"><summary><?php esc_html_e( 'View brief', 'twentyi-hosting-browser' ); ?></summary><p><?php echo esc_html( $brief ); ?></p></details>
                                    <?php endif; ?>
                                </td>
                                <td><code><?php echo esc_html( $domain ); ?></code></td>
                                <td>
                                    <?php echo esc_html( $contact_name ); ?><br />
                                    <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a><br />
                                    <?php echo esc_html( $phone ); ?>
                                </td>
                                <td>
                                    <strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $status ) ) ); ?></strong>
                                    <?php if ( $error ) : ?>
                                        <br /><small><?php echo esc_html( $error ); ?></small>
                                    <?php endif; ?>
                                    <?php if ( $package_type ) : ?>
                                        <br /><small><?php esc_html_e( 'Package type:', 'twentyi-hosting-browser' ); ?> <code><?php echo esc_html( $this->get_package_type_label( $package_type ) ); ?></code></small>
                                    <?php endif; ?>
                                    <?php if ( $bootstrap_status ) : ?>
                                        <br /><small><?php esc_html_e( 'WP admin:', 'twentyi-hosting-browser' ); ?> <strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $bootstrap_status ) ) ); ?></strong><?php echo $bootstrap_attempts ? ' (' . esc_html( $bootstrap_attempts ) . ')' : ''; ?></small>
                                    <?php endif; ?>
                                    <?php if ( $bootstrap_error ) : ?>
                                        <br /><small><?php echo esc_html( $bootstrap_error ); ?></small>
                                    <?php endif; ?>
                                    <?php if ( $welcome_status ) : ?>
                                        <br /><small><?php esc_html_e( 'Welcome email:', 'twentyi-hosting-browser' ); ?> <strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $welcome_status ) ) ); ?></strong><?php echo $welcome_sent ? ' - ' . esc_html( $welcome_sent ) : ''; ?></small>
                                    <?php endif; ?>
                                    <?php if ( $welcome_error ) : ?>
                                        <br /><small><?php echo esc_html( $welcome_error ); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( $package_id ) : ?>
                                        <a class="button button-small" href="<?php echo esc_url( $this->get_package_page_url( $package_id ) ); ?>"><?php echo esc_html( $package_id ); ?></a>
                                    <?php else : ?>
                                        &ndash;
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( 'created' !== $status ) : ?>
                                        <a class="button button-primary button-small" href="<?php echo esc_url( $approve_url ); ?>"><?php esc_html_e( 'Approve & Create', 'twentyi-hosting-browser' ); ?></a>
                                    <?php endif; ?>
                                    <?php if ( $package_id && $this->bootstrap_enabled() ) : ?>
                                        <a class="button button-small" href="<?php echo esc_url( $bootstrap_url ); ?>"><?php esc_html_e( 'Retry WP Admin', 'twentyi-hosting-browser' ); ?></a>
                                    <?php endif; ?>
                                    <?php if ( $package_id ) : ?>
                                        <a class="button button-small" href="<?php echo esc_url( $welcome_url ); ?>"><?php echo 'sent' === $welcome_status ? esc_html__( 'Resend Welcome', 'twentyi-hosting-browser' ) : esc_html__( 'Send Welcome', 'twentyi-hosting-browser' ); ?></a>
                                    <?php endif; ?>
                                    <a class="button button-small" href="<?php echo esc_url( $reject_url ); ?>"><?php esc_html_e( 'Mark Rejected', 'twentyi-hosting-browser' ); ?></a>
                                    <a class="button button-small button-link-delete" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this request?', 'twentyi-hosting-browser' ) ); ?>');"><?php esc_html_e( 'Delete', 'twentyi-hosting-browser' ); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render frontend website request shortcode.
     *
     * @param array<string,string> $atts Shortcode attributes.
     * @return string
     */
    public function render_frontend_request_shortcode( $atts = [] ) {
        $atts = shortcode_atts(
            [
                'title'        => __( 'Start your website request', 'twentyi-hosting-browser' ),
                'intro'        => __( 'Tell us what you need and we will prepare your hosting setup.', 'twentyi-hosting-browser' ),
                'package_type' => '',
                'package_label' => __( 'Hosting Package', 'twentyi-hosting-browser' ),
            ],
            $atts,
            'twentyi_website_request'
        );

        if ( ! $this->is_frontend_form_enabled() && ! current_user_can( 'manage_options' ) ) {
            return '';
        }

        if ( $this->frontend_requires_login() && ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'Please log in to submit a website request.', 'twentyi-hosting-browser' ) . '</p>';
        }

        $status  = isset( $_GET['twentyi_request_status'] ) ? sanitize_key( wp_unslash( $_GET['twentyi_request_status'] ) ) : '';
        $message = '';

        if ( 'success' === $status ) {
            $message = $this->get_frontend_success_message();
        } elseif ( 'created' === $status ) {
            $message = __( 'Thanks. Your website request has been received and the hosting package has been created.', 'twentyi-hosting-browser' );
        } elseif ( 'error' === $status ) {
            $message = __( 'Sorry, something went wrong. Please check the form and try again.', 'twentyi-hosting-browser' );
        }

        $package_type          = sanitize_text_field( (string) $atts['package_type'] );
        $show_package_select   = ( '' === $package_type && $this->frontend_package_selection_enabled() );
        $frontend_package_types = $show_package_select ? $this->get_frontend_public_package_types() : [];
        $default_package_type   = $this->get_frontend_package_type();
        $hidden_package_type    = '' !== $package_type ? $package_type : $default_package_type;

        ob_start();
        ?>
        <div class="twentyi-website-request-form" style="max-width:760px;">
            <?php if ( $message ) : ?>
                <div class="twentyi-website-request-notice" style="padding:12px 16px;margin-bottom:16px;border:1px solid #d1d5db;border-radius:10px;background:#f8fafc;">
                    <?php echo esc_html( $message ); ?>
                </div>
            <?php endif; ?>

            <h2><?php echo esc_html( $atts['title'] ); ?></h2>
            <p><?php echo esc_html( $atts['intro'] ); ?></p>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="twentyi_hosting_browser_frontend_request" />
                <?php if ( $show_package_select && ! empty( $frontend_package_types ) ) : ?>
                    <p>
                        <label for="twentyi_front_package_type"><?php echo esc_html( $atts['package_label'] ); ?></label><br />
                        <select name="package_type" id="twentyi_front_package_type" required style="width:100%;max-width:100%;">
                            <option value=""><?php esc_html_e( 'Choose a hosting package', 'twentyi-hosting-browser' ); ?></option>
                            <?php foreach ( $frontend_package_types as $front_package_type ) : ?>
                                <option value="<?php echo esc_attr( $front_package_type['id'] ); ?>" <?php selected( $default_package_type, $front_package_type['id'] ); ?>><?php echo esc_html( $front_package_type['label'] ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                <?php else : ?>
                    <input type="hidden" name="package_type" value="<?php echo esc_attr( $hidden_package_type ); ?>" />
                <?php endif; ?>
                <?php wp_nonce_field( 'twentyi_hosting_browser_frontend_request', 'twentyi_nonce' ); ?>
                <p style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
                    <label><?php esc_html_e( 'Leave this field empty', 'twentyi-hosting-browser' ); ?><input type="text" name="website_url" value="" tabindex="-1" autocomplete="off" /></label>
                </p>

                <p>
                    <label for="twentyi_front_business_name"><?php esc_html_e( 'Business / Website Name', 'twentyi-hosting-browser' ); ?></label><br />
                    <input type="text" name="business_name" id="twentyi_front_business_name" required style="width:100%;max-width:100%;" />
                </p>
                <p>
                    <label for="twentyi_front_contact_name"><?php esc_html_e( 'Your Name', 'twentyi-hosting-browser' ); ?></label><br />
                    <input type="text" name="contact_name" id="twentyi_front_contact_name" required style="width:100%;max-width:100%;" />
                </p>
                <p>
                    <label for="twentyi_front_domain_name"><?php esc_html_e( 'Domain Name', 'twentyi-hosting-browser' ); ?></label><br />
                    <input type="text" name="domain_name" id="twentyi_front_domain_name" placeholder="example.com" required style="width:100%;max-width:100%;" />
                </p>
                <p>
                    <label for="twentyi_front_email"><?php esc_html_e( 'Email Address', 'twentyi-hosting-browser' ); ?></label><br />
                    <input type="email" name="email" id="twentyi_front_email" required style="width:100%;max-width:100%;" />
                </p>
                <p>
                    <label for="twentyi_front_phone"><?php esc_html_e( 'Phone Number', 'twentyi-hosting-browser' ); ?></label><br />
                    <input type="text" name="phone" id="twentyi_front_phone" style="width:100%;max-width:100%;" />
                </p>
                <p>
                    <label for="twentyi_front_website_brief"><?php esc_html_e( 'Tell us about the website', 'twentyi-hosting-browser' ); ?></label><br />
                    <textarea name="website_brief" id="twentyi_front_website_brief" rows="6" style="width:100%;max-width:100%;"></textarea>
                </p>
                <p>
                    <label><input type="checkbox" name="terms_agreed" value="1" required /> <?php esc_html_e( 'I confirm the details are correct and agree to be contacted about this website request.', 'twentyi-hosting-browser' ); ?></label>
                </p>
                <p>
                    <button type="submit"><?php esc_html_e( 'Submit Website Request', 'twentyi-hosting-browser' ); ?></button>
                </p>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render the frontend client dashboard shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_client_dashboard_shortcode( $atts = [] ) {
        $atts = shortcode_atts(
            [
                'title' => __( 'Your Website Dashboard', 'twentyi-hosting-browser' ),
                'intro' => __( 'View the website information linked to your account.', 'twentyi-hosting-browser' ),
            ],
            $atts,
            'twentyi_client_dashboard'
        );

        if ( ! $this->client_dashboard_enabled() && ! current_user_can( 'manage_options' ) ) {
            return '';
        }

        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'Please log in to view your website dashboard.', 'twentyi-hosting-browser' ) . '</p>';
        }

        $current_user = wp_get_current_user();
        $email        = sanitize_email( $current_user->user_email );
        $notice       = isset( $_GET['twentyi_client_dashboard_notice'] ) ? sanitize_key( wp_unslash( $_GET['twentyi_client_dashboard_notice'] ) ) : '';
        $requests     = $this->get_client_dashboard_requests_for_email( $email );

        ob_start();
        ?>
        <div class="twentyi-client-dashboard" style="max-width:980px;">
            <h2><?php echo esc_html( $atts['title'] ); ?></h2>
            <p><?php echo esc_html( $atts['intro'] ); ?></p>

            <?php if ( 'welcome_sent' === $notice ) : ?>
                <div class="twentyi-client-dashboard-notice" style="padding:12px 16px;margin:0 0 16px;border:1px solid #bbf7d0;border-radius:12px;background:#f0fdf4;">
                    <?php esc_html_e( 'Your welcome email has been sent again.', 'twentyi-hosting-browser' ); ?>
                </div>
            <?php elseif ( 'mailbox_created' === $notice ) : ?>
                <div class="twentyi-client-dashboard-notice" style="padding:12px 16px;margin:0 0 16px;border:1px solid #bbf7d0;border-radius:12px;background:#f0fdf4;">
                    <?php esc_html_e( 'Your mailbox has been created.', 'twentyi-hosting-browser' ); ?>
                </div>
            <?php elseif ( 'mailbox_limit' === $notice ) : ?>
                <div class="twentyi-client-dashboard-notice" style="padding:12px 16px;margin:0 0 16px;border:1px solid #fde68a;border-radius:12px;background:#fffbeb;">
                    <?php esc_html_e( 'You have reached the mailbox limit for this website. Please contact support if you need another mailbox.', 'twentyi-hosting-browser' ); ?>
                </div>
            <?php elseif ( 'error' === $notice ) : ?>
                <div class="twentyi-client-dashboard-notice" style="padding:12px 16px;margin:0 0 16px;border:1px solid #fecaca;border-radius:12px;background:#fef2f2;">
                    <?php esc_html_e( 'Sorry, we could not complete that action. Please contact support.', 'twentyi-hosting-browser' ); ?>
                </div>
            <?php endif; ?>

            <?php if ( empty( $requests ) ) : ?>
                <div style="padding:18px;border:1px solid #e5e7eb;border-radius:14px;background:#f8fafc;">
                    <?php esc_html_e( 'No website information is linked to your account yet.', 'twentyi-hosting-browser' ); ?>
                </div>
            <?php else : ?>
                <?php foreach ( $requests as $request ) : ?>
                    <?php echo $this->render_client_dashboard_card( (int) $request->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render one client dashboard card.
     *
     * @param int $request_id Request ID.
     * @return string
     */
    protected function render_client_dashboard_card( $request_id ) {
        $request_id       = absint( $request_id );
        $business         = sanitize_text_field( get_post_meta( $request_id, 'business_name', true ) );
        $domain           = $this->sanitize_domain( get_post_meta( $request_id, 'domain_name', true ) );
        $temporary_host   = $domain ? $this->get_bootstrap_temporary_host( $domain ) : '';
        $email            = sanitize_email( get_post_meta( $request_id, 'email', true ) );
        $status           = sanitize_key( (string) get_post_meta( $request_id, 'status', true ) );
        $package_id       = sanitize_text_field( get_post_meta( $request_id, 'package_id', true ) );
        $package_type     = sanitize_text_field( get_post_meta( $request_id, 'package_type', true ) );
        $package_label    = $package_type ? $this->get_package_type_label( $package_type ) : __( 'Not selected', 'twentyi-hosting-browser' );
        $bootstrap_status = sanitize_key( (string) get_post_meta( $request_id, 'wp_admin_bootstrap_status', true ) );
        $welcome_status   = sanitize_key( (string) get_post_meta( $request_id, 'welcome_email_status', true ) );
        $welcome_sent     = sanitize_text_field( (string) get_post_meta( $request_id, 'welcome_email_sent_at', true ) );
        $submitted_at     = sanitize_text_field( (string) get_post_meta( $request_id, 'submitted_at', true ) );
        $created_at       = sanitize_text_field( (string) get_post_meta( $request_id, 'wp_admin_bootstrap_created_at', true ) );
        $wp_login_url     = $temporary_host ? 'https://' . $temporary_host . '/wp-login.php' : ( $domain ? 'https://' . $domain . '/wp-login.php' : '' );
        $lost_password    = $temporary_host ? 'https://' . $temporary_host . '/wp-login.php?action=lostpassword' : ( $domain ? 'https://' . $domain . '/wp-login.php?action=lostpassword' : '' );
        $billing_status   = sanitize_key( (string) get_post_meta( $request_id, 'wc_billing_status', true ) );
        $billing_source   = sanitize_key( (string) get_post_meta( $request_id, 'wc_billing_source', true ) );
        $billing_note     = sanitize_text_field( (string) get_post_meta( $request_id, 'wc_billing_note', true ) );
        $billing_updated  = sanitize_text_field( (string) get_post_meta( $request_id, 'wc_billing_updated_at', true ) );
        $dashboard_url    = get_permalink();
        $resend_url       = '';

        if ( $this->client_dashboard_allows_welcome_resend() && is_email( $email ) ) {
            $resend_url = wp_nonce_url(
                add_query_arg(
                    [
                        'action'     => 'twentyi_hosting_browser_client_dashboard_action',
                        'dash_action'=> 'resend_welcome',
                        'request_id' => $request_id,
                        'redirect_to'=> rawurlencode( $dashboard_url ?: home_url( '/' ) ),
                    ],
                    admin_url( 'admin-post.php' )
                ),
                'twentyi_hosting_browser_client_dashboard_' . $request_id
            );
        }

        ob_start();
        ?>
        <section class="twentyi-client-dashboard-card" style="margin:0 0 24px;padding:22px;border:1px solid #e5e7eb;border-radius:18px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.06);">
            <div style="display:flex;gap:16px;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;">
                <div>
                    <h3 style="margin:0 0 6px;font-size:1.35rem;"><?php echo esc_html( $business ?: $domain ?: __( 'Website', 'twentyi-hosting-browser' ) ); ?></h3>
                    <p style="margin:0;color:#64748b;"><?php echo esc_html( $this->client_dashboard_status_label( $status, $bootstrap_status, $welcome_status ) ); ?></p>
                </div>
                <?php if ( $package_id ) : ?>
                    <span style="display:inline-block;padding:5px 10px;border-radius:999px;background:#eef2ff;color:#3730a3;font-size:13px;">#<?php echo esc_html( $package_id ); ?></span>
                <?php endif; ?>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px;margin-top:18px;">
                <div style="padding:14px;border:1px solid #e5e7eb;border-radius:14px;background:#f8fafc;">
                    <strong><?php esc_html_e( 'Website Details', 'twentyi-hosting-browser' ); ?></strong>
                    <p style="margin:10px 0 0;"><?php esc_html_e( 'Website name:', 'twentyi-hosting-browser' ); ?><br /><strong><?php echo esc_html( $business ?: __( 'Not added yet', 'twentyi-hosting-browser' ) ); ?></strong></p>
                    <p style="margin:10px 0 0;"><?php esc_html_e( 'Live domain:', 'twentyi-hosting-browser' ); ?><br /><?php echo $domain ? '<a href="' . esc_url( 'https://' . $domain ) . '" target="_blank" rel="noopener">' . esc_html( $domain ) . '</a>' : esc_html__( 'Not created yet', 'twentyi-hosting-browser' ); ?></p>
                    <p style="margin:10px 0 0;"><?php esc_html_e( 'Staging URL:', 'twentyi-hosting-browser' ); ?><br /><?php echo $temporary_host && $package_id ? '<a href="' . esc_url( 'https://' . $temporary_host ) . '" target="_blank" rel="noopener">' . esc_html( $temporary_host ) . '</a>' : esc_html__( 'Available after the package is created', 'twentyi-hosting-browser' ); ?></p>
                </div>

                <div style="padding:14px;border:1px solid #e5e7eb;border-radius:14px;background:#f8fafc;">
                    <strong><?php esc_html_e( 'Login Details', 'twentyi-hosting-browser' ); ?></strong>
                    <p style="margin:10px 0 0;"><?php esc_html_e( 'WordPress username/email:', 'twentyi-hosting-browser' ); ?><br /><strong><?php echo esc_html( $email ?: __( 'Not available', 'twentyi-hosting-browser' ) ); ?></strong></p>
                    <p style="margin:10px 0 0;"><?php esc_html_e( 'WordPress login:', 'twentyi-hosting-browser' ); ?><br /><?php echo $wp_login_url && 'created' === $bootstrap_status ? '<a href="' . esc_url( $wp_login_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open WordPress Login', 'twentyi-hosting-browser' ) . '</a>' : esc_html__( 'Waiting for WordPress admin setup', 'twentyi-hosting-browser' ); ?></p>
                    <p style="margin:10px 0 0;"><?php esc_html_e( 'Password:', 'twentyi-hosting-browser' ); ?><br /><?php echo $lost_password && 'created' === $bootstrap_status ? '<a href="' . esc_url( $lost_password ) . '" target="_blank" rel="noopener">' . esc_html__( 'Set or reset your password', 'twentyi-hosting-browser' ) . '</a>' : esc_html__( 'A password setup email or temporary password welcome email will be sent when ready', 'twentyi-hosting-browser' ); ?></p>
                    <p style="margin:10px 0 0;"><?php esc_html_e( 'Welcome email:', 'twentyi-hosting-browser' ); ?><br /><strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $welcome_status ?: __( 'not sent yet', 'twentyi-hosting-browser' ) ) ) ); ?></strong><?php echo $welcome_sent ? '<br /><small>' . esc_html( $welcome_sent ) . '</small>' : ''; ?></p>
                </div>

                <div style="padding:14px;border:1px solid #e5e7eb;border-radius:14px;background:#f8fafc;">
                    <strong><?php esc_html_e( 'Package Details', 'twentyi-hosting-browser' ); ?></strong>
                    <p style="margin:10px 0 0;"><?php esc_html_e( 'Selected package:', 'twentyi-hosting-browser' ); ?><br /><strong><?php echo esc_html( $package_label ); ?></strong></p>
                    <p style="margin:10px 0 0;"><?php esc_html_e( 'Package type:', 'twentyi-hosting-browser' ); ?><br /><code><?php echo esc_html( $package_type ?: __( 'Not selected', 'twentyi-hosting-browser' ) ); ?></code></p>
                    <p style="margin:10px 0 0;"><?php esc_html_e( 'Package ID:', 'twentyi-hosting-browser' ); ?><br /><code><?php echo esc_html( $package_id ?: __( 'Not created yet', 'twentyi-hosting-browser' ) ); ?></code></p>
                    <p style="margin:10px 0 0;"><?php esc_html_e( 'StackCP login:', 'twentyi-hosting-browser' ); ?><br /><a href="<?php echo esc_url( $this->get_stackcp_login_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $this->get_stackcp_login_url() ); ?></a></p>
                </div>

                <?php if ( $billing_status ) : ?>
                    <?php $billing_badge = $this->request_has_unhealthy_billing( $request_id ) ? '#fff7ed' : '#ecfdf5'; ?>
                    <div style="padding:14px;border:1px solid #e5e7eb;border-radius:14px;background:<?php echo esc_attr( $billing_badge ); ?>;">
                        <strong><?php esc_html_e( 'Billing Status', 'twentyi-hosting-browser' ); ?></strong>
                        <p style="margin:10px 0 0;"><?php esc_html_e( 'Current status:', 'twentyi-hosting-browser' ); ?><br /><strong><?php echo esc_html( $this->format_billing_status_label( $billing_status ) ); ?></strong></p>
                        <?php if ( $billing_source ) : ?><p style="margin:10px 0 0;"><?php esc_html_e( 'Source:', 'twentyi-hosting-browser' ); ?><br /><?php echo esc_html( ucwords( str_replace( '_', ' ', $billing_source ) ) ); ?></p><?php endif; ?>
                        <?php if ( $billing_note ) : ?><p style="margin:10px 0 0;color:#92400e;"><?php echo esc_html( $billing_note ); ?></p><?php endif; ?>
                        <?php if ( $billing_updated ) : ?><p style="margin:10px 0 0;"><small><?php esc_html_e( 'Updated:', 'twentyi-hosting-browser' ); ?> <?php echo esc_html( $billing_updated ); ?></small></p><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php echo $this->render_client_mailbox_panel( $request_id, $domain, $package_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <div style="margin-top:18px;padding-top:16px;border-top:1px solid #e5e7eb;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <?php if ( $resend_url && 'created' === $bootstrap_status ) : ?>
                    <a href="<?php echo esc_url( $resend_url ); ?>" style="display:inline-block;padding:9px 13px;border-radius:10px;background:#111827;color:#fff;text-decoration:none;">
                        <?php echo 'sent' === $welcome_status ? esc_html__( 'Resend Welcome Email', 'twentyi-hosting-browser' ) : esc_html__( 'Send Welcome Email', 'twentyi-hosting-browser' ); ?>
                    </a>
                <?php endif; ?>
                <?php if ( $submitted_at ) : ?>
                    <small style="color:#64748b;"><?php esc_html_e( 'Submitted:', 'twentyi-hosting-browser' ); ?> <?php echo esc_html( $submitted_at ); ?></small>
                <?php endif; ?>
                <?php if ( $created_at ) : ?>
                    <small style="color:#64748b;"><?php esc_html_e( 'Admin created:', 'twentyi-hosting-browser' ); ?> <?php echo esc_html( $created_at ); ?></small>
                <?php endif; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }


    /**
     * Render the mailbox creation panel for a client dashboard card.
     *
     * @param int    $request_id Request ID.
     * @param string $domain Domain name.
     * @param string $package_id 20i package ID.
     * @return string
     */
    protected function render_client_mailbox_panel( $request_id, $domain, $package_id ) {
        if ( ! $this->client_dashboard_allows_mailbox_creation() || '' === $domain || '' === $package_id ) {
            return '';
        }

        if ( $this->wc_billing_blocks_client_actions() && $this->request_has_unhealthy_billing( $request_id ) ) {
            ob_start();
            ?>
            <div style="margin-top:18px;padding:16px;border:1px solid #fed7aa;border-radius:16px;background:#fff7ed;color:#9a3412;">
                <strong><?php esc_html_e( 'Email creation is paused', 'twentyi-hosting-browser' ); ?></strong>
                <p style="margin:8px 0 0;"><?php esc_html_e( 'Your subscription or payment status needs attention before new mailbox actions are available. Please contact support or check your billing.', 'twentyi-hosting-browser' ); ?></p>
            </div>
            <?php
            return ob_get_clean();
        }

        $mailboxes = $this->get_client_created_mailboxes( $request_id );
        $limit     = $this->get_client_dashboard_mailbox_limit();
        $remaining = max( 0, $limit - count( $mailboxes ) );
        $action    = esc_url( admin_url( 'admin-post.php' ) );
        $redirect  = esc_url_raw( get_permalink() ?: home_url( '/' ) );

        ob_start();
        ?>
        <div style="margin-top:18px;padding:16px;border:1px solid #dbeafe;border-radius:16px;background:#eff6ff;">
            <strong><?php esc_html_e( 'Create Your Email Address', 'twentyi-hosting-browser' ); ?></strong>
            <p style="margin:8px 0 0;color:#475569;"><?php echo esc_html( sprintf( __( 'You can create up to %d mailbox(es) for this website.', 'twentyi-hosting-browser' ), $limit ) ); ?></p>

            <?php if ( ! empty( $mailboxes ) ) : ?>
                <div style="margin-top:12px;">
                    <strong style="display:block;margin-bottom:6px;"><?php esc_html_e( 'Created mailboxes', 'twentyi-hosting-browser' ); ?></strong>
                    <ul style="margin:0 0 0 18px;">
                        <?php foreach ( $mailboxes as $mailbox ) : ?>
                            <li>
                                <code><?php echo esc_html( $mailbox['address'] ?? '' ); ?></code>
                                <?php if ( ! empty( $mailbox['created_at'] ) ) : ?>
                                    <small style="color:#64748b;"> <?php echo esc_html( $this->format_date( $mailbox['created_at'] ) ); ?></small>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ( $remaining <= 0 ) : ?>
                <p style="margin:12px 0 0;color:#92400e;"><?php esc_html_e( 'You have reached the mailbox limit for this website. Please contact support if you need another mailbox.', 'twentyi-hosting-browser' ); ?></p>
            <?php else : ?>
                <form method="post" action="<?php echo $action; ?>" style="margin-top:14px;display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px;align-items:end;">
                    <input type="hidden" name="action" value="twentyi_hosting_browser_client_dashboard_action" />
                    <input type="hidden" name="dash_action" value="create_mailbox" />
                    <input type="hidden" name="request_id" value="<?php echo esc_attr( $request_id ); ?>" />
                    <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>" />
                    <?php wp_nonce_field( 'twentyi_hosting_browser_client_dashboard_' . $request_id ); ?>

                    <label>
                        <span style="display:block;font-weight:600;margin-bottom:4px;"><?php esc_html_e( 'Email prefix', 'twentyi-hosting-browser' ); ?></span>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <input type="text" name="mail_local" pattern="[A-Za-z0-9._%+\-]+" placeholder="hello" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:10px;" />
                            <span>@<?php echo esc_html( $domain ); ?></span>
                        </span>
                    </label>

                    <label>
                        <span style="display:block;font-weight:600;margin-bottom:4px;"><?php esc_html_e( 'Password', 'twentyi-hosting-browser' ); ?></span>
                        <input type="password" name="mail_password" minlength="8" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:10px;" />
                    </label>

                    <label>
                        <span style="display:block;font-weight:600;margin-bottom:4px;"><?php esc_html_e( 'Confirm Password', 'twentyi-hosting-browser' ); ?></span>
                        <input type="password" name="mail_password_confirm" minlength="8" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:10px;" />
                    </label>

                    <button type="submit" style="padding:10px 14px;border:0;border-radius:10px;background:#1d4ed8;color:#fff;cursor:pointer;">
                        <?php esc_html_e( 'Create Email Address', 'twentyi-hosting-browser' ); ?>
                    </button>
                </form>
                <p style="margin:10px 0 0;color:#64748b;font-size:13px;"><?php esc_html_e( 'For security, your mailbox password is not stored or displayed in this dashboard after creation.', 'twentyi-hosting-browser' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle frontend client dashboard actions.
     */
    public function handle_client_dashboard_action() {
        $source      = 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ? $_POST : $_GET;
        $request_id  = absint( $source['request_id'] ?? 0 );
        $dash_action = sanitize_key( wp_unslash( $source['dash_action'] ?? '' ) );
        $redirect_to = isset( $source['redirect_to'] ) ? esc_url_raw( rawurldecode( wp_unslash( $source['redirect_to'] ) ) ) : home_url( '/' );

        if ( ! $redirect_to ) {
            $redirect_to = home_url( '/' );
        }

        $redirect = function( $notice ) use ( $redirect_to ) {
            wp_safe_redirect( add_query_arg( 'twentyi_client_dashboard_notice', sanitize_key( $notice ), $redirect_to ) );
            exit;
        };

        if ( ! is_user_logged_in() || ! $request_id || self::REQUEST_POST_TYPE !== get_post_type( $request_id ) ) {
            $redirect( 'error' );
        }

        check_admin_referer( 'twentyi_hosting_browser_client_dashboard_' . $request_id );

        $current_user  = wp_get_current_user();
        $request_email = sanitize_email( get_post_meta( $request_id, 'email', true ) );

        if ( ! $current_user || ! is_email( $current_user->user_email ) || strtolower( $current_user->user_email ) !== strtolower( $request_email ) ) {
            $redirect( 'error' );
        }

        if ( 'resend_welcome' === $dash_action ) {
            if ( ! $this->client_dashboard_allows_welcome_resend() ) {
                $redirect( 'error' );
            }

            if ( 'created' !== sanitize_key( (string) get_post_meta( $request_id, 'wp_admin_bootstrap_status', true ) ) ) {
                $redirect( 'error' );
            }

            $result = $this->send_client_welcome_email( $request_id, [], true );

            if ( is_wp_error( $result ) ) {
                $redirect( 'error' );
            }

            $redirect( 'welcome_sent' );
        }

        if ( 'create_mailbox' === $dash_action ) {
            if ( ! $this->client_dashboard_allows_mailbox_creation() ) {
                $redirect( 'error' );
            }

            if ( $this->wc_billing_blocks_client_actions() && $this->request_has_unhealthy_billing( $request_id ) ) {
                $redirect( 'billing_blocked' );
            }

            $package_id = sanitize_text_field( get_post_meta( $request_id, 'package_id', true ) );
            $domain     = $this->sanitize_domain( get_post_meta( $request_id, 'domain_name', true ) );

            if ( '' === $package_id || '' === $domain ) {
                $redirect( 'error' );
            }

            $created_mailboxes = $this->get_client_created_mailboxes( $request_id );
            if ( count( $created_mailboxes ) >= $this->get_client_dashboard_mailbox_limit() ) {
                $redirect( 'mailbox_limit' );
            }

            $mail_local    = $this->sanitize_mail_local( $source['mail_local'] ?? '' );
            $mail_password = isset( $source['mail_password'] ) ? trim( (string) wp_unslash( $source['mail_password'] ) ) : '';
            $mail_confirm  = isset( $source['mail_password_confirm'] ) ? trim( (string) wp_unslash( $source['mail_password_confirm'] ) ) : '';

            if ( '' === $mail_local || '' === $mail_password || $mail_password !== $mail_confirm || strlen( $mail_password ) < 8 ) {
                $redirect( 'error' );
            }

            foreach ( $created_mailboxes as $mailbox ) {
                if ( isset( $mailbox['address'] ) && strtolower( $mailbox['address'] ) === strtolower( $mail_local . '@' . $domain ) ) {
                    $redirect( 'error' );
                }
            }

            $result = $this->perform_create_mailbox( $package_id, $domain, $mail_local, $mail_password, true, true );

            if ( is_wp_error( $result ) ) {
                $this->add_activity_log(
                    'client_mailbox_create',
                    __( 'Client mailbox creation failed.', 'twentyi-hosting-browser' ),
                    [
                        'request_id' => $request_id,
                        'package_id' => $package_id,
                        'address'    => $mail_local . '@' . $domain,
                        'error'      => $result->get_error_message(),
                    ],
                    'error'
                );
                $redirect( 'error' );
            }

            $this->record_client_created_mailbox( $request_id, $mail_local, $domain );
            $this->clear_package_cache( $package_id );

            $this->add_activity_log(
                'client_mailbox_create',
                sprintf(
                    /* translators: %s: mailbox address */
                    __( 'Client created mailbox %s from the frontend dashboard.', 'twentyi-hosting-browser' ),
                    $mail_local . '@' . $domain
                ),
                [
                    'request_id' => $request_id,
                    'package_id' => $package_id,
                    'address'    => $mail_local . '@' . $domain,
                ],
                'success'
            );

            $redirect( 'mailbox_created' );
        }

        $redirect( 'error' );
    }

    /**
     * Activity log page.
     */
    public function render_activity_log_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $cleared = isset( $_GET['log_cleared'] ) ? (bool) absint( $_GET['log_cleared'] ) : false;
        $logs    = $this->get_activity_log();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( '20i Activity Log', 'twentyi-hosting-browser' ); ?></h1>
            <p><?php esc_html_e( 'A local admin audit trail for the main hosting actions performed through this plugin.', 'twentyi-hosting-browser' ); ?></p>

            <?php if ( $cleared ) : ?>
                <div class="notice notice-success inline"><p><?php esc_html_e( 'Activity log cleared.', 'twentyi-hosting-browser' ); ?></p></div>
            <?php endif; ?>

            <p>
                <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG ) ); ?>"><?php esc_html_e( 'Back to Websites', 'twentyi-hosting-browser' ); ?></a>
                <a class="button button-secondary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=twentyi_hosting_browser_clear_activity_log' ), 'twentyi_hosting_browser_clear_activity_log' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Clear the local activity log?', 'twentyi-hosting-browser' ) ); ?>');"><?php esc_html_e( 'Clear Log', 'twentyi-hosting-browser' ); ?></a>
            </p>

            <table class="widefat striped fixed">
                <thead>
                    <tr>
                        <th style="width:170px;"><?php esc_html_e( 'When', 'twentyi-hosting-browser' ); ?></th>
                        <th style="width:120px;"><?php esc_html_e( 'Status', 'twentyi-hosting-browser' ); ?></th>
                        <th style="width:170px;"><?php esc_html_e( 'Type', 'twentyi-hosting-browser' ); ?></th>
                        <th><?php esc_html_e( 'Message', 'twentyi-hosting-browser' ); ?></th>
                        <th><?php esc_html_e( 'Context', 'twentyi-hosting-browser' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty( $logs ) ) : ?>
                    <tr>
                        <td colspan="5"><?php esc_html_e( 'No activity has been logged yet.', 'twentyi-hosting-browser' ); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ( $logs as $entry ) : ?>
                        <?php
                        $status = isset( $entry['status'] ) ? sanitize_key( $entry['status'] ) : 'info';
                        $badge  = 'success' === $status ? '#edfaef' : ( 'error' === $status ? '#fcf0f1' : '#f0f6fc' );
                        ?>
                        <tr>
                            <td><?php echo esc_html( $this->format_date( $entry['time'] ?? '' ) ); ?></td>
                            <td><span style="display:inline-block; padding:3px 8px; border-radius:999px; background:<?php echo esc_attr( $badge ); ?>;"><?php echo esc_html( ucfirst( $status ) ); ?></span></td>
                            <td><code><?php echo esc_html( $entry['type'] ?? 'general' ); ?></code></td>
                            <td><?php echo esc_html( $entry['message'] ?? '' ); ?></td>
                            <td>
                                <?php if ( ! empty( $entry['context'] ) && is_array( $entry['context'] ) ) : ?>
                                    <pre style="white-space:pre-wrap; margin:0; max-height:140px; overflow:auto;"><?php echo esc_html( wp_json_encode( $entry['context'], JSON_PRETTY_PRINT ) ); ?></pre>
                                <?php else : ?>
                                    &ndash;
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Settings page.
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $test_result = null;

        if ( isset( $_GET['twentyi_test_connection'] ) && '1' === $_GET['twentyi_test_connection'] ) {
            check_admin_referer( 'twentyi_hosting_browser_test_connection' );
            $test_result = $this->test_connection();
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( '20i Hosting Settings', 'twentyi-hosting-browser' ); ?></h1>

            <?php settings_errors(); ?>

            <?php if ( is_array( $test_result ) ) : ?>
                <?php if ( ! empty( $test_result['success'] ) ) : ?>
                    <div class="notice notice-success"><p><?php echo esc_html( $test_result['message'] ); ?></p></div>
                <?php else : ?>
                    <div class="notice notice-error"><p><?php echo esc_html( $test_result['message'] ); ?></p></div>
                <?php endif; ?>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'twentyi_hosting_browser_settings_group' );
                do_settings_sections( self::SETTINGS_SLUG );
                submit_button( __( 'Save Settings', 'twentyi-hosting-browser' ) );
                ?>
            </form>

            <hr />

            <h2><?php esc_html_e( 'Connection Test', 'twentyi-hosting-browser' ); ?></h2>
            <p><?php esc_html_e( 'Use this to confirm the API key can read your hosting packages from 20i.', 'twentyi-hosting-browser' ); ?></p>
            <p>
                <a class="button button-secondary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=' . self::SETTINGS_SLUG . '&twentyi_test_connection=1' ), 'twentyi_hosting_browser_test_connection' ) ); ?>">
                    <?php esc_html_e( 'Test Connection', 'twentyi-hosting-browser' ); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Websites page.
     */
    public function render_websites_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $force_refresh = isset( $_GET['refreshed'] ) ? (bool) absint( $_GET['refreshed'] ) : false;
        $site_status   = isset( $_GET['site_status'] ) ? sanitize_key( wp_unslash( $_GET['site_status'] ) ) : '';
        $site_message  = isset( $_GET['site_message'] ) ? sanitize_text_field( wp_unslash( $_GET['site_message'] ) ) : '';
        $result        = $this->get_packages( $force_refresh );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( '20i Hosted Websites', 'twentyi-hosting-browser' ); ?></h1>
            <p><?php esc_html_e( 'This page shows hosting packages returned by the 20i API.', 'twentyi-hosting-browser' ); ?></p>

            <?php if ( '' !== $site_message ) : ?>
                <div class="notice <?php echo 'success' === $site_status ? 'notice-success' : 'notice-error'; ?> inline"><p><?php echo esc_html( $site_message ); ?></p></div>
            <?php endif; ?>

            <p>
                <a class="button button-secondary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=twentyi_hosting_browser_refresh' ), 'twentyi_hosting_browser_refresh' ) ); ?>">
                    <?php esc_html_e( 'Refresh List', 'twentyi-hosting-browser' ); ?>
                </a>
                <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::CREATE_SLUG ) ); ?>">
                    <?php esc_html_e( 'Add Website', 'twentyi-hosting-browser' ); ?>
                </a>
                <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::SETTINGS_SLUG ) ); ?>">
                    <?php esc_html_e( 'Settings', 'twentyi-hosting-browser' ); ?>
                </a>
                <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::ACTIVITY_LOG_SLUG ) ); ?>">
                    <?php esc_html_e( 'Activity Log', 'twentyi-hosting-browser' ); ?>
                </a>
            </p>

            <?php if ( empty( $this->get_api_key() ) ) : ?>
                <div class="notice notice-warning inline"><p><?php esc_html_e( 'Add your 20i API key on the Settings page to load websites.', 'twentyi-hosting-browser' ); ?></p></div>
            <?php elseif ( is_wp_error( $result ) ) : ?>
                <div class="notice notice-error inline"><p><?php echo esc_html( $result->get_error_message() ); ?></p></div>
            <?php else : ?>
                <?php $packages = is_array( $result ) ? $result : []; ?>
                <p>
                    <?php
                    printf(
                        esc_html( _n( '%d website found.', '%d websites found.', count( $packages ), 'twentyi-hosting-browser' ) ),
                        intval( count( $packages ) )
                    );
                    ?>
                </p>

                <table class="widefat striped fixed">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Primary Domain', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Package ID', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Domains', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Package Type', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Created', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Enabled', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'StackCP Users', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'twentyi-hosting-browser' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ( empty( $packages ) ) : ?>
                        <tr>
                            <td colspan="8"><?php esc_html_e( 'No websites were returned by the API.', 'twentyi-hosting-browser' ); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $packages as $package ) : ?>
                            <?php
                            $primary_name = isset( $package['name'] ) ? (string) $package['name'] : '';
                            $package_id   = isset( $package['id'] ) ? (string) $package['id'] : '';
                            $names        = isset( $package['names'] ) && is_array( $package['names'] ) ? $package['names'] : [];
                            $type_name    = isset( $package['packageTypeName'] ) ? (string) $package['packageTypeName'] : '';
                            $created      = isset( $package['created'] ) ? (string) $package['created'] : '';
                            $enabled      = ! empty( $package['enabled'] );
                            $stack_users  = isset( $package['stackUsers'] ) && is_array( $package['stackUsers'] ) ? $package['stackUsers'] : [];
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html( $primary_name ?: '—' ); ?></strong></td>
                                <td><?php echo esc_html( $package_id ?: '—' ); ?></td>
                                <td>
                                    <?php if ( ! empty( $names ) ) : ?>
                                        <code><?php echo esc_html( implode( ', ', $names ) ); ?></code>
                                    <?php else : ?>
                                        &ndash;
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $type_name ?: '—' ); ?></td>
                                <td><?php echo esc_html( $this->format_date( $created ) ); ?></td>
                                <td><?php echo $enabled ? esc_html__( 'Yes', 'twentyi-hosting-browser' ) : esc_html__( 'No', 'twentyi-hosting-browser' ); ?></td>
                                <td>
                                    <?php if ( ! empty( $stack_users ) ) : ?>
                                        <code><?php echo esc_html( implode( ', ', $stack_users ) ); ?></code>
                                    <?php else : ?>
                                        &ndash;
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( '' !== $package_id ) : ?>
                                        <a class="button button-small" href="<?php echo esc_url( $this->get_package_page_url( $package_id ) ); ?>">
                                            <?php esc_html_e( 'View Package', 'twentyi-hosting-browser' ); ?>
                                        </a>
                                    <?php else : ?>
                                        &ndash;
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render create package page.
     */
    public function render_create_package_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $package_types        = $this->get_package_types();
        $stack_users          = $this->get_stack_users();
        $created_id           = isset( $_GET['created_package'] ) ? sanitize_text_field( wp_unslash( $_GET['created_package'] ) ) : '';
        $message              = isset( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : '';
        $domain_lookup        = isset( $_GET['domain_lookup'] ) ? $this->sanitize_domain( wp_unslash( $_GET['domain_lookup'] ) ) : '';
        $domain_lookup_result = '' !== $domain_lookup ? $this->search_domain_availability( $domain_lookup ) : null;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Add Website to 20i', 'twentyi-hosting-browser' ); ?></h1>
            <p><?php esc_html_e( 'Create a new hosting package in your 20i account from inside WordPress.', 'twentyi-hosting-browser' ); ?></p>
            <?php $this->render_domain_search_box( $domain_lookup, $domain_lookup_result ); ?>

            <?php if ( $created_id ) : ?>
                <div class="notice notice-success"><p>
                    <?php
                    printf(
                        esc_html__( 'Website created successfully. New package ID: %s', 'twentyi-hosting-browser' ),
                        esc_html( $created_id )
                    );
                    ?>
                </p></div>
                <p>
                    <a class="button button-primary" href="<?php echo esc_url( $this->get_package_page_url( $created_id ) ); ?>">
                        <?php esc_html_e( 'View New Package', 'twentyi-hosting-browser' ); ?>
                    </a>
                </p>
            <?php elseif ( $message ) : ?>
                <div class="notice notice-error"><p><?php echo esc_html( $message ); ?></p></div>
            <?php endif; ?>

            <?php if ( empty( $this->get_api_key() ) ) : ?>
                <div class="notice notice-warning inline"><p><?php esc_html_e( 'Add your 20i API key on the Settings page first.', 'twentyi-hosting-browser' ); ?></p></div>
                <p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::SETTINGS_SLUG ) ); ?>"><?php esc_html_e( 'Go to Settings', 'twentyi-hosting-browser' ); ?></a></p>
                <?php
                return;
            endif;
            ?>

            <?php if ( is_wp_error( $package_types ) ) : ?>
                <div class="notice notice-error inline"><p><?php echo esc_html( $package_types->get_error_message() ); ?></p></div>
                <?php
                return;
            endif;
            ?>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="twentyi_hosting_browser_create_package" />
                <?php wp_nonce_field( 'twentyi_hosting_browser_create_package' ); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="twentyi_domain_name"><?php esc_html_e( 'Primary Domain', 'twentyi-hosting-browser' ); ?></label></th>
                            <td>
                                <input type="text" name="domain_name" id="twentyi_domain_name" class="regular-text" placeholder="example.com" required />
                                <p class="description"><?php esc_html_e( 'The main domain for the new hosting package.', 'twentyi-hosting-browser' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="twentyi_type"><?php esc_html_e( 'Package Type', 'twentyi-hosting-browser' ); ?></label></th>
                            <td>
                                <select name="type" id="twentyi_type" required>
                                    <option value=""><?php esc_html_e( 'Select a package type', 'twentyi-hosting-browser' ); ?></option>
                                    <?php foreach ( $package_types as $package_type ) : ?>
                                        <option value="<?php echo esc_attr( $package_type['id'] ); ?>"><?php echo esc_html( $package_type['label'] ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php esc_html_e( 'Loaded from your 20i package types.', 'twentyi-hosting-browser' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="twentyi_label"><?php esc_html_e( 'Label', 'twentyi-hosting-browser' ); ?></label></th>
                            <td>
                                <input type="text" name="label" id="twentyi_label" class="regular-text" placeholder="Client Website" />
                                <p class="description"><?php esc_html_e( 'Optional memorable name for the package.', 'twentyi-hosting-browser' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="twentyi_extra_domain_names"><?php esc_html_e( 'Extra Domains', 'twentyi-hosting-browser' ); ?></label></th>
                            <td>
                                <textarea name="extra_domain_names" id="twentyi_extra_domain_names" rows="4" class="large-text" placeholder="example.org&#10;www.example.com"></textarea>
                                <p class="description"><?php esc_html_e( 'Optional. One domain per line, or comma-separated.', 'twentyi-hosting-browser' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="twentyi_stack_user"><?php esc_html_e( 'Assign StackCP User', 'twentyi-hosting-browser' ); ?></label></th>
                            <td>
                                <select name="stackUser" id="twentyi_stack_user">
                                    <option value=""><?php esc_html_e( 'Do not assign a StackCP user', 'twentyi-hosting-browser' ); ?></option>
                                    <?php if ( ! is_wp_error( $stack_users ) ) : ?>
                                        <?php foreach ( $stack_users as $stack_user ) : ?>
                                            <option value="<?php echo esc_attr( $stack_user['id'] ); ?>"><?php echo esc_html( $stack_user['label'] ); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <p class="description"><?php esc_html_e( 'Optional. Links the new website to an existing StackCP user.', 'twentyi-hosting-browser' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="twentyi_document_roots"><?php esc_html_e( 'Document Roots', 'twentyi-hosting-browser' ); ?></label></th>
                            <td>
                                <textarea name="document_roots" id="twentyi_document_roots" rows="5" class="large-text code" placeholder="example.com=/public_html&#10;www.example.com=/public_html"></textarea>
                                <p class="description"><?php esc_html_e( 'Optional. One mapping per line in the format domain=/path.', 'twentyi-hosting-browser' ); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button( __( 'Create Website', 'twentyi-hosting-browser' ) ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render StackCP users page.
     */
    public function render_stack_users_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $search           = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
        $selected_user_id = isset( $_GET['stack_user'] ) ? sanitize_text_field( wp_unslash( $_GET['stack_user'] ) ) : '';
        $stack_users      = $this->get_stack_users();

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'StackCP Users', 'twentyi-hosting-browser' ); ?></h1>
            <p><?php esc_html_e( 'View StackCP users, see which packages they can access, generate handoff details for clients, and create new StackCP users where your 20i account exposes the user creation route.', 'twentyi-hosting-browser' ); ?></p>

            <?php $this->render_stack_user_admin_notice(); ?>
            <?php $this->render_create_stack_user_box(); ?>

            <form method="get" style="margin:16px 0; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <input type="hidden" name="page" value="<?php echo esc_attr( self::STACK_USERS_SLUG ); ?>" />
                <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Search by name, email or StackCP user ID', 'twentyi-hosting-browser' ); ?>" />
                <?php submit_button( __( 'Search Users', 'twentyi-hosting-browser' ), 'secondary', '', false ); ?>
                <?php if ( '' !== $search ) : ?>
                    <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::STACK_USERS_SLUG ) ); ?>"><?php esc_html_e( 'Clear', 'twentyi-hosting-browser' ); ?></a>
                <?php endif; ?>
            </form>

            <?php if ( is_wp_error( $stack_users ) ) : ?>
                <div class="notice notice-error inline"><p><?php echo esc_html( $stack_users->get_error_message() ); ?></p></div>
            <?php else : ?>
                <?php
                $package_map = $this->build_stack_user_package_map();
                $filtered    = $this->filter_stack_users( $stack_users, $search );
                $selected    = '' !== $selected_user_id ? $this->find_stack_user_by_id( $selected_user_id, $stack_users ) : null;
                ?>

                <?php if ( ! empty( $selected ) ) : ?>
                    <?php $this->render_stack_user_detail_box( $selected, $package_map[ $selected['id'] ] ?? [] ); ?>
                <?php endif; ?>

                <p>
                    <?php
                    printf(
                        esc_html( _n( '%d StackCP user found.', '%d StackCP users found.', count( $filtered ), 'twentyi-hosting-browser' ) ),
                        intval( count( $filtered ) )
                    );
                    ?>
                </p>

                <table class="widefat striped fixed">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'User', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'User ID', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Email', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Packages', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Grants', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'twentyi-hosting-browser' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ( empty( $filtered ) ) : ?>
                        <tr>
                            <td colspan="6"><?php esc_html_e( 'No StackCP users matched your search.', 'twentyi-hosting-browser' ); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $filtered as $stack_user ) : ?>
                            <?php $packages = $package_map[ $stack_user['id'] ] ?? []; ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html( $stack_user['name'] ?: $stack_user['label'] ); ?></strong>
                                    <?php if ( ! empty( $stack_user['company'] ) ) : ?>
                                        <br /><span class="description"><?php echo esc_html( $stack_user['company'] ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?php echo esc_html( $stack_user['id'] ); ?></code></td>
                                <td><?php echo ! empty( $stack_user['email'] ) ? esc_html( $stack_user['email'] ) : '—'; ?></td>
                                <td>
                                    <?php if ( empty( $packages ) ) : ?>
                                        &ndash;
                                    <?php else : ?>
                                        <?php foreach ( array_slice( $packages, 0, 3 ) as $package ) : ?>
                                            <div><a href="<?php echo esc_url( $this->get_package_page_url( $package['id'] ) ); ?>"><?php echo esc_html( $package['name'] ?: $package['id'] ); ?></a></div>
                                        <?php endforeach; ?>
                                        <?php if ( count( $packages ) > 3 ) : ?>
                                            <div class="description"><?php printf( esc_html__( '+ %d more', 'twentyi-hosting-browser' ), intval( count( $packages ) - 3 ) ); ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo ! empty( $stack_user['grants_summary'] ) ? esc_html( $stack_user['grants_summary'] ) : '—'; ?></td>
                                <td>
                                    <a class="button button-small" href="<?php echo esc_url( add_query_arg( [ 'page' => self::STACK_USERS_SLUG, 'stack_user' => $stack_user['id'], 's' => $search ], admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'View User', 'twentyi-hosting-browser' ); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render StackCP create-user notices from redirects.
     */
    protected function render_stack_user_admin_notice() {
        $status  = isset( $_GET['stack_user_status'] ) ? sanitize_key( wp_unslash( $_GET['stack_user_status'] ) ) : '';
        $message = isset( $_GET['stack_user_message'] ) ? sanitize_text_field( wp_unslash( $_GET['stack_user_message'] ) ) : '';

        if ( '' === $message ) {
            return;
        }

        $class = 'success' === $status ? 'notice-success' : 'notice-error';
        echo '<div class="notice ' . esc_attr( $class ) . ' inline"><p>' . esc_html( $message ) . '</p></div>';
    }

    /**
     * Render create StackCP user form.
     */
    protected function render_create_stack_user_box() {
        $packages         = $this->get_packages();
        $selected_package = isset( $_GET['assign_package'] ) ? sanitize_text_field( wp_unslash( $_GET['assign_package'] ) ) : '';
        ?>
        <div style="margin:16px 0 24px; background:#fff; border:1px solid #dcdcde; padding:16px 20px;">
            <h2 style="margin-top:0;"><?php esc_html_e( 'Create StackCP User', 'twentyi-hosting-browser' ); ?></h2>
            <p><?php esc_html_e( 'Create a customer login for StackCP. If your 20i account exposes the create-user route through the API, this form will submit it directly. If 20i rejects the route, the plugin shows a clear fallback message so you can create the user manually in My20i.', 'twentyi-hosting-browser' ); ?></p>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="twentyi_hosting_browser_create_stack_user" />
                <?php wp_nonce_field( 'twentyi_hosting_browser_create_stack_user' ); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="twentyi_stack_full_name"><?php esc_html_e( 'Client Name', 'twentyi-hosting-browser' ); ?></label></th>
                            <td>
                                <input type="text" id="twentyi_stack_full_name" name="stack_full_name" class="regular-text" required placeholder="Jane Smith" />
                                <p class="description"><?php esc_html_e( 'Used for the StackCP contact name.', 'twentyi-hosting-browser' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="twentyi_stack_email"><?php esc_html_e( 'Client Email', 'twentyi-hosting-browser' ); ?></label></th>
                            <td><input type="email" id="twentyi_stack_email" name="stack_email" class="regular-text" required placeholder="client@example.com" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="twentyi_stack_company"><?php esc_html_e( 'Company', 'twentyi-hosting-browser' ); ?></label></th>
                            <td><input type="text" id="twentyi_stack_company" name="stack_company" class="regular-text" placeholder="Client Company Ltd" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="twentyi_stack_phone"><?php esc_html_e( 'Phone', 'twentyi-hosting-browser' ); ?></label></th>
                            <td><input type="text" id="twentyi_stack_phone" name="stack_phone" class="regular-text" placeholder="+44..." /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="twentyi_stack_password"><?php esc_html_e( 'Password', 'twentyi-hosting-browser' ); ?></label></th>
                            <td>
                                <input type="text" id="twentyi_stack_password" name="stack_password" class="regular-text code" />
                                <button type="button" class="button" onclick="document.getElementById('twentyi_stack_password').value = Math.random().toString(36).slice(-8) + Math.random().toString(36).slice(-8) + '!';"><?php esc_html_e( 'Generate', 'twentyi-hosting-browser' ); ?></button>
                                <p class="description"><?php esc_html_e( 'Optional. Some 20i routes auto-generate a password, while others accept one in the payload.', 'twentyi-hosting-browser' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="twentyi_stack_assign_package"><?php esc_html_e( 'Assign Package', 'twentyi-hosting-browser' ); ?></label></th>
                            <td>
                                <select id="twentyi_stack_assign_package" name="assign_package" class="regular-text">
                                    <option value=""><?php esc_html_e( 'Do not assign during creation', 'twentyi-hosting-browser' ); ?></option>
                                    <?php if ( ! is_wp_error( $packages ) && is_array( $packages ) ) : ?>
                                        <?php foreach ( $packages as $package ) : ?>
                                            <?php
                                            $package_id   = isset( $package['id'] ) ? (string) $package['id'] : '';
                                            $package_name = isset( $package['name'] ) ? (string) $package['name'] : $package_id;
                                            ?>
                                            <?php if ( '' !== $package_id ) : ?>
                                                <option value="<?php echo esc_attr( $package_id ); ?>" <?php selected( $selected_package, $package_id ); ?>><?php echo esc_html( $package_name . ' - ' . $package_id ); ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <p class="description"><?php esc_html_e( 'Assignment uses defensive API candidates because 20i’s public docs do not publish a full create-and-grant payload.', 'twentyi-hosting-browser' ); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button( __( 'Create StackCP User', 'twentyi-hosting-browser' ) ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render package details page.
     */
    public function render_package_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $package_id     = isset( $_GET['package_id'] ) ? sanitize_text_field( wp_unslash( $_GET['package_id'] ) ) : '';
        $force_refresh  = isset( $_GET['refresh_package'] ) ? (bool) absint( $_GET['refresh_package'] ) : false;
        $action_status  = isset( $_GET['action_status'] ) ? sanitize_key( wp_unslash( $_GET['action_status'] ) ) : '';
        $action_message = isset( $_GET['action_message'] ) ? sanitize_text_field( wp_unslash( $_GET['action_message'] ) ) : '';

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( '20i Package Details', 'twentyi-hosting-browser' ); ?></h1>
            <?php if ( '' === $package_id ) : ?>
                <div class="notice notice-warning inline"><p><?php esc_html_e( 'Choose a package from the Websites screen first.', 'twentyi-hosting-browser' ); ?></p></div>
                <p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG ) ); ?>"><?php esc_html_e( 'Back to Websites', 'twentyi-hosting-browser' ); ?></a></p>
            <?php else : ?>
                <?php
                if ( $force_refresh ) {
                    check_admin_referer( 'twentyi_hosting_browser_refresh_package_' . $package_id );
                }

                $summary = $this->get_package_summary( $package_id, $force_refresh );
                $bundle  = $this->get_package_detail_bundle( $package_id, $force_refresh, is_wp_error( $summary ) ? [] : $summary );
                ?>

                <p>
                    <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG ) ); ?>"><?php esc_html_e( 'Back to Websites', 'twentyi-hosting-browser' ); ?></a>
                    <a class="button button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'page' => self::PACKAGE_SLUG, 'package_id' => $package_id, 'refresh_package' => 1 ], admin_url( 'admin.php' ) ), 'twentyi_hosting_browser_refresh_package_' . $package_id ) ); ?>">
                        <?php esc_html_e( 'Refresh Package Data', 'twentyi-hosting-browser' ); ?>
                    </a>
                </p>

                <?php if ( '' !== $action_message ) : ?>
                    <div class="notice <?php echo 'success' === $action_status ? 'notice-success' : 'notice-error'; ?> inline"><p><?php echo esc_html( $action_message ); ?></p></div>
                <?php endif; ?>

                <?php if ( is_wp_error( $summary ) ) : ?>
                    <div class="notice notice-error inline"><p><?php echo esc_html( $summary->get_error_message() ); ?></p></div>
                <?php else : ?>
                    <?php $this->render_package_summary_box( $summary ); ?>
                    <?php $this->render_domain_dns_onboarding_box( $summary ); ?>
                <?php endif; ?>

                <?php $this->render_package_actions_box( $package_id, is_wp_error( $bundle ) ? [] : $bundle ); ?>
                <?php $this->render_mailbox_management_box( is_wp_error( $summary ) ? [] : $summary, is_wp_error( $bundle ) ? [] : $bundle ); ?>
                <?php $this->render_client_handoff_box( $summary, is_wp_error( $bundle ) ? [] : $bundle ); ?>
                <?php $this->render_delete_site_box( is_wp_error( $summary ) ? [] : $summary ); ?>

                <?php if ( is_wp_error( $bundle ) ) : ?>
                    <div class="notice notice-error inline"><p><?php echo esc_html( $bundle->get_error_message() ); ?></p></div>
                <?php else : ?>
                    <?php $this->render_package_section( __( 'Mail Objects', 'twentyi-hosting-browser' ), $bundle['email_objects'], 'render_mail_objects' ); ?>
                    <?php $this->render_package_section( __( 'WordPress Settings', 'twentyi-hosting-browser' ), $bundle['wordpress_settings'], 'render_wordpress_settings' ); ?>
                    <?php $this->render_package_section( __( 'WordPress Administrators', 'twentyi-hosting-browser' ), $bundle['wordpress_admins'], 'render_wordpress_admins' ); ?>
                    <?php $this->render_package_section( __( 'FTP Users', 'twentyi-hosting-browser' ), $bundle['ftp_users'], 'render_ftp_users' ); ?>
                    <?php $this->render_package_section( __( 'Cache Report', 'twentyi-hosting-browser' ), $bundle['cache_report'], 'render_cache_report' ); ?>
                    <?php $this->render_package_section( __( 'Malware Scan', 'twentyi-hosting-browser' ), $bundle['malware_scan'], 'render_malware_scan' ); ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render a package section with graceful fallback messaging.
     *
     * @param string   $title Section title.
     * @param array    $section Section payload.
     * @param callable $renderer Renderer method name.
     */
    protected function render_package_section( $title, $section, $renderer ) {
        ?>
        <div style="margin-top:24px; background:#fff; border:1px solid #dcdcde; padding:16px 20px;">
            <h2 style="margin-top:0;"><?php echo esc_html( $title ); ?></h2>
            <?php if ( empty( $section['success'] ) ) : ?>
                <p><?php echo esc_html( $section['message'] ); ?></p>
            <?php else : ?>
                <?php call_user_func( [ $this, $renderer ], $section['data'] ); ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Summary box.
     *
     * @param array $summary Package summary data.
     */
    protected function render_package_summary_box( $summary ) {
        $names       = isset( $summary['names'] ) && is_array( $summary['names'] ) ? $summary['names'] : [];
        $stack_users = isset( $summary['stackUsers'] ) && is_array( $summary['stackUsers'] ) ? $summary['stackUsers'] : [];
        ?>
        <div style="margin-top:16px; background:#fff; border:1px solid #dcdcde; padding:16px 20px;">
            <h2 style="margin-top:0;"><?php esc_html_e( 'Package Summary', 'twentyi-hosting-browser' ); ?></h2>
            <table class="widefat striped" style="max-width:920px;">
                <tbody>
                    <tr>
                        <th style="width:220px;"><?php esc_html_e( 'Primary Domain', 'twentyi-hosting-browser' ); ?></th>
                        <td><strong><?php echo esc_html( $summary['name'] ?? '—' ); ?></strong></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Package ID', 'twentyi-hosting-browser' ); ?></th>
                        <td><code><?php echo esc_html( $summary['id'] ?? '—' ); ?></code></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Domains', 'twentyi-hosting-browser' ); ?></th>
                        <td><?php echo ! empty( $names ) ? '<code>' . esc_html( implode( ', ', $names ) ) . '</code>' : '—'; ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Package Type', 'twentyi-hosting-browser' ); ?></th>
                        <td><?php echo esc_html( $summary['packageTypeName'] ?? '—' ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Created', 'twentyi-hosting-browser' ); ?></th>
                        <td><?php echo esc_html( $this->format_date( isset( $summary['created'] ) ? (string) $summary['created'] : '' ) ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Enabled', 'twentyi-hosting-browser' ); ?></th>
                        <td><?php echo ! empty( $summary['enabled'] ) ? esc_html__( 'Yes', 'twentyi-hosting-browser' ) : esc_html__( 'No', 'twentyi-hosting-browser' ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'StackCP Users', 'twentyi-hosting-browser' ); ?></th>
                        <td><?php echo ! empty( $stack_users ) ? '<code>' . esc_html( implode( ', ', $stack_users ) ) . '</code>' : '—'; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render mailbox management box.
     *
     * @param array $summary Package summary.
     * @param array $bundle Package detail bundle.
     */
    protected function render_mailbox_management_box( $summary, $bundle = [] ) {
        if ( empty( $summary ) || ! is_array( $summary ) ) {
            return;
        }

        $package_id = isset( $summary['id'] ) ? (string) $summary['id'] : '';
        $domains    = $this->extract_package_domains( $summary );
        ?>
        <div style="margin-top:24px; background:#fff; border:1px solid #dcdcde; padding:16px 20px;">
            <h2 style="margin-top:0;"><?php esc_html_e( 'Mailbox Management', 'twentyi-hosting-browser' ); ?></h2>
            <p><?php esc_html_e( 'Create a mailbox or an email forwarder for this hosting package. 20i documents mailbox and forwarder creation through the package email endpoint.', 'twentyi-hosting-browser' ); ?></p>

            <?php if ( empty( $domains ) ) : ?>
                <div class="notice notice-warning inline"><p><?php esc_html_e( 'No package domains were found, so mailbox actions are unavailable until the package has at least one domain.', 'twentyi-hosting-browser' ); ?></p></div>
            <?php else : ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:16px; align-items:start;">
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="border:1px solid #dcdcde; padding:16px;">
                        <h3 style="margin-top:0;"><?php esc_html_e( 'Create Mailbox', 'twentyi-hosting-browser' ); ?></h3>
                        <input type="hidden" name="action" value="twentyi_hosting_browser_package_action" />
                        <input type="hidden" name="package_id" value="<?php echo esc_attr( $package_id ); ?>" />
                        <input type="hidden" name="package_action" value="create_mailbox" />
                        <?php wp_nonce_field( 'twentyi_hosting_browser_package_action' ); ?>

                        <p>
                            <label for="twentyi-mail-domain"><strong><?php esc_html_e( 'Domain', 'twentyi-hosting-browser' ); ?></strong></label><br />
                            <select id="twentyi-mail-domain" name="mail_domain" class="regular-text">
                                <?php foreach ( $domains as $domain ) : ?>
                                    <option value="<?php echo esc_attr( $domain ); ?>"><?php echo esc_html( $domain ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>

                        <p>
                            <label for="twentyi-mail-local"><strong><?php esc_html_e( 'Mailbox Prefix', 'twentyi-hosting-browser' ); ?></strong></label><br />
                            <input id="twentyi-mail-local" type="text" name="mail_local" class="regular-text" placeholder="info" required />
                        </p>

                        <p>
                            <label for="twentyi-mail-password"><strong><?php esc_html_e( 'Mailbox Password', 'twentyi-hosting-browser' ); ?></strong></label><br />
                            <input id="twentyi-mail-password" type="text" name="mail_password" class="regular-text code" required />
                        </p>

                        <p>
                            <label><input type="checkbox" name="mail_send" value="1" checked /> <?php esc_html_e( 'Allow sending', 'twentyi-hosting-browser' ); ?></label><br />
                            <label><input type="checkbox" name="mail_receive" value="1" checked /> <?php esc_html_e( 'Allow receiving', 'twentyi-hosting-browser' ); ?></label>
                        </p>

                        <p class="description"><?php esc_html_e( 'This creates a real mailbox on the selected package domain.', 'twentyi-hosting-browser' ); ?></p>
                        <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Create Mailbox', 'twentyi-hosting-browser' ); ?></button></p>
                    </form>

                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="border:1px solid #dcdcde; padding:16px;">
                        <h3 style="margin-top:0;"><?php esc_html_e( 'Create Forwarder', 'twentyi-hosting-browser' ); ?></h3>
                        <input type="hidden" name="action" value="twentyi_hosting_browser_package_action" />
                        <input type="hidden" name="package_id" value="<?php echo esc_attr( $package_id ); ?>" />
                        <input type="hidden" name="package_action" value="create_forwarder" />
                        <?php wp_nonce_field( 'twentyi_hosting_browser_package_action' ); ?>

                        <p>
                            <label for="twentyi-forward-domain"><strong><?php esc_html_e( 'Domain', 'twentyi-hosting-browser' ); ?></strong></label><br />
                            <select id="twentyi-forward-domain" name="mail_domain" class="regular-text">
                                <?php foreach ( $domains as $domain ) : ?>
                                    <option value="<?php echo esc_attr( $domain ); ?>"><?php echo esc_html( $domain ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>

                        <p>
                            <label for="twentyi-forward-local"><strong><?php esc_html_e( 'Forwarder Prefix', 'twentyi-hosting-browser' ); ?></strong></label><br />
                            <input id="twentyi-forward-local" type="text" name="mail_local" class="regular-text" placeholder="enquiries" required />
                        </p>

                        <p>
                            <label for="twentyi-forward-remote"><strong><?php esc_html_e( 'Destination Email', 'twentyi-hosting-browser' ); ?></strong></label><br />
                            <input id="twentyi-forward-remote" type="email" name="mail_remote" class="regular-text" placeholder="name@example.com" required />
                        </p>

                        <p class="description"><?php esc_html_e( 'This creates an email forwarder using 20i’s package email endpoint.', 'twentyi-hosting-browser' ); ?></p>
                        <p><button type="submit" class="button button-secondary"><?php esc_html_e( 'Create Forwarder', 'twentyi-hosting-browser' ); ?></button></p>
                    </form>
                </div>

                <p class="description" style="margin-top:12px;"><?php esc_html_e( 'Tip: mailbox prefixes such as info, hello, support, and billing keep client handoff a lot tidier.', 'twentyi-hosting-browser' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render mail objects section.
     *
     * @param mixed $data Mail data payload.
     */
    protected function render_mail_objects( $data ) {
        if ( ! is_array( $data ) || empty( $data['domains'] ) || ! is_array( $data['domains'] ) ) {
            echo '<p>' . esc_html__( 'No mail objects were returned for this package.', 'twentyi-hosting-browser' ) . '</p>';
            return;
        }

        if ( ! empty( $data['source'] ) && 'package' === $data['source'] ) {
            echo '<p class="description">' . esc_html__( 'These mail objects were returned from a package-level email endpoint.', 'twentyi-hosting-browser' ) . '</p>';
        } else {
            echo '<p class="description">' . esc_html__( 'These mail objects were collected from one or more domain-level email endpoints.', 'twentyi-hosting-browser' ) . '</p>';
        }

        foreach ( $data['domains'] as $entry ) {
            $domain = isset( $entry['domain'] ) ? (string) $entry['domain'] : __( 'Mail Objects', 'twentyi-hosting-browser' );
            $rows   = $this->normalize_mail_object_rows( isset( $entry['data'] ) ? $entry['data'] : [] );

            echo '<h3 style="margin-top:20px;">' . esc_html( $domain ) . '</h3>';

            if ( ! empty( $rows ) ) {
                $this->render_rows_table( $rows );
            } else {
                $flat = isset( $entry['data'] ) && is_array( $entry['data'] ) ? $this->flatten_scalar_data( $entry['data'] ) : [];

                if ( ! empty( $flat ) ) {
                    $fallback_rows = [];
                    foreach ( $flat as $key => $value ) {
                        $fallback_rows[] = [
                            'field' => (string) $key,
                            'value' => (string) $value,
                        ];
                    }
                    $this->render_rows_table( $fallback_rows );
                } else {
                    echo '<p>' . esc_html__( 'No mail rows were returned for this domain.', 'twentyi-hosting-browser' ) . '</p>';
                }
            }
        }
    }

    /**
     * Render WordPress settings section.
     *
     * @param mixed $data Endpoint data.
     */

    /**
     * Render a domain search helper on the create page.
     *
     * @param string               $search_domain Search term.
     * @param array|WP_Error|null  $search_result Search result.
     */
    protected function render_domain_search_box( $search_domain, $search_result = null ) {
        ?>
        <div style="margin:16px 0 24px; background:#fff; border:1px solid #dcdcde; padding:16px 20px;">
            <h2 style="margin-top:0;"><?php esc_html_e( 'Domain Search', 'twentyi-hosting-browser' ); ?></h2>
            <p><?php esc_html_e( 'Check whether a domain looks available before you create the hosting package.', 'twentyi-hosting-browser' ); ?></p>

            <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
                <input type="hidden" name="page" value="<?php echo esc_attr( self::CREATE_SLUG ); ?>" />
                <div>
                    <label for="twentyi-domain-lookup"><strong><?php esc_html_e( 'Domain', 'twentyi-hosting-browser' ); ?></strong></label><br />
                    <input type="text" id="twentyi-domain-lookup" name="domain_lookup" class="regular-text" placeholder="example.com" value="<?php echo esc_attr( $search_domain ); ?>" />
                </div>
                <div>
                    <button type="submit" class="button button-secondary"><?php esc_html_e( 'Search Domain', 'twentyi-hosting-browser' ); ?></button>
                </div>
            </form>

            <?php if ( '' !== $search_domain ) : ?>
                <div style="margin-top:16px;">
                    <?php if ( is_wp_error( $search_result ) ) : ?>
                        <div class="notice notice-error inline"><p><?php echo esc_html( $search_result->get_error_message() ); ?></p></div>
                    <?php elseif ( is_array( $search_result ) ) : ?>
                        <?php
                        $normalized = $this->normalize_domain_search_result( $search_domain, $search_result );
                        $badge_bg   = '#f0f0f1';
                        $badge_text = __( 'Result returned', 'twentyi-hosting-browser' );

                        if ( true === $normalized['available'] ) {
                            $badge_bg   = '#d1e7dd';
                            $badge_text = __( 'Looks available', 'twentyi-hosting-browser' );
                        } elseif ( false === $normalized['available'] ) {
                            $badge_bg   = '#f8d7da';
                            $badge_text = __( 'Looks unavailable', 'twentyi-hosting-browser' );
                        }
                        ?>
                        <p style="margin:0 0 10px;"><strong><?php echo esc_html( $normalized['domain'] ); ?></strong>
                            <span style="display:inline-block; margin-left:8px; padding:4px 8px; border-radius:999px; background:<?php echo esc_attr( $badge_bg ); ?>;"><?php echo esc_html( $badge_text ); ?></span>
                        </p>

                        <?php if ( ! empty( $normalized['summary'] ) ) : ?>
                            <p><?php echo esc_html( $normalized['summary'] ); ?></p>
                        <?php endif; ?>

                        <?php if ( ! empty( $normalized['suggestions'] ) ) : ?>
                            <p><strong><?php esc_html_e( 'Suggestions', 'twentyi-hosting-browser' ); ?>:</strong><br />
                                <code><?php echo esc_html( implode( ', ', $normalized['suggestions'] ) ); ?></code>
                            </p>
                        <?php endif; ?>

                        <?php if ( ! empty( $normalized['fields'] ) ) : ?>
                            <?php $this->render_rows_table( $normalized['fields'] ); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render a domain and DNS onboarding box for a package.
     *
     * @param array $summary Package summary.
     */
    protected function render_domain_dns_onboarding_box( $summary ) {
        if ( empty( $summary ) || ! is_array( $summary ) ) {
            return;
        }

        $domains = $this->extract_package_domains( $summary );

        if ( empty( $domains ) ) {
            return;
        }
        ?>
        <div style="margin-top:24px; background:#fff; border:1px solid #dcdcde; padding:16px 20px;">
            <h2 style="margin-top:0;"><?php esc_html_e( 'Domain & DNS Onboarding', 'twentyi-hosting-browser' ); ?></h2>
            <p><?php esc_html_e( 'Check nameserver readiness, review public DNS, and copy a clean Google Workspace preset when you need one.', 'twentyi-hosting-browser' ); ?></p>

            <div style="margin-bottom:16px; padding:12px 14px; background:#f6f7f7; border:1px solid #dcdcde;">
                <strong><?php esc_html_e( '20i nameservers', 'twentyi-hosting-browser' ); ?>:</strong>
                <code><?php echo esc_html( implode( ', ', $this->get_twentyi_nameservers() ) ); ?></code>
            </div>

            <?php foreach ( $domains as $domain ) : ?>
                <?php
                $status  = $this->get_domain_nameserver_status( $domain );
                $records = $this->get_public_dns_snapshot( $domain );
                ?>
                <div style="margin:18px 0 0; padding:16px; border:1px solid #dcdcde;">
                    <h3 style="margin-top:0;"><?php echo esc_html( $domain ); ?></h3>

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:14px;">
                        <div style="padding:12px; border:1px solid #dcdcde; background:#f6f7f7;">
                            <strong><?php esc_html_e( 'Nameserver status', 'twentyi-hosting-browser' ); ?></strong>
                            <div style="margin-top:6px;">
                                <?php if ( true === $status['using_twentyi'] ) : ?>
                                    <span style="display:inline-block; padding:4px 8px; border-radius:999px; background:#d1e7dd;"><?php esc_html_e( 'Using 20i nameservers', 'twentyi-hosting-browser' ); ?></span>
                                <?php elseif ( false === $status['using_twentyi'] ) : ?>
                                    <span style="display:inline-block; padding:4px 8px; border-radius:999px; background:#f8d7da;"><?php esc_html_e( 'Not using 20i nameservers', 'twentyi-hosting-browser' ); ?></span>
                                <?php else : ?>
                                    <span style="display:inline-block; padding:4px 8px; border-radius:999px; background:#f0f0f1;"><?php esc_html_e( 'Could not confirm', 'twentyi-hosting-browser' ); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ( ! empty( $status['nameservers'] ) ) : ?>
                                <p style="margin:10px 0 0;"><code><?php echo esc_html( implode( ', ', $status['nameservers'] ) ); ?></code></p>
                            <?php else : ?>
                                <p style="margin:10px 0 0;"><?php esc_html_e( 'No public NS records were returned by the server.', 'twentyi-hosting-browser' ); ?></p>
                            <?php endif; ?>
                        </div>

                        <div style="padding:12px; border:1px solid #dcdcde; background:#f6f7f7;">
                            <strong><?php esc_html_e( 'Free SSL readiness', 'twentyi-hosting-browser' ); ?></strong>
                            <p style="margin:10px 0 0;">
                                <?php
                                if ( true === $status['ssl_ready'] ) {
                                    esc_html_e( 'Ready for 20i free SSL checks.', 'twentyi-hosting-browser' );
                                } elseif ( false === $status['using_twentyi'] ) {
                                    esc_html_e( 'Point the domain to 20i nameservers first.', 'twentyi-hosting-browser' );
                                } elseif ( ! empty( $status['acme_challenge_cname'] ) ) {
                                    esc_html_e( 'Remove the public _acme-challenge CNAME before requesting the free SSL.', 'twentyi-hosting-browser' );
                                } else {
                                    esc_html_e( 'The plugin could not fully verify SSL readiness yet.', 'twentyi-hosting-browser' );
                                }
                                ?>
                            </p>
                        </div>

                        <div style="padding:12px; border:1px solid #dcdcde; background:#f6f7f7;">
                            <strong><?php esc_html_e( 'Public WWW target', 'twentyi-hosting-browser' ); ?></strong>
                            <p style="margin:10px 0 0;"><?php echo ! empty( $records['www_target'] ) ? esc_html( $records['www_target'] ) : esc_html__( 'No A, AAAA, or CNAME found for www.', 'twentyi-hosting-browser' ); ?></p>
                        </div>
                    </div>

                    <?php if ( ! empty( $status['acme_challenge_cname'] ) ) : ?>
                        <div class="notice notice-warning inline"><p>
                            <?php
                            printf(
                                esc_html__( 'Found _acme-challenge CNAME: %s', 'twentyi-hosting-browser' ),
                                $status['acme_challenge_cname']
                            );
                            ?>
                        </p></div>
                    <?php endif; ?>

                    <?php if ( ! empty( $records['rows'] ) ) : ?>
                        <h4 style="margin:16px 0 8px;"><?php esc_html_e( 'Public DNS Snapshot', 'twentyi-hosting-browser' ); ?></h4>
                        <?php $this->render_public_dns_rows_table( $records['rows'] ); ?>
                    <?php else : ?>
                        <p><?php esc_html_e( 'The server did not return public DNS rows for this domain.', 'twentyi-hosting-browser' ); ?></p>
                    <?php endif; ?>

                    <h4 style="margin:16px 0 8px;"><?php esc_html_e( 'Google Workspace Preset', 'twentyi-hosting-browser' ); ?></h4>
                    <p class="description"><?php esc_html_e( 'This is a copy-ready zone snippet using Google’s current single MX pattern plus the standard SPF include. Remove conflicting MX records first.', 'twentyi-hosting-browser' ); ?></p>
                    <textarea readonly rows="6" class="large-text code"><?php echo esc_textarea( $this->build_google_workspace_zone_snippet( $domain ) ); ?></textarea>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render public DNS rows in a table.
     *
     * @param array $rows Row data.
     */
    protected function render_public_dns_rows_table( $rows ) {
        ?>
        <table class="widefat striped fixed">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Host', 'twentyi-hosting-browser' ); ?></th>
                    <th><?php esc_html_e( 'Type', 'twentyi-hosting-browser' ); ?></th>
                    <th><?php esc_html_e( 'Value', 'twentyi-hosting-browser' ); ?></th>
                    <th><?php esc_html_e( 'Notes', 'twentyi-hosting-browser' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $rows as $row ) : ?>
                    <tr>
                        <td><code><?php echo esc_html( $row['host'] ); ?></code></td>
                        <td><?php echo esc_html( $row['type'] ); ?></td>
                        <td style="word-break:break-word;"><?php echo esc_html( $row['value'] ); ?></td>
                        <td><?php echo ! empty( $row['notes'] ) ? esc_html( $row['notes'] ) : '—'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render client handoff box.
     *
     * @param array|WP_Error $summary Package summary.
     * @param array          $bundle Package detail bundle.
     */
    protected function render_client_handoff_box( $summary, $bundle = [] ) {
        if ( is_wp_error( $summary ) || ! is_array( $summary ) ) {
            return;
        }

        $assigned_users = $this->get_stack_users_for_package_summary( $summary );
        $primary_domain = isset( $summary['name'] ) ? (string) $summary['name'] : '';
        $package_label  = isset( $summary['label'] ) ? (string) $summary['label'] : '';
        $package_id     = isset( $summary['id'] ) ? (string) $summary['id'] : '';
        $site_url       = '';

        if ( isset( $bundle['wordpress_settings']['success'] ) && ! empty( $bundle['wordpress_settings']['success'] ) && ! empty( $bundle['wordpress_settings']['data']['siteurl'] ) ) {
            $site_url = (string) $bundle['wordpress_settings']['data']['siteurl'];
        }
        ?>
        <div style="margin-top:24px; background:#fff; border:1px solid #dcdcde; padding:16px 20px;">
            <h2 style="margin-top:0;"><?php esc_html_e( 'Client Handoff', 'twentyi-hosting-browser' ); ?></h2>
            <p><?php esc_html_e( 'Use this section to hand over StackCP access details to your client once the package is ready.', 'twentyi-hosting-browser' ); ?></p>

            <?php if ( empty( $assigned_users ) ) : ?>
                <div class="notice notice-warning inline"><p><?php esc_html_e( 'No StackCP user is currently assigned to this package. You can still create a package with a StackCP user during provisioning, or manage package access in My20i.', 'twentyi-hosting-browser' ); ?></p></div>
                <p class="description"><?php esc_html_e( 'This package can still be viewed and managed, but the handoff template becomes much more useful once at least one StackCP user is linked.', 'twentyi-hosting-browser' ); ?></p>
                <p><a class="button button-secondary" href="<?php echo esc_url( add_query_arg( [ 'page' => self::STACK_USERS_SLUG, 'assign_package' => $package_id ], admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Create StackCP User for this Package', 'twentyi-hosting-browser' ); ?></a></p>
            <?php else : ?>
                <?php foreach ( $assigned_users as $stack_user ) : ?>
                    <div style="margin:16px 0; padding:16px; border:1px solid #dcdcde; background:#f6f7f7;">
                        <p style="margin-top:0;"><strong><?php echo esc_html( $stack_user['name'] ?: $stack_user['label'] ); ?></strong>
                        <?php if ( ! empty( $stack_user['email'] ) ) : ?>
                            <br /><span class="description"><?php echo esc_html( $stack_user['email'] ); ?></span>
                        <?php endif; ?>
                        <br /><code><?php echo esc_html( $stack_user['id'] ); ?></code></p>

                        <p><strong><?php esc_html_e( 'Suggested subject:', 'twentyi-hosting-browser' ); ?></strong> <?php echo esc_html( $this->build_handoff_subject( $primary_domain ?: $package_label ?: $package_id ) ); ?></p>
                        <textarea readonly rows="12" class="large-text code"><?php echo esc_textarea( $this->build_stackcp_handoff_message( $stack_user, [ $summary ], [ 'site_url' => $site_url ] ) ); ?></textarea>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render package actions box.
     *
     * @param string $package_id Package ID.
     * @param array  $bundle Package detail bundle.
     */
    protected function render_package_actions_box( $package_id, $bundle = [] ) {
        $ftp_users = [];

        if ( isset( $bundle['ftp_users']['success'] ) && ! empty( $bundle['ftp_users']['success'] ) && ! empty( $bundle['ftp_users']['data'] ) ) {
            $ftp_users = $this->extract_ftp_user_options( $bundle['ftp_users']['data'] );
        }
        ?>
        <div style="margin-top:24px; background:#fff; border:1px solid #dcdcde; padding:16px 20px;">
            <h2 style="margin-top:0;"><?php esc_html_e( 'Manage Package Actions', 'twentyi-hosting-browser' ); ?></h2>
            <p><?php esc_html_e( 'Run common maintenance actions for this package from inside WordPress.', 'twentyi-hosting-browser' ); ?></p>

            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; margin-bottom:16px;">
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;">
                    <input type="hidden" name="action" value="twentyi_hosting_browser_package_action" />
                    <input type="hidden" name="package_id" value="<?php echo esc_attr( $package_id ); ?>" />
                    <input type="hidden" name="package_action" value="purge_cache" />
                    <?php wp_nonce_field( 'twentyi_hosting_browser_package_action' ); ?>
                    <?php submit_button( __( 'Purge Cache', 'twentyi-hosting-browser' ), 'secondary', 'submit', false ); ?>
                </form>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;">
                    <input type="hidden" name="action" value="twentyi_hosting_browser_package_action" />
                    <input type="hidden" name="package_id" value="<?php echo esc_attr( $package_id ); ?>" />
                    <input type="hidden" name="package_action" value="run_malware_scan" />
                    <?php wp_nonce_field( 'twentyi_hosting_browser_package_action' ); ?>
                    <?php submit_button( __( 'Run Malware Scan', 'twentyi-hosting-browser' ), 'secondary', 'submit', false ); ?>
                </form>
            </div>

            <hr />
            <h3><?php esc_html_e( 'FTP Access', 'twentyi-hosting-browser' ); ?></h3>
            <?php if ( empty( $ftp_users ) ) : ?>
                <p><?php esc_html_e( 'Load package FTP users first to manage temporary FTP access here.', 'twentyi-hosting-browser' ); ?></p>
            <?php else : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="twentyi_hosting_browser_package_action" />
                    <input type="hidden" name="package_id" value="<?php echo esc_attr( $package_id ); ?>" />
                    <?php wp_nonce_field( 'twentyi_hosting_browser_package_action' ); ?>

                    <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
                        <div>
                            <label for="twentyi_ftp_user"><strong><?php esc_html_e( 'FTP User', 'twentyi-hosting-browser' ); ?></strong></label><br />
                            <select name="ftp_user" id="twentyi_ftp_user">
                                <?php foreach ( $ftp_users as $ftp_user ) : ?>
                                    <option value="<?php echo esc_attr( $ftp_user ); ?>"><?php echo esc_html( $ftp_user ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="twentyi_unlock_minutes"><strong><?php esc_html_e( 'Unlock For', 'twentyi-hosting-browser' ); ?></strong></label><br />
                            <select name="unlock_minutes" id="twentyi_unlock_minutes">
                                <option value="15"><?php esc_html_e( '15 minutes', 'twentyi-hosting-browser' ); ?></option>
                                <option value="60" selected><?php esc_html_e( '1 hour', 'twentyi-hosting-browser' ); ?></option>
                                <option value="240"><?php esc_html_e( '4 hours', 'twentyi-hosting-browser' ); ?></option>
                                <option value="1440"><?php esc_html_e( '24 hours', 'twentyi-hosting-browser' ); ?></option>
                            </select>
                        </div>

                        <div>
                            <button type="submit" class="button button-secondary" name="package_action" value="unlock_ftp"><?php esc_html_e( 'Unlock FTP', 'twentyi-hosting-browser' ); ?></button>
                            <button type="submit" class="button" name="package_action" value="enable_ftp"><?php esc_html_e( 'Enable FTP', 'twentyi-hosting-browser' ); ?></button>
                            <button type="submit" class="button" name="package_action" value="disable_ftp"><?php esc_html_e( 'Disable FTP', 'twentyi-hosting-browser' ); ?></button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render the destructive package deletion box.
     *
     * @param array $summary Package summary.
     */
    protected function render_delete_site_box( $summary ) {
        if ( empty( $summary ) || ! is_array( $summary ) ) {
            return;
        }

        $package_id     = isset( $summary['id'] ) ? (string) $summary['id'] : '';
        $primary_domain = isset( $summary['name'] ) ? (string) $summary['name'] : '';
        $confirm_label  = '' !== $primary_domain ? $primary_domain : $package_id;

        if ( '' === $package_id ) {
            return;
        }
        ?>
        <div style="margin-top:24px; background:#fff; border:1px solid #d63638; border-left:4px solid #d63638; padding:16px 20px;">
            <h2 style="margin-top:0; color:#b32d2e;"><?php esc_html_e( 'Danger Zone: Delete Site', 'twentyi-hosting-browser' ); ?></h2>
            <p><?php esc_html_e( 'Delete the hosting package from 20i. This is intended for admin use only and may remove the website, files, databases, mailboxes, and related hosting data depending on your 20i package type.', 'twentyi-hosting-browser' ); ?></p>
            <p><strong><?php esc_html_e( 'This cannot be undone from this plugin.', 'twentyi-hosting-browser' ); ?></strong></p>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Final warning: this will ask 20i to delete this hosting package. Continue?', 'twentyi-hosting-browser' ) ); ?>');">
                <input type="hidden" name="action" value="twentyi_hosting_browser_package_action" />
                <input type="hidden" name="package_id" value="<?php echo esc_attr( $package_id ); ?>" />
                <input type="hidden" name="package_action" value="delete_package" />
                <?php wp_nonce_field( 'twentyi_hosting_browser_package_action' ); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Package', 'twentyi-hosting-browser' ); ?></th>
                            <td>
                                <strong><?php echo esc_html( $confirm_label ); ?></strong><br />
                                <code><?php echo esc_html( $package_id ); ?></code>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="twentyi-delete-confirm"><?php esc_html_e( 'Type DELETE', 'twentyi-hosting-browser' ); ?></label></th>
                            <td><input id="twentyi-delete-confirm" type="text" name="delete_confirm" class="regular-text code" autocomplete="off" placeholder="DELETE" required /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="twentyi-package-match"><?php esc_html_e( 'Confirm Package', 'twentyi-hosting-browser' ); ?></label></th>
                            <td>
                                <input id="twentyi-package-match" type="text" name="package_match" class="regular-text code" autocomplete="off" placeholder="<?php echo esc_attr( $confirm_label ); ?>" required />
                                <p class="description">
                                    <?php
                                    printf(
                                        /* translators: %s: primary domain or package ID. */
                                        esc_html__( 'Type %s or the package ID exactly to confirm.', 'twentyi-hosting-browser' ),
                                        '<code>' . esc_html( $confirm_label ) . '</code>'
                                    );
                                    ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button( __( 'Delete Site from 20i', 'twentyi-hosting-browser' ), 'delete', 'submit', false ); ?>
            </form>
        </div>
        <?php
    }

    protected function render_wordpress_settings( $data ) {
        if ( ! is_array( $data ) || empty( $data ) ) {
            echo '<p>' . esc_html__( 'No WordPress settings were returned for this package.', 'twentyi-hosting-browser' ) . '</p>';
            return;
        }

        $rows = [];

        foreach ( [ 'siteurl', 'home', 'blogname', 'blogdescription', 'site_icon_url', 'admin_email', 'version' ] as $key ) {
            if ( isset( $data[ $key ] ) && '' !== (string) $data[ $key ] ) {
                $rows[ $key ] = $data[ $key ];
            }
        }

        if ( empty( $rows ) ) {
            foreach ( $data as $key => $value ) {
                if ( is_scalar( $value ) ) {
                    $rows[ $key ] = $value;
                }
            }
        }

        if ( empty( $rows ) ) {
            echo '<p>' . esc_html__( 'The API returned data, but it was not in a simple format to display here.', 'twentyi-hosting-browser' ) . '</p>';
            echo '<pre style="overflow:auto; max-height:280px;">' . esc_html( wp_json_encode( $data, JSON_PRETTY_PRINT ) ) . '</pre>';
            return;
        }

        echo '<table class="widefat striped" style="max-width:920px;"><tbody>';
        foreach ( $rows as $key => $value ) {
            echo '<tr>';
            echo '<th style="width:220px;">' . esc_html( ucwords( str_replace( '_', ' ', (string) $key ) ) ) . '</th>';
            echo '<td>' . esc_html( (string) $value ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    /**
     * Render WordPress admins section.
     *
     * @param mixed $data Endpoint data.
     */
    protected function render_wordpress_admins( $data ) {
        if ( ! is_array( $data ) || empty( $data ) ) {
            echo '<p>' . esc_html__( 'No WordPress administrators were returned for this package.', 'twentyi-hosting-browser' ) . '</p>';
            return;
        }

        $rows = $this->normalize_collection_rows( $data, [ 'username', 'user_login', 'email', 'user_email', 'display_name', 'role' ] );

        if ( empty( $rows ) ) {
            echo '<pre style="overflow:auto; max-height:280px;">' . esc_html( wp_json_encode( $data, JSON_PRETTY_PRINT ) ) . '</pre>';
            return;
        }

        $this->render_rows_table( $rows );
    }

    /**
     * Render FTP users section.
     *
     * @param mixed $data Endpoint data.
     */
    protected function render_ftp_users( $data ) {
        if ( ! is_array( $data ) || empty( $data ) ) {
            echo '<p>' . esc_html__( 'No FTP users were returned for this package.', 'twentyi-hosting-browser' ) . '</p>';
            return;
        }

        $rows = $this->normalize_collection_rows( $data, [ 'username', 'user', 'path', 'enabled', 'UnlockedUntil' ] );

        if ( empty( $rows ) ) {
            echo '<pre style="overflow:auto; max-height:280px;">' . esc_html( wp_json_encode( $data, JSON_PRETTY_PRINT ) ) . '</pre>';
            return;
        }

        $this->render_rows_table( $rows );
    }

    /**
     * Render cache report section.
     *
     * @param mixed $data Endpoint data.
     */
    protected function render_cache_report( $data ) {
        if ( ! is_array( $data ) || empty( $data ) ) {
            echo '<p>' . esc_html__( 'No cache report data was returned for this package.', 'twentyi-hosting-browser' ) . '</p>';
            return;
        }

        $flattened = $this->flatten_scalar_data( $data );

        if ( empty( $flattened ) ) {
            echo '<pre style="overflow:auto; max-height:280px;">' . esc_html( wp_json_encode( $data, JSON_PRETTY_PRINT ) ) . '</pre>';
            return;
        }

        echo '<table class="widefat striped" style="max-width:920px;"><tbody>';
        foreach ( $flattened as $key => $value ) {
            echo '<tr>';
            echo '<th style="width:260px;">' . esc_html( $key ) . '</th>';
            echo '<td>' . esc_html( (string) $value ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    /**
     * Render malware section.
     *
     * @param mixed $data Endpoint data.
     */
    protected function render_malware_scan( $data ) {
        if ( ! is_array( $data ) || empty( $data ) ) {
            echo '<p>' . esc_html__( 'No malware scan data was returned for this package.', 'twentyi-hosting-browser' ) . '</p>';
            return;
        }

        $summary_rows = [];
        foreach ( [ 'isInfected', 'infectedFiles', 'warnedFiles', 'lastScan', 'lastScanned', 'status' ] as $key ) {
            if ( array_key_exists( $key, $data ) && ! is_array( $data[ $key ] ) ) {
                $summary_rows[ $key ] = is_bool( $data[ $key ] ) ? ( $data[ $key ] ? 'true' : 'false' ) : $data[ $key ];
            }
        }

        if ( ! empty( $summary_rows ) ) {
            echo '<table class="widefat striped" style="max-width:920px; margin-bottom:16px;"><tbody>';
            foreach ( $summary_rows as $key => $value ) {
                echo '<tr>';
                echo '<th style="width:220px;">' . esc_html( ucwords( str_replace( '_', ' ', (string) $key ) ) ) . '</th>';
                echo '<td>' . esc_html( (string) $value ) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }

        if ( ! empty( $data['files'] ) && is_array( $data['files'] ) ) {
            echo '<h3>' . esc_html__( 'Flagged Files', 'twentyi-hosting-browser' ) . '</h3>';
            $rows = $this->normalize_collection_rows( $data['files'], [ 'file', 'path', 'signature', 'risk', 'status' ] );
            if ( ! empty( $rows ) ) {
                $this->render_rows_table( $rows );
                return;
            }
        }

        if ( empty( $summary_rows ) ) {
            echo '<pre style="overflow:auto; max-height:280px;">' . esc_html( wp_json_encode( $data, JSON_PRETTY_PRINT ) ) . '</pre>';
        }
    }

    /**
     * Refresh packages list.
     */
    public function handle_refresh() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to do that.', 'twentyi-hosting-browser' ) );
        }

        check_admin_referer( 'twentyi_hosting_browser_refresh' );
        $this->clear_caches();
        $this->add_activity_log( 'packages_refresh', __( 'Hosting package list cache was refreshed.', 'twentyi-hosting-browser' ), [], 'success' );

        wp_safe_redirect( admin_url( 'admin.php?page=' . self::MENU_SLUG . '&refreshed=1' ) );
        exit;
    }

    /**
     * Clear local activity log.
     */
    public function handle_clear_activity_log() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to do that.', 'twentyi-hosting-browser' ) );
        }

        check_admin_referer( 'twentyi_hosting_browser_clear_activity_log' );
        $this->clear_activity_log();

        wp_safe_redirect( admin_url( 'admin.php?page=' . self::ACTIVITY_LOG_SLUG . '&log_cleared=1' ) );
        exit;
    }

    /**
     * Handle frontend website request submissions.
     */
    public function handle_frontend_request() {
        if ( ! $this->is_frontend_form_enabled() && ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Website requests are not currently enabled.', 'twentyi-hosting-browser' ) );
        }

        if ( $this->frontend_requires_login() && ! is_user_logged_in() ) {
            wp_safe_redirect( wp_login_url( wp_get_referer() ?: home_url( '/' ) ) );
            exit;
        }

        check_admin_referer( 'twentyi_hosting_browser_frontend_request', 'twentyi_nonce' );

        $referer = wp_get_referer() ?: home_url( '/' );

        if ( ! empty( $_POST['website_url'] ) ) {
            $this->redirect_frontend_request( $referer, 'success' );
        }

        $ip_key = 'twentyi_frontend_request_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );
        if ( get_transient( $ip_key ) ) {
            $this->redirect_frontend_request( $referer, 'success' );
        }
        set_transient( $ip_key, 1, MINUTE_IN_SECONDS );

        $business_name = sanitize_text_field( wp_unslash( $_POST['business_name'] ?? '' ) );
        $contact_name  = sanitize_text_field( wp_unslash( $_POST['contact_name'] ?? '' ) );
        $domain_name   = $this->sanitize_domain( wp_unslash( $_POST['domain_name'] ?? '' ) );
        $email         = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
        $phone         = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
        $brief         = sanitize_textarea_field( wp_unslash( $_POST['website_brief'] ?? '' ) );
        $terms         = ! empty( $_POST['terms_agreed'] );
        $posted_type   = sanitize_text_field( wp_unslash( $_POST['package_type'] ?? '' ) );
        $default_type  = $this->get_frontend_package_type();
        $package_type  = $this->resolve_frontend_submitted_package_type( $posted_type );

        if ( '' === $business_name || '' === $contact_name || '' === $domain_name || ! is_email( $email ) || ! $terms ) {
            $this->redirect_frontend_request( $referer, 'error' );
        }

        $request_id = wp_insert_post(
            [
                'post_type'   => self::REQUEST_POST_TYPE,
                'post_status' => 'pending',
                'post_title'  => sprintf(
                    /* translators: 1: business name, 2: domain */
                    __( '%1$s - %2$s', 'twentyi-hosting-browser' ),
                    $business_name,
                    $domain_name
                ),
            ],
            true
        );

        if ( is_wp_error( $request_id ) ) {
            $this->redirect_frontend_request( $referer, 'error' );
        }

        $request_id = (int) $request_id;
        update_post_meta( $request_id, 'business_name', $business_name );
        update_post_meta( $request_id, 'contact_name', $contact_name );
        update_post_meta( $request_id, 'domain_name', $domain_name );
        update_post_meta( $request_id, 'email', $email );
        update_post_meta( $request_id, 'phone', $phone );
        update_post_meta( $request_id, 'website_brief', $brief );
        update_post_meta( $request_id, 'package_type', $package_type );
        update_post_meta( $request_id, 'status', 'pending_review' );
        update_post_meta( $request_id, 'submitted_at', current_time( 'mysql' ) );

        $this->add_activity_log(
            'frontend_request',
            sprintf(
                /* translators: %s: domain name */
                __( 'Frontend website request received for %s.', 'twentyi-hosting-browser' ),
                $domain_name
            ),
            [
                'request_id'    => $request_id,
                'domain'        => $domain_name,
                'email'         => $email,
                'package_type'  => $package_type,
            ],
            'info'
        );

        $status = 'success';

        if ( 'auto' === $this->get_frontend_mode() ) {
            if ( '' === $package_type ) {
                update_post_meta( $request_id, 'status', 'needs_review' );
                update_post_meta( $request_id, 'last_error', __( 'Auto-create is enabled, but no package type was selected or available.', 'twentyi-hosting-browser' ) );
            } else {
                $result = $this->create_hosting_package(
                    [
                        'domain_name' => $domain_name,
                        'type'        => $package_type,
                        'label'       => $business_name,
                    ]
                );

                if ( is_wp_error( $result ) ) {
                    update_post_meta( $request_id, 'status', 'needs_review' );
                    update_post_meta( $request_id, 'last_error', $result->get_error_message() );
                    $this->add_activity_log( 'frontend_request_auto_create', $result->get_error_message(), [ 'request_id' => $request_id, 'domain' => $domain_name, 'package_type' => $package_type ], 'error' );
                } else {
                    update_post_meta( $request_id, 'status', 'created' );
                    update_post_meta( $request_id, 'package_id', $result['package_id'] );
                    update_post_meta( $request_id, 'created_result', $result['result'] );
                    $this->schedule_wordpress_admin_bootstrap( $request_id, (string) $result['package_id'], $domain_name, $email, $contact_name, $business_name );
                    $this->add_activity_log( 'frontend_request_auto_create', __( 'Frontend request auto-created a hosting package.', 'twentyi-hosting-browser' ), [ 'request_id' => $request_id, 'domain' => $domain_name, 'package_type' => $package_type, 'package_id' => $result['package_id'] ], 'success' );
                    $status = 'created';
                }
            }
        }

        $this->send_website_request_notification( $request_id );
        $this->redirect_frontend_request( $referer, $status );
    }

    /**
     * Handle request approve/reject/delete actions.
     */
    public function handle_website_request_action() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to do that.', 'twentyi-hosting-browser' ) );
        }

        $request_id = absint( $_GET['request_id'] ?? 0 );
        $action     = sanitize_key( wp_unslash( $_GET['request_action'] ?? '' ) );

        if ( ! $request_id || self::REQUEST_POST_TYPE !== get_post_type( $request_id ) ) {
            $this->redirect_requests_page_with_notice( 'error', __( 'Website request not found.', 'twentyi-hosting-browser' ) );
        }

        check_admin_referer( 'twentyi_hosting_browser_request_action_' . $request_id );

        if ( 'delete' === $action ) {
            wp_trash_post( $request_id );
            $this->add_activity_log( 'frontend_request_delete', __( 'Website request moved to Trash.', 'twentyi-hosting-browser' ), [ 'request_id' => $request_id ], 'info' );
            $this->redirect_requests_page_with_notice( 'success', __( 'Website request deleted.', 'twentyi-hosting-browser' ) );
        }

        if ( 'reject' === $action ) {
            update_post_meta( $request_id, 'status', 'rejected' );
            $this->add_activity_log( 'frontend_request_reject', __( 'Website request marked as rejected.', 'twentyi-hosting-browser' ), [ 'request_id' => $request_id ], 'info' );
            $this->redirect_requests_page_with_notice( 'success', __( 'Website request marked as rejected.', 'twentyi-hosting-browser' ) );
        }

        if ( 'bootstrap' === $action ) {
            if ( $this->wc_billing_blocks_client_actions() && $this->request_has_unhealthy_billing( $request_id ) ) {
                $redirect( 'billing_blocked' );
            }

            $package_id = sanitize_text_field( get_post_meta( $request_id, 'package_id', true ) );
            $domain     = $this->sanitize_domain( get_post_meta( $request_id, 'domain_name', true ) );
            $email      = sanitize_email( get_post_meta( $request_id, 'email', true ) );
            $name       = sanitize_text_field( get_post_meta( $request_id, 'contact_name', true ) );
            $business   = sanitize_text_field( get_post_meta( $request_id, 'business_name', true ) );

            if ( '' === $package_id || '' === $domain || ! is_email( $email ) ) {
                $this->redirect_requests_page_with_notice( 'error', __( 'This request needs a package ID, domain and valid email before admin bootstrap can run.', 'twentyi-hosting-browser' ) );
            }

            $this->schedule_wordpress_admin_bootstrap( $request_id, $package_id, $domain, $email, $name, $business, true );
            $this->redirect_requests_page_with_notice( 'success', __( 'WordPress admin bootstrap has been queued. WP-Cron will retry until the cloned site responds.', 'twentyi-hosting-browser' ) );
        }

        if ( 'welcome' === $action ) {
            $welcome_result = $this->send_client_welcome_email( $request_id, [], true );

            if ( is_wp_error( $welcome_result ) ) {
                $this->redirect_requests_page_with_notice( 'error', $welcome_result->get_error_message() );
            }

            $this->redirect_requests_page_with_notice( 'success', __( 'Client welcome email sent.', 'twentyi-hosting-browser' ) );
        }

        if ( 'approve' !== $action ) {
            $this->redirect_requests_page_with_notice( 'error', __( 'Unknown request action.', 'twentyi-hosting-browser' ) );
        }

        $domain_name  = $this->sanitize_domain( get_post_meta( $request_id, 'domain_name', true ) );
        $business     = sanitize_text_field( get_post_meta( $request_id, 'business_name', true ) );
        $package_type = sanitize_text_field( get_post_meta( $request_id, 'package_type', true ) );
        $package_type = $package_type ?: $this->get_frontend_package_type();

        if ( '' === $domain_name ) {
            update_post_meta( $request_id, 'status', 'needs_review' );
            update_post_meta( $request_id, 'last_error', __( 'Missing or invalid domain name.', 'twentyi-hosting-browser' ) );
            $this->redirect_requests_page_with_notice( 'error', __( 'Request is missing a valid domain name.', 'twentyi-hosting-browser' ) );
        }

        if ( '' === $package_type ) {
            update_post_meta( $request_id, 'status', 'needs_review' );
            update_post_meta( $request_id, 'last_error', __( 'No package type selected.', 'twentyi-hosting-browser' ) );
            $this->redirect_requests_page_with_notice( 'error', __( 'Choose a package type before approving this request.', 'twentyi-hosting-browser' ) );
        }

        $result = $this->create_hosting_package(
            [
                'domain_name' => $domain_name,
                'type'        => $package_type,
                'label'       => $business ?: $domain_name,
            ]
        );

        if ( is_wp_error( $result ) ) {
            update_post_meta( $request_id, 'status', 'needs_review' );
            update_post_meta( $request_id, 'last_error', $result->get_error_message() );
            $this->add_activity_log( 'frontend_request_approve', $result->get_error_message(), [ 'request_id' => $request_id, 'domain' => $domain_name, 'package_type' => $package_type ], 'error' );
            $this->redirect_requests_page_with_notice( 'error', $result->get_error_message() );
        }

        update_post_meta( $request_id, 'status', 'created' );
        update_post_meta( $request_id, 'package_id', $result['package_id'] );
        update_post_meta( $request_id, 'created_result', $result['result'] );
        delete_post_meta( $request_id, 'last_error' );
        $request_email = sanitize_email( get_post_meta( $request_id, 'email', true ) );
        $request_name  = sanitize_text_field( get_post_meta( $request_id, 'contact_name', true ) );
        $this->schedule_wordpress_admin_bootstrap( $request_id, (string) $result['package_id'], $domain_name, $request_email, $request_name, $business );

        $this->add_activity_log(
            'frontend_request_approve',
            sprintf(
                /* translators: %s: domain name */
                __( 'Approved website request and created hosting package for %s.', 'twentyi-hosting-browser' ),
                $domain_name
            ),
            [
                'request_id'   => $request_id,
                'domain'       => $domain_name,
                'package_type' => $package_type,
                'package_id'   => $result['package_id'],
            ],
            'success'
        );

        $this->redirect_requests_page_with_notice( 'success', __( 'Hosting package created from website request.', 'twentyi-hosting-browser' ) );
    }

    /**
     * Create a website/package in 20i.
     */
    public function handle_create_package() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to do that.', 'twentyi-hosting-browser' ) );
        }

        check_admin_referer( 'twentyi_hosting_browser_create_package' );

        $domain_name    = $this->sanitize_domain( isset( $_POST['domain_name'] ) ? wp_unslash( $_POST['domain_name'] ) : '' );
        $type           = sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) );
        $label          = sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) );
        $stack_user     = sanitize_text_field( wp_unslash( $_POST['stackUser'] ?? '' ) );
        $extra_domains  = $this->parse_domain_list( isset( $_POST['extra_domain_names'] ) ? wp_unslash( $_POST['extra_domain_names'] ) : '' );
        $document_roots = $this->parse_document_roots( isset( $_POST['document_roots'] ) ? wp_unslash( $_POST['document_roots'] ) : '' );

        if ( '' === $domain_name ) {
            $this->add_activity_log( 'package_create', __( 'Package creation failed: missing or invalid primary domain.', 'twentyi-hosting-browser' ), [], 'error' );
            $this->redirect_create_page_with_message( __( 'Please add a valid primary domain.', 'twentyi-hosting-browser' ) );
        }

        if ( '' === $type ) {
            $this->add_activity_log( 'package_create', __( 'Package creation failed: missing package type.', 'twentyi-hosting-browser' ), [ 'domain' => $domain_name ], 'error' );
            $this->redirect_create_page_with_message( __( 'Please choose a package type.', 'twentyi-hosting-browser' ) );
        }

        $payload = [
            'domain_name' => $domain_name,
            'type'        => $type,
        ];

        if ( '' !== $label ) {
            $payload['label'] = $label;
        }

        if ( ! empty( $extra_domains ) ) {
            $payload['extra_domain_names'] = $extra_domains;
        }

        if ( '' !== $stack_user ) {
            $payload['stackUser'] = $stack_user;
        }

        if ( ! empty( $document_roots ) ) {
            $payload['documentRoots'] = $document_roots;
        }

        $result = $this->request( '/reseller/*/addWeb', 'POST', $payload );

        if ( is_wp_error( $result ) ) {
            $this->add_activity_log(
                'package_create',
                $result->get_error_message(),
                [
                    'domain' => $domain_name,
                    'type'   => $type,
                ],
                'error'
            );
            $this->redirect_create_page_with_message( $result->get_error_message() );
        }

        $new_package_id = '';

        if ( isset( $result['result'] ) ) {
            $new_package_id = (string) $result['result'];
        } elseif ( isset( $result['id'] ) ) {
            $new_package_id = (string) $result['id'];
        }

        $this->clear_caches();
        $this->add_activity_log(
            'package_create',
            sprintf(
                /* translators: %s: domain name */
                __( 'Created hosting package for %s.', 'twentyi-hosting-browser' ),
                $domain_name
            ),
            [
                'package_id' => $new_package_id,
                'domain'     => $domain_name,
                'type'       => $type,
                'stack_user' => $stack_user,
            ],
            'success'
        );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page'            => self::CREATE_SLUG,
                    'created_package' => $new_package_id ?: __( 'Created', 'twentyi-hosting-browser' ),
                ],
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    /**
     * Handle StackCP user creation.
     */
    public function handle_create_stack_user() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to do that.', 'twentyi-hosting-browser' ) );
        }

        check_admin_referer( 'twentyi_hosting_browser_create_stack_user' );

        $full_name      = isset( $_POST['stack_full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['stack_full_name'] ) ) : '';
        $email          = isset( $_POST['stack_email'] ) ? sanitize_email( wp_unslash( $_POST['stack_email'] ) ) : '';
        $company        = isset( $_POST['stack_company'] ) ? sanitize_text_field( wp_unslash( $_POST['stack_company'] ) ) : '';
        $phone          = isset( $_POST['stack_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['stack_phone'] ) ) : '';
        $password       = isset( $_POST['stack_password'] ) ? trim( (string) wp_unslash( $_POST['stack_password'] ) ) : '';
        $assign_package = isset( $_POST['assign_package'] ) ? sanitize_text_field( wp_unslash( $_POST['assign_package'] ) ) : '';

        if ( '' === $full_name || '' === $email || ! is_email( $email ) ) {
            $this->add_activity_log( 'stack_user_create', __( 'StackCP user creation failed: invalid client name or email.', 'twentyi-hosting-browser' ), [ 'email' => $email ], 'error' );
            $this->redirect_stack_users_page_with_notice( 'error', __( 'Enter a valid client name and email address before creating a StackCP user.', 'twentyi-hosting-browser' ) );
        }

        if ( '' !== $password && strlen( $password ) < 8 ) {
            $this->add_activity_log( 'stack_user_create', __( 'StackCP user creation failed: password too short.', 'twentyi-hosting-browser' ), [ 'email' => $email ], 'error' );
            $this->redirect_stack_users_page_with_notice( 'error', __( 'StackCP user passwords should be at least 8 characters long.', 'twentyi-hosting-browser' ) );
        }

        $payload = $this->build_stack_user_create_payload(
            [
                'full_name' => $full_name,
                'email'     => $email,
                'company'   => $company,
                'phone'     => $phone,
                'password'  => $password,
            ]
        );

        $result = $this->perform_create_stack_user( $payload );

        if ( is_wp_error( $result ) ) {
            $message = $result->get_error_message() . ' ' . __( '20i’s public docs clearly document listing StackCP users, but the create route can vary by account/API documentation. Create this user manually in My20i if this account rejects the candidate routes.', 'twentyi-hosting-browser' );
            $this->add_activity_log( 'stack_user_create', $message, [ 'email' => $email, 'assign_package' => $assign_package ], 'error' );
            $this->redirect_stack_users_page_with_notice( 'error', $message );
        }

        $created_user_id = $this->extract_created_stack_user_id( $result );
        $message         = '' !== $created_user_id
            ? sprintf(
                /* translators: %s: StackCP user ID */
                __( 'StackCP user creation request completed. Detected user reference: %s.', 'twentyi-hosting-browser' ),
                $created_user_id
            )
            : __( 'StackCP user creation request completed. Refresh the StackCP Users list to confirm the new user reference.', 'twentyi-hosting-browser' );

        if ( '' !== $assign_package && '' !== $created_user_id ) {
            $assign_result = $this->perform_assign_stack_user_to_package( $assign_package, $created_user_id );

            if ( is_wp_error( $assign_result ) ) {
                $message .= ' ' . sprintf(
                    /* translators: %s: API error */
                    __( 'The user may have been created, but package assignment was not confirmed: %s', 'twentyi-hosting-browser' ),
                    $assign_result->get_error_message()
                );
            } else {
                $message .= ' ' . __( 'Package assignment request was also sent.', 'twentyi-hosting-browser' );
            }
        } elseif ( '' !== $assign_package ) {
            $message .= ' ' . __( 'A package was selected, but the API response did not include a clear StackCP user reference to assign.', 'twentyi-hosting-browser' );
        }

        $this->clear_caches();
        $this->add_activity_log(
            'stack_user_create',
            $message,
            [
                'email'          => $email,
                'stack_user_ref' => $created_user_id,
                'assign_package' => $assign_package,
            ],
            'success'
        );
        $this->redirect_stack_users_page_with_notice( 'success', $message );
    }

    /**
     * Handle package maintenance actions.
     */
    public function handle_package_action() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to do that.', 'twentyi-hosting-browser' ) );
        }

        check_admin_referer( 'twentyi_hosting_browser_package_action' );

        $package_id     = isset( $_POST['package_id'] ) ? sanitize_text_field( wp_unslash( $_POST['package_id'] ) ) : '';
        $package_action = isset( $_POST['package_action'] ) ? sanitize_key( wp_unslash( $_POST['package_action'] ) ) : '';
        $ftp_user       = isset( $_POST['ftp_user'] ) ? $this->sanitize_ftp_username( wp_unslash( $_POST['ftp_user'] ) ) : '';
        $unlock_minutes = isset( $_POST['unlock_minutes'] ) ? absint( $_POST['unlock_minutes'] ) : 60;
        $mail_domain    = isset( $_POST['mail_domain'] ) ? $this->sanitize_domain( wp_unslash( $_POST['mail_domain'] ) ) : '';
        $mail_local     = isset( $_POST['mail_local'] ) ? $this->sanitize_mail_local_part( wp_unslash( $_POST['mail_local'] ) ) : '';
        $mail_password  = isset( $_POST['mail_password'] ) ? trim( (string) wp_unslash( $_POST['mail_password'] ) ) : '';
        $mail_remote    = isset( $_POST['mail_remote'] ) ? sanitize_email( wp_unslash( $_POST['mail_remote'] ) ) : '';
        $mail_send      = isset( $_POST['mail_send'] );
        $mail_receive   = isset( $_POST['mail_receive'] );
        $delete_confirm = isset( $_POST['delete_confirm'] ) ? sanitize_text_field( wp_unslash( $_POST['delete_confirm'] ) ) : '';
        $package_match  = isset( $_POST['package_match'] ) ? sanitize_text_field( wp_unslash( $_POST['package_match'] ) ) : '';
        $result         = null;

        if ( '' === $package_id || '' === $package_action ) {
            $this->redirect_package_page_with_notice( $package_id, 'error', __( 'Missing package action details.', 'twentyi-hosting-browser' ) );
        }

        switch ( $package_action ) {
            case 'purge_cache':
                $result = $this->perform_cache_purge( $package_id );
                break;

            case 'run_malware_scan':
                $result = $this->perform_malware_scan( $package_id );
                break;

            case 'delete_package':
                $summary          = $this->get_package_summary( $package_id );
                $confirm_targets  = [ $package_id ];
                $primary_domain   = '';

                if ( ! is_wp_error( $summary ) && is_array( $summary ) ) {
                    $primary_domain  = isset( $summary['name'] ) ? (string) $summary['name'] : '';
                    $confirm_targets = array_merge( $confirm_targets, $this->extract_package_domains( $summary ) );
                }

                $confirm_targets = array_values(
                    array_unique(
                        array_filter(
                            array_map(
                                static function ( $target ) {
                                    return strtolower( trim( (string) $target ) );
                                },
                                $confirm_targets
                            )
                        )
                    )
                );

                if ( 'DELETE' !== $delete_confirm || ! in_array( strtolower( trim( $package_match ) ), $confirm_targets, true ) ) {
                    $this->redirect_package_page_with_notice( $package_id, 'error', __( 'Delete confirmation did not match. Type DELETE and the package domain or package ID exactly.', 'twentyi-hosting-browser' ) );
                }

                $result = $this->perform_delete_package( $package_id );

                if ( is_wp_error( $result ) ) {
                    $this->redirect_package_page_with_notice( $package_id, 'error', $result->get_error_message() );
                }

                $this->clear_caches();
                $message = sprintf(
                    /* translators: %s: package domain or ID. */
                    __( 'Delete request sent for 20i hosting package %s.', 'twentyi-hosting-browser' ),
                    $primary_domain ?: $package_id
                );

                $this->add_activity_log(
                    'package_delete',
                    $message,
                    [
                        'package_id' => $package_id,
                        'domain'     => $primary_domain,
                    ],
                    'success'
                );

                $this->redirect_websites_page_with_notice( 'success', $message );
                break;

            case 'create_mailbox':
                if ( '' === $mail_domain || '' === $mail_local || '' === $mail_password ) {
                    $this->redirect_package_page_with_notice( $package_id, 'error', __( 'Choose a domain, mailbox prefix, and password before creating a mailbox.', 'twentyi-hosting-browser' ) );
                }

                if ( strlen( $mail_password ) < 8 ) {
                    $this->redirect_package_page_with_notice( $package_id, 'error', __( 'Mailbox passwords should be at least 8 characters long.', 'twentyi-hosting-browser' ) );
                }

                $result = $this->perform_create_mailbox( $package_id, $mail_domain, $mail_local, $mail_password, $mail_send, $mail_receive );

                if ( ! is_wp_error( $result ) ) {
                    $result = [
                        'message' => sprintf(
                            /* translators: %s: email address */
                            __( 'Mailbox %s created successfully.', 'twentyi-hosting-browser' ),
                            $mail_local . '@' . $mail_domain
                        ),
                    ];
                }
                break;

            case 'create_forwarder':
                if ( '' === $mail_domain || '' === $mail_local || '' === $mail_remote ) {
                    $this->redirect_package_page_with_notice( $package_id, 'error', __( 'Choose a domain, forwarder prefix, and destination email before creating a forwarder.', 'twentyi-hosting-browser' ) );
                }

                $result = $this->perform_create_forwarder( $package_id, $mail_domain, $mail_local, $mail_remote );

                if ( ! is_wp_error( $result ) ) {
                    $result = [
                        'message' => sprintf(
                            /* translators: 1: source mailbox, 2: destination email */
                            __( 'Forwarder %1$s now sends mail to %2$s.', 'twentyi-hosting-browser' ),
                            $mail_local . '@' . $mail_domain,
                            $mail_remote
                        ),
                    ];
                }
                break;

            case 'unlock_ftp':
                if ( '' === $ftp_user ) {
                    $this->redirect_package_page_with_notice( $package_id, 'error', __( 'Choose an FTP user to unlock.', 'twentyi-hosting-browser' ) );
                }

                $minutes = max( 15, $unlock_minutes );
                $result  = $this->perform_ftp_user_update(
                    $package_id,
                    $ftp_user,
                    [
                        'UnlockedUntil' => gmdate( 'c', time() + $minutes * MINUTE_IN_SECONDS ),
                        'Enabled'       => true,
                    ]
                );

                if ( ! is_wp_error( $result ) ) {
                    $result = [
                        'message' => sprintf(
                            /* translators: 1: FTP username, 2: number of minutes */
                            __( 'FTP user %1$s unlocked for %2$d minutes.', 'twentyi-hosting-browser' ),
                            $ftp_user,
                            $minutes
                        ),
                    ];
                }
                break;

            case 'enable_ftp':
            case 'disable_ftp':
                if ( '' === $ftp_user ) {
                    $this->redirect_package_page_with_notice( $package_id, 'error', __( 'Choose an FTP user to update.', 'twentyi-hosting-browser' ) );
                }

                $enabled = 'enable_ftp' === $package_action;
                $result  = $this->perform_ftp_user_update( $package_id, $ftp_user, [ 'Enabled' => $enabled ] );

                if ( ! is_wp_error( $result ) ) {
                    $result = [
                        'message' => sprintf(
                            /* translators: 1: FTP username, 2: status label */
                            __( 'FTP user %1$s %2$s.', 'twentyi-hosting-browser' ),
                            $ftp_user,
                            $enabled ? __( 'enabled', 'twentyi-hosting-browser' ) : __( 'disabled', 'twentyi-hosting-browser' )
                        ),
                    ];
                }
                break;
        }

        if ( null === $result ) {
            $this->redirect_package_page_with_notice( $package_id, 'error', __( 'That package action is not supported.', 'twentyi-hosting-browser' ) );
        }

        if ( is_wp_error( $result ) ) {
            $this->redirect_package_page_with_notice( $package_id, 'error', $result->get_error_message() );
        }

        $this->clear_package_cache( $package_id );
        $this->redirect_package_page_with_notice( $package_id, 'success', $result['message'] ?? __( 'Package action completed.', 'twentyi-hosting-browser' ) );
    }

    /**
     * Build StackCP user create payload.
     *
     * @param array $input Sanitized input values.
     * @return array<string,mixed>
     */
    protected function build_stack_user_create_payload( $input ) {
        $full_name = isset( $input['full_name'] ) ? trim( (string) $input['full_name'] ) : '';
        $parts     = preg_split( '/\s+/', $full_name );
        $first     = is_array( $parts ) && ! empty( $parts ) ? array_shift( $parts ) : '';
        $last      = is_array( $parts ) ? implode( ' ', $parts ) : '';

        $contact = [
            'person_name'   => $full_name,
            'name'          => $full_name,
            'firstName'     => $first,
            'lastName'      => $last,
            'email'         => isset( $input['email'] ) ? (string) $input['email'] : '',
            'company'       => isset( $input['company'] ) ? (string) $input['company'] : '',
            'companyName'   => isset( $input['company'] ) ? (string) $input['company'] : '',
            'phone'         => isset( $input['phone'] ) ? (string) $input['phone'] : '',
            'phoneNumber'   => isset( $input['phone'] ) ? (string) $input['phone'] : '',
            'countryCode'   => 'GB',
            'country_code'  => 'GB',
        ];

        $contact = array_filter(
            $contact,
            static function ( $value ) {
                return '' !== (string) $value;
            }
        );

        if ( ! empty( $input['password'] ) ) {
            $contact['password']    = (string) $input['password'];
            $contact['newPassword'] = (string) $input['password'];
        }

        return $contact;
    }

    /**
     * Create a StackCP user through likely 20i API routes.
     *
     * @param array $contact Contact payload.
     * @return array|WP_Error
     */
    protected function perform_create_stack_user( $contact ) {
        $candidates = [
            [ 'path' => '/reseller/*/susers',     'method' => 'POST', 'body' => [ 'contact' => $contact ] ],
            [ 'path' => '/reseller/*/susers',     'method' => 'POST', 'body' => [ 'new' => [ 'contact' => $contact ] ] ],
            [ 'path' => '/reseller/*/susers',     'method' => 'POST', 'body' => [ 'new' => $contact ] ],
            [ 'path' => '/reseller/*/susers',     'method' => 'POST', 'body' => $contact ],
            [ 'path' => '/reseller/*/susers/add', 'method' => 'POST', 'body' => [ 'contact' => $contact ] ],
            [ 'path' => '/reseller/*/suser',      'method' => 'POST', 'body' => [ 'contact' => $contact ] ],
            [ 'path' => '/reseller/*/stackUsers', 'method' => 'POST', 'body' => [ 'contact' => $contact ] ],
        ];

        return $this->try_action_requests(
            $candidates,
            __( 'We could not create a StackCP user with the available 20i API routes.', 'twentyi-hosting-browser' )
        );
    }

    /**
     * Try to assign a StackCP user to a package through likely 20i API routes.
     *
     * @param string $package_id Package ID.
     * @param string $stack_user_id StackCP user ID or reference.
     * @return array|WP_Error
     */
    protected function perform_assign_stack_user_to_package( $package_id, $stack_user_id ) {
        $encoded_package_id = rawurlencode( $package_id );
        $references         = $this->build_stack_user_reference_candidates( $stack_user_id );
        $candidates         = [];

        foreach ( $references as $reference ) {
            $encoded_user = rawurlencode( $reference );

            $candidates[] = [ 'path' => '/package/' . $encoded_package_id . '/stackUsers', 'method' => 'POST', 'body' => [ 'stackUser' => $reference ] ];
            $candidates[] = [ 'path' => '/package/' . $encoded_package_id . '/stackUsers', 'method' => 'POST', 'body' => [ 'add' => [ $reference ] ] ];
            $candidates[] = [ 'path' => '/package/' . $encoded_package_id . '/stackUsers', 'method' => 'POST', 'body' => [ 'stackUsers' => [ $reference ] ] ];
            $candidates[] = [ 'path' => '/package/' . $encoded_package_id . '/users',      'method' => 'POST', 'body' => [ 'stackUser' => $reference ] ];
            $candidates[] = [ 'path' => '/reseller/*/susers/' . $encoded_user,             'method' => 'POST', 'body' => [ 'packages' => [ 'add' => [ $package_id ] ] ] ];
            $candidates[] = [ 'path' => '/reseller/*/susers/' . $encoded_user,             'method' => 'POST', 'body' => [ 'packageAccess' => [ $package_id ] ] ];
        }

        return $this->try_action_requests(
            $candidates,
            __( 'We could not assign this StackCP user to the selected package with the available 20i API routes.', 'twentyi-hosting-browser' )
        );
    }

    /**
     * Build StackCP user reference candidates.
     *
     * @param string $stack_user_id User ID.
     * @return array<int,string>
     */
    protected function build_stack_user_reference_candidates( $stack_user_id ) {
        $stack_user_id = trim( (string) $stack_user_id );

        if ( '' === $stack_user_id ) {
            return [];
        }

        $candidates = [ $stack_user_id ];

        if ( 0 !== strpos( $stack_user_id, 'stack-user:' ) && preg_match( '/^\d+$/', $stack_user_id ) ) {
            $candidates[] = 'stack-user:' . $stack_user_id;
        }

        if ( 0 === strpos( $stack_user_id, 'stack-user:' ) ) {
            $number = substr( $stack_user_id, strlen( 'stack-user:' ) );
            if ( '' !== $number ) {
                $candidates[] = $number;
            }
        }

        return array_values( array_unique( array_filter( $candidates ) ) );
    }

    /**
     * Extract a likely StackCP user reference from a 20i response.
     *
     * @param mixed $data API response.
     * @return string
     */
    protected function extract_created_stack_user_id( $data ) {
        if ( is_scalar( $data ) ) {
            return sanitize_text_field( (string) $data );
        }

        if ( ! is_array( $data ) ) {
            return '';
        }

        foreach ( [ 'stackUser', 'stackUserId', 'stack_user', 'stack_user_id', 'suser', 'suserId', 'id', 'user', 'result' ] as $key ) {
            if ( isset( $data[ $key ] ) ) {
                $value = $data[ $key ];

                if ( is_scalar( $value ) ) {
                    return sanitize_text_field( (string) $value );
                }

                if ( is_array( $value ) ) {
                    $nested = $this->extract_created_stack_user_id( $value );
                    if ( '' !== $nested ) {
                        return $nested;
                    }
                }
            }
        }

        foreach ( $data as $value ) {
            if ( is_array( $value ) ) {
                $nested = $this->extract_created_stack_user_id( $value );
                if ( '' !== $nested ) {
                    return $nested;
                }
            }
        }

        return '';
    }

    /**
     * Redirect back to StackCP users page with a notice.
     *
     * @param string $status success|error.
     * @param string $message Notice message.
     */
    protected function redirect_stack_users_page_with_notice( $status, $message ) {
        wp_safe_redirect(
            add_query_arg(
                [
                    'page'               => self::STACK_USERS_SLUG,
                    'stack_user_status'  => sanitize_key( $status ),
                    'stack_user_message' => (string) $message,
                ],
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    /**
     * Purge cache for a package.
     *
     * @param string $package_id Package ID.
     * @return array|WP_Error
     */
    protected function perform_cache_purge( $package_id ) {
        $encoded_package_id = rawurlencode( $package_id );

        $result = $this->try_action_requests(
            [
                [ 'path' => '/package/' . $encoded_package_id . '/web/cacheReport',      'method' => 'POST', 'body' => [ 'purge' => true ] ],
                [ 'path' => '/package/' . $encoded_package_id . '/web/cacheReport/purge', 'method' => 'POST', 'body' => [] ],
                [ 'path' => '/package/' . $encoded_package_id . '/web/purgeCache',        'method' => 'POST', 'body' => [] ],
                [ 'path' => '/package/' . $encoded_package_id . '/web/stackcache/purge',  'method' => 'POST', 'body' => [] ],
            ],
            __( 'We could not purge cache for this package using the available 20i API routes.', 'twentyi-hosting-browser' )
        );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return [
            'message' => __( 'Cache purge request sent successfully.', 'twentyi-hosting-browser' ),
        ];
    }

    /**
     * Trigger a malware scan for a package.
     *
     * @param string $package_id Package ID.
     * @return array|WP_Error
     */
    protected function perform_malware_scan( $package_id ) {
        $encoded_package_id = rawurlencode( $package_id );

        $result = $this->try_action_requests(
            [
                [ 'path' => '/package/' . $encoded_package_id . '/web/malwareScan',       'method' => 'POST', 'body' => [ 'scan' => true ] ],
                [ 'path' => '/package/' . $encoded_package_id . '/web/malwareScan/start', 'method' => 'POST', 'body' => [] ],
                [ 'path' => '/package/' . $encoded_package_id . '/web/malwareScan',       'method' => 'POST', 'body' => [] ],
            ],
            __( 'We could not start a malware scan for this package using the available 20i API routes.', 'twentyi-hosting-browser' )
        );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return [
            'message' => __( 'Malware scan request sent successfully.', 'twentyi-hosting-browser' ),
        ];
    }

    /**
     * Create a mailbox for a package domain.
     *
     * @param string $package_id Package ID.
     * @param string $domain Domain name.
     * @param string $local Mailbox local part.
     * @param string $password Mailbox password.
     * @param bool   $send Allow sending.
     * @param bool   $receive Allow receiving.
     * @return array|WP_Error
     */
    protected function perform_create_mailbox( $package_id, $domain, $local, $password, $send = true, $receive = true ) {
        $encoded_package_id = rawurlencode( $package_id );
        $encoded_domain     = rawurlencode( $domain );

        $payload = [
            'new' => [
                'mailbox' => [
                    'local'    => $local,
                    'send'     => $send ? 'true' : 'false',
                    'receive'  => $receive ? 'true' : 'false',
                    'password' => $password,
                ],
            ],
        ];

        return $this->request( '/package/' . $encoded_package_id . '/email/' . $encoded_domain, 'POST', $payload );
    }

    /**
     * Create a forwarder for a package domain.
     *
     * @param string $package_id Package ID.
     * @param string $domain Domain name.
     * @param string $local Forwarder local part.
     * @param string $remote Destination email address.
     * @return array|WP_Error
     */
    protected function perform_create_forwarder( $package_id, $domain, $local, $remote ) {
        $encoded_package_id = rawurlencode( $package_id );
        $encoded_domain     = rawurlencode( $domain );

        $payload = [
            'new' => [
                'forward' => [
                    'local'  => $local,
                    'remote' => $remote,
                ],
            ],
        ];

        return $this->request( '/package/' . $encoded_package_id . '/email/' . $encoded_domain, 'POST', $payload );
    }

    /**
     * Update an FTP user for a package.
     *
     * @param string $package_id Package ID.
     * @param string $ftp_user FTP username.
     * @param array  $changes Change set.
     * @return array|WP_Error
     */
    protected function perform_ftp_user_update( $package_id, $ftp_user, $changes ) {
        $encoded_package_id = rawurlencode( $package_id );
        $ftp_user           = (string) $ftp_user;

        return $this->try_action_requests(
            [
                [ 'path' => '/package/' . $encoded_package_id . '/web/ftpusers', 'method' => 'POST', 'body' => [ 'update' => [ $ftp_user => $changes ] ] ],
                [ 'path' => '/package/' . $encoded_package_id . '/web/ftpusers', 'method' => 'POST', 'body' => [ 'update' => array_merge( [ 'username' => $ftp_user ], $changes ) ] ],
                [ 'path' => '/package/' . $encoded_package_id . '/web/ftpusers', 'method' => 'POST', 'body' => array_merge( [ 'username' => $ftp_user ], $changes ) ],
            ],
            __( 'We could not update that FTP user with the available 20i API routes.', 'twentyi-hosting-browser' )
        );
    }

    /**
     * Delete a hosting package through likely 20i API routes.
     *
     * 20i's public support docs confirm packages can be deleted in the reseller control panel,
     * but do not publish a single public delete-package endpoint. These candidates keep the
     * plugin usable while returning a clear error if the account requires a different route.
     *
     * @param string $package_id Package ID.
     * @return array|WP_Error
     */
    protected function perform_delete_package( $package_id ) {
        $encoded_package_id = rawurlencode( $package_id );

        return $this->try_action_requests(
            [
                [ 'path' => '/package/' . $encoded_package_id,                 'method' => 'DELETE', 'body' => [] ],
                [ 'path' => '/package/' . $encoded_package_id . '/web',         'method' => 'DELETE', 'body' => [] ],
                [ 'path' => '/package/' . $encoded_package_id . '/delete',      'method' => 'POST',   'body' => [] ],
                [ 'path' => '/package/' . $encoded_package_id,                 'method' => 'POST',   'body' => [ 'delete' => true ] ],
                [ 'path' => '/reseller/*/package/' . $encoded_package_id,       'method' => 'DELETE', 'body' => [] ],
                [ 'path' => '/reseller/*/web/' . $encoded_package_id,           'method' => 'DELETE', 'body' => [] ],
                [ 'path' => '/reseller/*/deleteWeb',                            'method' => 'POST',   'body' => [ 'id' => $package_id, 'packageId' => $package_id ] ],
                [ 'path' => '/reseller/*/removeWeb',                            'method' => 'POST',   'body' => [ 'id' => $package_id, 'packageId' => $package_id ] ],
            ],
            __( 'We could not delete this hosting package using the available 20i API routes. Check My20i API documentation or browser Network requests for the exact delete endpoint on your account.', 'twentyi-hosting-browser' )
        );
    }

    /**
     * Try a sequence of action requests until one succeeds.
     *
     * @param array  $candidates Request candidates.
     * @param string $fallback_message Fallback error message.
     * @return array|WP_Error
     */
    protected function try_action_requests( $candidates, $fallback_message ) {
        $errors = [];

        foreach ( $candidates as $candidate ) {
            $path   = isset( $candidate['path'] ) ? (string) $candidate['path'] : '';
            $method = isset( $candidate['method'] ) ? (string) $candidate['method'] : 'POST';
            $body   = isset( $candidate['body'] ) && is_array( $candidate['body'] ) ? $candidate['body'] : [];

            if ( '' === $path ) {
                continue;
            }

            $result = $this->request( $path, $method, $body );

            if ( ! is_wp_error( $result ) ) {
                return is_array( $result ) ? $result : [];
            }

            $errors[] = $result->get_error_message();
        }

        return new WP_Error( 'twentyi_action_failed', ! empty( $errors ) ? implode( ' ', array_unique( $errors ) ) : $fallback_message );
    }

    /**
     * Redirect back to the websites page with a notice.
     *
     * @param string $status success|error.
     * @param string $message Notice message.
     */
    protected function redirect_websites_page_with_notice( $status, $message ) {
        wp_safe_redirect(
            add_query_arg(
                [
                    'page'         => self::MENU_SLUG,
                    'site_status'  => sanitize_key( $status ),
                    'site_message' => (string) $message,
                    'refreshed'    => 1,
                ],
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    /**
     * Redirect back to a package page with a notice.
     *
     * @param string $package_id Package ID.
     * @param string $status success|error.
     * @param string $message Notice message.
     */
    protected function redirect_package_page_with_notice( $package_id, $status, $message ) {
        $context = [ 'package_id' => (string) $package_id ];
        if ( isset( $_POST['package_action'] ) ) {
            $context['action'] = sanitize_key( wp_unslash( $_POST['package_action'] ) );
        }
        $this->add_activity_log( 'package_action', (string) $message, $context, sanitize_key( $status ) );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page'           => self::PACKAGE_SLUG,
                    'package_id'     => (string) $package_id,
                    'action_status'  => sanitize_key( $status ),
                    'action_message' => (string) $message,
                ],
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    /**
     * Create a 20i hosting package from prepared values.
     *
     * @param array<string,mixed> $args Package values.
     * @return array<string,mixed>|WP_Error
     */
    protected function create_hosting_package( $args ) {
        $domain_name    = $this->sanitize_domain( $args['domain_name'] ?? '' );
        $type           = sanitize_text_field( (string) ( $args['type'] ?? '' ) );
        $label          = sanitize_text_field( (string) ( $args['label'] ?? '' ) );
        $stack_user     = sanitize_text_field( (string) ( $args['stackUser'] ?? '' ) );
        $extra_domains  = isset( $args['extra_domain_names'] ) && is_array( $args['extra_domain_names'] ) ? array_filter( array_map( [ $this, 'sanitize_domain' ], $args['extra_domain_names'] ) ) : [];
        $document_roots = isset( $args['documentRoots'] ) && is_array( $args['documentRoots'] ) ? $args['documentRoots'] : [];

        if ( '' === $domain_name ) {
            return new WP_Error( 'twentyi_invalid_domain', __( 'Please add a valid primary domain.', 'twentyi-hosting-browser' ) );
        }

        if ( '' === $type ) {
            return new WP_Error( 'twentyi_missing_package_type', __( 'Please choose a package type.', 'twentyi-hosting-browser' ) );
        }

        $payload = [
            'domain_name' => $domain_name,
            'type'        => $type,
        ];

        if ( '' !== $label ) {
            $payload['label'] = $label;
        }

        if ( ! empty( $extra_domains ) ) {
            $payload['extra_domain_names'] = array_values( $extra_domains );
        }

        if ( '' !== $stack_user ) {
            $payload['stackUser'] = $stack_user;
        }

        if ( ! empty( $document_roots ) ) {
            $payload['documentRoots'] = $document_roots;
        }

        $result = $this->request( '/reseller/*/addWeb', 'POST', $payload );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        $new_package_id = '';

        if ( isset( $result['result'] ) ) {
            $new_package_id = (string) $result['result'];
        } elseif ( isset( $result['id'] ) ) {
            $new_package_id = (string) $result['id'];
        }

        $this->clear_caches();

        return [
            'package_id' => $new_package_id,
            'result'     => $result,
            'payload'    => $payload,
        ];
    }

    /**
     * Redirect frontend form back to page.
     *
     * @param string $url    Referer URL.
     * @param string $status Status key.
     */
    protected function redirect_frontend_request( $url, $status ) {
        wp_safe_redirect(
            add_query_arg(
                [ 'twentyi_request_status' => sanitize_key( $status ) ],
                remove_query_arg( [ 'twentyi_request_status' ], $url )
            )
        );
        exit;
    }

    /**
     * Redirect to requests admin page with notice.
     *
     * @param string $status  Notice status.
     * @param string $message Notice message.
     */
    protected function redirect_requests_page_with_notice( $status, $message ) {
        wp_safe_redirect(
            add_query_arg(
                [
                    'page'            => self::REQUESTS_SLUG,
                    'request_notice'  => sanitize_key( $status ),
                    'request_message' => (string) $message,
                ],
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    /**
     * Send admin notification for a new website request.
     *
     * @param int $request_id Request post ID.
     */
    protected function send_website_request_notification( $request_id ) {
        $business     = (string) get_post_meta( $request_id, 'business_name', true );
        $domain       = (string) get_post_meta( $request_id, 'domain_name', true );
        $email        = (string) get_post_meta( $request_id, 'email', true );
        $phone        = (string) get_post_meta( $request_id, 'phone', true );
        $brief        = (string) get_post_meta( $request_id, 'website_brief', true );
        $status       = (string) get_post_meta( $request_id, 'status', true );
        $package_type = (string) get_post_meta( $request_id, 'package_type', true );
        $admin_to     = get_option( 'admin_email' );

        if ( ! is_email( $admin_to ) ) {
            return;
        }

        $subject = sprintf(
            /* translators: %s: domain name */
            __( 'New website request: %s', 'twentyi-hosting-browser' ),
            $domain
        );

        $body = sprintf(
            "Business: %s\nDomain: %s\nSelected package: %s\nClient email: %s\nPhone: %s\nStatus: %s\n\nBrief:\n%s\n\nReview: %s",
            $business,
            $domain,
            $package_type ? $this->get_package_type_label( $package_type ) : __( 'Not selected', 'twentyi-hosting-browser' ),
            $email,
            $phone,
            $status,
            $brief,
            admin_url( 'admin.php?page=' . self::REQUESTS_SLUG )
        );

        wp_mail( $admin_to, $subject, $body );
    }

    /**
     * Test connection.
     *
     * @return array<string,mixed>
     */
    protected function test_connection() {
        $result = $this->request( '/package' );

        if ( is_wp_error( $result ) ) {
            return [
                'success' => false,
                'message' => $result->get_error_message(),
            ];
        }

        $count = is_array( $result ) ? count( $result ) : 0;

        return [
            'success' => true,
            'message' => sprintf(
                /* translators: %d: number of packages */
                __( 'Connection successful. 20i returned %d hosting package(s).', 'twentyi-hosting-browser' ),
                intval( $count )
            ),
        ];
    }

    /**
     * Get packages, optionally refreshing the transient.
     *
     * @param bool $force_refresh Bypass cache.
     * @return array|WP_Error
     */
    protected function get_packages( $force_refresh = false ) {
        if ( ! $force_refresh ) {
            $cached = get_transient( self::CACHE_PACKAGES );
            if ( false !== $cached ) {
                return $cached;
            }
        }

        $result = $this->request( '/package' );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        if ( ! is_array( $result ) ) {
            return new WP_Error( 'twentyi_invalid_packages', __( '20i returned an unexpected response.', 'twentyi-hosting-browser' ) );
        }

        set_transient( self::CACHE_PACKAGES, $result, self::CACHE_PACKAGES_TTL );

        return $result;
    }

    /**
     * Get a package summary from the package listing.
     *
     * @param string $package_id Package ID.
     * @param bool   $force_refresh Force refresh.
     * @return array|WP_Error
     */
    protected function get_package_summary( $package_id, $force_refresh = false ) {
        $packages = $this->get_packages( $force_refresh );

        if ( is_wp_error( $packages ) ) {
            return $packages;
        }

        foreach ( $packages as $package ) {
            if ( isset( $package['id'] ) && (string) $package['id'] === (string) $package_id ) {
                return $package;
            }
        }

        return new WP_Error( 'twentyi_package_not_found', __( 'That package could not be found in the current 20i package list.', 'twentyi-hosting-browser' ) );
    }

    /**
     * Get all detail sections for a single package.
     *
     * @param string $package_id Package ID.
     * @param bool   $force_refresh Force refresh.
     * @return array|WP_Error
     */
    protected function get_package_detail_bundle( $package_id, $force_refresh = false, $summary = [] ) {
        if ( '' === $package_id ) {
            return new WP_Error( 'twentyi_missing_package_id', __( 'Missing package ID.', 'twentyi-hosting-browser' ) );
        }

        $cache_key = $this->get_package_cache_key( $package_id );

        if ( ! $force_refresh ) {
            $cached = get_transient( $cache_key );
            if ( false !== $cached ) {
                return $cached;
            }
        }

        $encoded_package_id = rawurlencode( $package_id );

        $bundle = [
            'email_objects'      => $this->fetch_mail_objects_for_package( $package_id, is_array( $summary ) ? $summary : [] ),
            'wordpress_settings' => $this->fetch_package_section( '/package/' . $encoded_package_id . '/web/wordpressSettings', __( 'WordPress settings are not available for this package or the endpoint did not return data.', 'twentyi-hosting-browser' ) ),
            'wordpress_admins'   => $this->fetch_package_section( '/package/' . $encoded_package_id . '/web/wordpressAdministrators', __( 'WordPress administrators are not available for this package or the endpoint did not return data.', 'twentyi-hosting-browser' ) ),
            'ftp_users'          => $this->fetch_package_section( '/package/' . $encoded_package_id . '/web/ftpusers', __( 'FTP users are not available for this package or the endpoint did not return data.', 'twentyi-hosting-browser' ) ),
            'cache_report'       => $this->fetch_package_section( '/package/' . $encoded_package_id . '/web/cacheReport', __( 'The cache report is not available for this package or the endpoint did not return data.', 'twentyi-hosting-browser' ) ),
            'malware_scan'       => $this->fetch_package_section( '/package/' . $encoded_package_id . '/web/malwareScan', __( 'The malware scan report is not available for this package or the endpoint did not return data.', 'twentyi-hosting-browser' ) ),
        ];

        set_transient( $cache_key, $bundle, self::CACHE_PACKAGES_TTL );

        return $bundle;
    }

    /**
     * Fetch mail objects for a package.
     *
     * @param string $package_id Package ID.
     * @param array  $summary Package summary.
     * @return array<string,mixed>
     */
    protected function fetch_mail_objects_for_package( $package_id, $summary = [] ) {
        $encoded_package_id = rawurlencode( $package_id );
        $fallback_message   = __( 'Mail objects are not available for this package or the email endpoints did not return data.', 'twentyi-hosting-browser' );
        $domains            = $this->extract_package_domains( $summary );
        $entries            = [];

        $package_level = $this->request( '/package/' . $encoded_package_id . '/email' );
        if ( ! is_wp_error( $package_level ) && ! empty( $package_level ) ) {
            return [
                'success' => true,
                'message' => '',
                'data'    => [
                    'source'  => 'package',
                    'domains' => [
                        [
                            'domain' => __( 'All package email objects', 'twentyi-hosting-browser' ),
                            'data'   => $package_level,
                        ],
                    ],
                ],
            ];
        }

        foreach ( $domains as $domain ) {
            $result = $this->request( '/package/' . $encoded_package_id . '/email/' . rawurlencode( $domain ) );
            if ( is_wp_error( $result ) || empty( $result ) ) {
                continue;
            }

            $entries[] = [
                'domain' => $domain,
                'data'   => $result,
            ];
        }

        if ( empty( $entries ) ) {
            return [
                'success' => false,
                'message' => $fallback_message,
                'data'    => null,
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'data'    => [
                'source'  => 'domain',
                'domains' => $entries,
            ],
        ];
    }

    /**
     * Fetch available package types.
     *
     * @return array|WP_Error
     */
    protected function get_package_types() {
        $cached = get_transient( self::CACHE_PACKAGE_TYPES );
        if ( false !== $cached ) {
            return $cached;
        }

        $result = $this->request( '/reseller/*/packageTypes' );

        if ( is_wp_error( $result ) ) {
            $fallback = $this->request( '/packageTypes' );
            if ( is_wp_error( $fallback ) ) {
                return $result;
            }
            $result = $fallback;
        }

        $normalized = $this->normalize_package_types( $result );
        set_transient( self::CACHE_PACKAGE_TYPES, $normalized, self::CACHE_PACKAGES_TTL );

        return $normalized;
    }

    /**
     * Fetch available stack users.
     *
     * @return array|WP_Error
     */
    protected function get_stack_users() {
        $cached = get_transient( self::CACHE_STACK_USERS );
        if ( false !== $cached ) {
            return $cached;
        }

        $result = $this->request( '/reseller/*/susers' );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        $normalized = $this->normalize_stack_users( $result );
        set_transient( self::CACHE_STACK_USERS, $normalized, self::CACHE_PACKAGES_TTL );

        return $normalized;
    }

    /**
     * Make an authenticated request to the 20i API.
     *
     * @param string $path API path.
     * @param string $method HTTP method.
     * @param array  $body Optional request body.
     * @return array|WP_Error
     */
    protected function request( $path, $method = 'GET', $body = [] ) {
        $api_key = $this->get_api_key();

        if ( '' === $api_key ) {
            return new WP_Error( 'twentyi_missing_api_key', __( 'Please add your 20i API key in the plugin settings first.', 'twentyi-hosting-browser' ) );
        }

        $url  = 'https://api.20i.com' . $path;
        $args = [
            'method'  => strtoupper( $method ),
            'timeout' => 20,
            'headers' => [
                'Authorization' => 'Bearer ' . base64_encode( $api_key ),
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
        ];

        if ( ! empty( $body ) ) {
            $args['body'] = wp_json_encode( $body );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return new WP_Error(
                'twentyi_request_failed',
                sprintf(
                    /* translators: %s: error message */
                    __( 'The request to 20i failed: %s', 'twentyi-hosting-browser' ),
                    $response->get_error_message()
                )
            );
        }

        $code          = (int) wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        if ( '' === trim( (string) $response_body ) ) {
            $data = [];
        } else {
            $data = json_decode( $response_body, true );
        }

        if ( $code < 200 || $code >= 300 ) {
            $message = __( 'The 20i API returned an error.', 'twentyi-hosting-browser' );

            if ( is_array( $data ) ) {
                if ( ! empty( $data['message'] ) && is_string( $data['message'] ) ) {
                    $message = $data['message'];
                } elseif ( ! empty( $data['error'] ) && is_string( $data['error'] ) ) {
                    $message = $data['error'];
                } elseif ( ! empty( $data['errors'] ) && is_array( $data['errors'] ) ) {
                    $message = implode( ' ', array_map( 'strval', $data['errors'] ) );
                }
            }

            return new WP_Error(
                'twentyi_http_error',
                sprintf(
                    /* translators: 1: HTTP status code, 2: error message */
                    __( '20i API error (%1$d): %2$s', 'twentyi-hosting-browser' ),
                    $code,
                    $message
                )
            );
        }

        if ( null === $data && JSON_ERROR_NONE !== json_last_error() ) {
            if ( 'GET' !== strtoupper( $method ) ) {
                return [
                    'raw_body' => (string) $response_body,
                ];
            }

            return new WP_Error( 'twentyi_invalid_json', __( '20i returned invalid JSON.', 'twentyi-hosting-browser' ) );
        }

        return $data;
    }

    /**
     * Add an entry to the local activity log.
     *
     * @param string $type Entry type.
     * @param string $message Human readable message.
     * @param array  $context Extra context.
     * @param string $status info|success|error.
     */
    protected function add_activity_log( $type, $message, $context = [], $status = 'info' ) {
        $logs = $this->get_activity_log();
        $user = wp_get_current_user();

        $entry = [
            'time'    => current_time( 'mysql' ),
            'status'  => in_array( $status, [ 'info', 'success', 'error' ], true ) ? $status : 'info',
            'type'    => sanitize_key( $type ),
            'message' => sanitize_text_field( (string) $message ),
            'user'    => $user && $user->exists() ? $user->user_login : '',
            'context' => $this->sanitize_log_context( $context ),
        ];

        array_unshift( $logs, $entry );
        $logs = array_slice( $logs, 0, 200 );

        update_option( self::OPTION_ACTIVITY_LOG, $logs, false );
    }

    /**
     * Get local activity log entries.
     *
     * @return array<int,array<string,mixed>>
     */
    protected function get_activity_log() {
        $logs = get_option( self::OPTION_ACTIVITY_LOG, [] );

        return is_array( $logs ) ? $logs : [];
    }

    /**
     * Clear local activity log entries.
     */
    protected function clear_activity_log() {
        delete_option( self::OPTION_ACTIVITY_LOG );
    }

    /**
     * Sanitize arbitrary context for the local activity log.
     *
     * @param mixed $context Context value.
     * @return mixed
     */
    protected function sanitize_log_context( $context ) {
        if ( is_scalar( $context ) || null === $context ) {
            return sanitize_text_field( (string) $context );
        }

        if ( ! is_array( $context ) ) {
            return '';
        }

        $clean = [];

        foreach ( $context as $key => $value ) {
            $clean_key = is_string( $key ) ? sanitize_key( $key ) : (int) $key;
            $clean[ $clean_key ] = $this->sanitize_log_context( $value );
        }

        return $clean;
    }

    /**
     * Get saved API key.
     *
     * @return string
     */
    protected function get_api_key() {
        return trim( (string) get_option( self::OPTION_API_KEY, '' ) );
    }

    /**
     * Package page URL.
     *
     * @param string $package_id Package ID.
     * @return string
     */
    protected function get_package_page_url( $package_id ) {
        return add_query_arg(
            [
                'page'       => self::PACKAGE_SLUG,
                'package_id' => (string) $package_id,
            ],
            admin_url( 'admin.php' )
        );
    }

    /**
     * Fetch a package detail section.
     *
     * @param string $path Endpoint path.
     * @param string $fallback_message Fallback message.
     * @return array<string,mixed>
     */
    protected function fetch_package_section( $path, $fallback_message ) {
        $result = $this->request( $path );

        if ( is_wp_error( $result ) ) {
            return [
                'success' => false,
                'message' => $result->get_error_message() ? $result->get_error_message() : $fallback_message,
                'data'    => null,
            ];
        }

        if ( empty( $result ) ) {
            return [
                'success' => false,
                'message' => $fallback_message,
                'data'    => $result,
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'data'    => $result,
        ];
    }

    /**
     * Get cache key for a package.
     *
     * @param string $package_id Package ID.
     * @return string
     */
    protected function get_package_cache_key( $package_id ) {
        return self::CACHE_PACKAGE_DETAIL_PREFIX . md5( (string) $package_id );
    }


    /**
     * Clear package-specific caches.
     *
     * @param string $package_id Package ID.
     */
    protected function clear_package_cache( $package_id ) {
        delete_transient( $this->get_package_cache_key( $package_id ) );
        delete_transient( self::CACHE_PACKAGES );
    }

    /**
     * Format API date.
     *
     * @param string $date_string API date.
     * @return string
     */
    protected function format_date( $date_string ) {
        if ( empty( $date_string ) ) {
            return '—';
        }

        $timestamp = strtotime( $date_string );

        if ( false === $timestamp ) {
            return $date_string;
        }

        return wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
    }

    /**
     * Clear plugin caches.
     */
    protected function clear_caches() {
        delete_transient( self::CACHE_PACKAGES );
        delete_transient( self::CACHE_PACKAGE_TYPES );
        delete_transient( self::CACHE_STACK_USERS );
    }

    /**
     * Flatten nested scalar data for display.
     *
     * @param array  $data Source data.
     * @param string $prefix Nested prefix.
     * @return array<string,string>
     */
    protected function flatten_scalar_data( $data, $prefix = '' ) {
        $results = [];

        foreach ( $data as $key => $value ) {
            $label = '' === $prefix ? (string) $key : $prefix . ' → ' . $key;

            if ( is_array( $value ) ) {
                $results = array_merge( $results, $this->flatten_scalar_data( $value, $label ) );
            } elseif ( is_bool( $value ) ) {
                $results[ $label ] = $value ? 'true' : 'false';
            } elseif ( is_scalar( $value ) || null === $value ) {
                $results[ $label ] = null === $value ? '' : (string) $value;
            }
        }

        return $results;
    }

    /**
     * Normalize an API collection to rows.
     *
     * @param mixed $data Source data.
     * @param array $preferred_keys Preferred columns.
     * @return array<int,array<string,string>>
     */
    protected function normalize_collection_rows( $data, $preferred_keys = [] ) {
        $rows = [];

        if ( ! is_array( $data ) ) {
            return $rows;
        }

        foreach ( $data as $item_key => $item ) {
            if ( ! is_array( $item ) ) {
                if ( is_scalar( $item ) ) {
                    $rows[] = [
                        'key'   => (string) $item_key,
                        'value' => (string) $item,
                    ];
                }
                continue;
            }

            $row = [];

            foreach ( $preferred_keys as $preferred_key ) {
                if ( isset( $item[ $preferred_key ] ) && ! is_array( $item[ $preferred_key ] ) ) {
                    $row[ $preferred_key ] = is_bool( $item[ $preferred_key ] ) ? ( $item[ $preferred_key ] ? 'true' : 'false' ) : (string) $item[ $preferred_key ];
                }
            }

            if ( empty( $row ) ) {
                foreach ( $item as $key => $value ) {
                    if ( is_scalar( $value ) || null === $value ) {
                        $row[ (string) $key ] = is_bool( $value ) ? ( $value ? 'true' : 'false' ) : (string) $value;
                    }
                }
            }

            if ( empty( $row ) && ! empty( $item_key ) ) {
                $row['key'] = (string) $item_key;
            }

            if ( ! empty( $row ) ) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Render rows table.
     *
     * @param array<int,array<string,string>> $rows Rows to render.
     */
    protected function render_rows_table( $rows ) {
        if ( empty( $rows ) ) {
            echo '<p>' . esc_html__( 'No rows to display.', 'twentyi-hosting-browser' ) . '</p>';
            return;
        }

        $headers = [];
        foreach ( $rows as $row ) {
            foreach ( array_keys( $row ) as $key ) {
                $headers[ $key ] = $key;
            }
        }

        echo '<table class="widefat striped"><thead><tr>';
        foreach ( $headers as $header ) {
            echo '<th>' . esc_html( ucwords( str_replace( '_', ' ', (string) $header ) ) ) . '</th>';
        }
        echo '</tr></thead><tbody>';

        foreach ( $rows as $row ) {
            echo '<tr>';
            foreach ( $headers as $header ) {
                echo '<td>' . esc_html( isset( $row[ $header ] ) ? (string) $row[ $header ] : '—' ) . '</td>';
            }
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    /**
     * Normalize package type API response.
     *
     * @param mixed $data API response.
     * @return array<int,array<string,string>>
     */
    protected function normalize_package_types( $data ) {
        $normalized = [];

        if ( ! is_array( $data ) ) {
            return $normalized;
        }

        foreach ( $data as $key => $item ) {
            if ( is_array( $item ) ) {
                $id    = isset( $item['id'] ) ? (string) $item['id'] : ( is_string( $key ) ? $key : '' );
                $label = '';

                if ( isset( $item['name'] ) ) {
                    $label = (string) $item['name'];
                } elseif ( isset( $item['label'] ) ) {
                    $label = (string) $item['label'];
                } elseif ( isset( $item['productName'] ) ) {
                    $label = (string) $item['productName'];
                }

                if ( '' !== $id ) {
                    $normalized[] = [
                        'id'    => $id,
                        'label' => $label ? sprintf( '%s (%s)', $label, $id ) : $id,
                    ];
                }
            } elseif ( is_string( $item ) || is_numeric( $item ) ) {
                $id = (string) $item;
                $normalized[] = [
                    'id'    => $id,
                    'label' => $id,
                ];
            }
        }

        usort(
            $normalized,
            static function ( $a, $b ) {
                return strcasecmp( $a['label'], $b['label'] );
            }
        );

        return $normalized;
    }

    /**
     * Normalize StackCP users response.
     *
     * @param mixed $data API response.
     * @return array<int,array<string,mixed>>
     */
    protected function normalize_stack_users( $data ) {
        $normalized = [];

        if ( ! is_array( $data ) || empty( $data['contact'] ) || ! is_array( $data['contact'] ) ) {
            return $normalized;
        }

        foreach ( $data['contact'] as $stack_user_id => $contact ) {
            if ( ! is_array( $contact ) ) {
                continue;
            }

            $name    = trim( implode( ' ', array_filter( [ $contact['firstName'] ?? '', $contact['lastName'] ?? '' ] ) ) );
            $email   = isset( $contact['email'] ) ? (string) $contact['email'] : '';
            $company = isset( $contact['company'] ) ? (string) $contact['company'] : '';

            if ( '' === $name && isset( $contact['person_name'] ) ) {
                $name = (string) $contact['person_name'];
            }

            $grant_map = [];
            foreach ( [ 'grantMap', 'grants', 'grantmap' ] as $grant_key ) {
                if ( isset( $data[ $grant_key ][ $stack_user_id ] ) && is_array( $data[ $grant_key ][ $stack_user_id ] ) ) {
                    $grant_map = $data[ $grant_key ][ $stack_user_id ];
                    break;
                }
            }

            $label = (string) $stack_user_id;
            if ( '' !== $name && '' !== $email ) {
                $label = sprintf( '%1$s (%2$s) - %3$s', $name, $email, $stack_user_id );
            } elseif ( '' !== $name ) {
                $label = sprintf( '%1$s - %2$s', $name, $stack_user_id );
            } elseif ( '' !== $email ) {
                $label = sprintf( '%1$s - %2$s', $email, $stack_user_id );
            }

            $normalized[] = [
                'id'             => (string) $stack_user_id,
                'label'          => $label,
                'name'           => $name,
                'email'          => $email,
                'company'        => $company,
                'contact'        => $contact,
                'grant_map'      => $grant_map,
                'grants_summary' => $this->summarize_grant_map( $grant_map ),
            ];
        }

        usort(
            $normalized,
            static function ( $a, $b ) {
                return strcasecmp( (string) $a['label'], (string) $b['label'] );
            }
        );

        return $normalized;
    }

    /**
     * Extract FTP usernames from endpoint data.
     *
     * @param mixed $data FTP endpoint data.
     * @return array<int,string>
     */
    protected function extract_ftp_user_options( $data ) {
        $rows    = $this->normalize_collection_rows( $data, [ 'username', 'user' ] );
        $options = [];

        foreach ( $rows as $row ) {
            $username = '';

            if ( ! empty( $row['username'] ) ) {
                $username = (string) $row['username'];
            } elseif ( ! empty( $row['user'] ) ) {
                $username = (string) $row['user'];
            } elseif ( ! empty( $row['key'] ) ) {
                $username = (string) $row['key'];
            }

            $username = $this->sanitize_ftp_username( $username );

            if ( '' !== $username ) {
                $options[] = $username;
            }
        }

        $options = array_values( array_unique( $options ) );
        sort( $options, SORT_NATURAL | SORT_FLAG_CASE );

        return $options;
    }

    /**
     * Normalize mail object data into display rows.
     *
     * @param mixed $data Mail object data.
     * @return array<int,array<string,string>>
     */
    protected function normalize_mail_object_rows( $data ) {
        if ( ! is_array( $data ) ) {
            return [];
        }

        $candidates = [];

        if ( isset( $data['result']['result'] ) && is_array( $data['result']['result'] ) ) {
            $candidates[] = $data['result']['result'];
        }

        if ( isset( $data['result'] ) && is_array( $data['result'] ) ) {
            $candidates[] = $data['result'];
        }

        foreach ( [ 'mailbox', 'mailboxes', 'forward', 'forwards' ] as $key ) {
            if ( isset( $data[ $key ] ) && is_array( $data[ $key ] ) ) {
                $candidates[] = $data[ $key ];
            }
        }

        $candidates[] = $data;

        foreach ( $candidates as $candidate ) {
            $rows = $this->normalize_collection_rows( $candidate, [ 'type', 'id', 'local', 'remote', 'send', 'receive', 'generatedId', 'password', 'destination', 'enabled' ] );
            if ( ! empty( $rows ) ) {
                return $rows;
            }
        }

        return [];
    }

    /**
     * Extract the package domains from summary data.
     *
     * @param array $summary Package summary.
     * @return array<int,string>
     */
    protected function extract_package_domains( $summary ) {
        $domains = [];

        if ( ! empty( $summary['name'] ) ) {
            $domains[] = $this->sanitize_domain( (string) $summary['name'] );
        }

        if ( ! empty( $summary['names'] ) && is_array( $summary['names'] ) ) {
            foreach ( $summary['names'] as $domain ) {
                $domains[] = $this->sanitize_domain( (string) $domain );
            }
        }

        $domains = array_values( array_filter( array_unique( $domains ) ) );
        sort( $domains, SORT_NATURAL | SORT_FLAG_CASE );

        return $domains;
    }

    /**
     * Sanitize a mailbox local part.
     *
     * @param string $value Raw local part.
     * @return string
     */
    protected function sanitize_mail_local_part( $value ) {
        $value = trim( (string) $value );

        if ( preg_match( '/^[A-Za-z0-9._%+-]+$/', $value ) ) {
            return $value;
        }

        return '';
    }

    /**
     * Sanitize an FTP username.
     *
     * @param string $username Raw username.
     * @return string
     */
    protected function sanitize_ftp_username( $username ) {
        $username = trim( (string) $username );

        if ( preg_match( '/^[A-Za-z0-9._@-]+$/', $username ) ) {
            return $username;
        }

        return '';
    }

    /**
     * Get brand name for handoff templates.
     *
     * @return string
     */
    protected function get_brand_name() {
        $value = trim( (string) get_option( self::OPTION_BRAND_NAME, '' ) );
        return '' !== $value ? $value : get_bloginfo( 'name' );
    }

    /**
     * Get StackCP login URL.
     *
     * @return string
     */
    protected function get_stackcp_login_url() {
        $value = trim( (string) get_option( self::OPTION_STACKCP_LOGIN_URL, '' ) );
        return '' !== $value ? $value : 'https://stackcp.com';
    }

    /**
     * Get support email for handoff templates.
     *
     * @return string
     */
    protected function get_support_email() {
        return trim( (string) get_option( self::OPTION_SUPPORT_EMAIL, '' ) );
    }

    /**
     * Whether the frontend client dashboard is enabled.
     *
     * @return bool
     */
    protected function client_dashboard_enabled() {
        return (bool) get_option( self::OPTION_CLIENT_DASHBOARD_ENABLED, 1 );
    }

    /**
     * Whether clients can resend their welcome email.
     *
     * @return bool
     */
    protected function client_dashboard_allows_welcome_resend() {
        return (bool) get_option( self::OPTION_CLIENT_DASHBOARD_ALLOW_RESEND, 1 );
    }


    /**
     * Whether clients can create mailboxes from their dashboard.
     *
     * @return bool
     */
    protected function client_dashboard_allows_mailbox_creation() {
        return (bool) get_option( self::OPTION_CLIENT_DASHBOARD_ALLOW_MAILBOX, 1 );
    }

    /**
     * Get the per-request frontend mailbox creation limit.
     *
     * @return int
     */
    protected function get_client_dashboard_mailbox_limit() {
        return max( 1, absint( get_option( self::OPTION_CLIENT_DASHBOARD_MAILBOX_LIMIT, 3 ) ) );
    }

    /**
     * Sanitize a mailbox local part.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    protected function sanitize_mail_local( $value ) {
        $value = strtolower( trim( (string) wp_unslash( $value ) ) );
        $value = preg_replace( '/[^a-z0-9._%+\-]/', '', $value );
        $value = trim( (string) $value, '.-' );

        if ( '' === $value || strlen( $value ) > 64 ) {
            return '';
        }

        return $value;
    }

    /**
     * Get client-created mailbox records for a request.
     *
     * @param int $request_id Request ID.
     * @return array<int,array<string,string>>
     */
    protected function get_client_created_mailboxes( $request_id ) {
        $records = get_post_meta( absint( $request_id ), 'client_created_mailboxes', true );

        if ( ! is_array( $records ) ) {
            return [];
        }

        $clean = [];
        foreach ( $records as $record ) {
            if ( ! is_array( $record ) || empty( $record['address'] ) ) {
                continue;
            }

            $clean[] = [
                'local'      => isset( $record['local'] ) ? $this->sanitize_mail_local( $record['local'] ) : '',
                'domain'     => isset( $record['domain'] ) ? $this->sanitize_domain( $record['domain'] ) : '',
                'address'    => sanitize_email( $record['address'] ),
                'created_at' => isset( $record['created_at'] ) ? sanitize_text_field( $record['created_at'] ) : '',
            ];
        }

        return array_values( array_filter( $clean, function ( $record ) {
            return ! empty( $record['address'] );
        } ) );
    }

    /**
     * Record a mailbox created through the client dashboard.
     *
     * @param int    $request_id Request ID.
     * @param string $local Local part.
     * @param string $domain Domain.
     */
    protected function record_client_created_mailbox( $request_id, $local, $domain ) {
        $records   = $this->get_client_created_mailboxes( $request_id );
        $address   = sanitize_email( $local . '@' . $domain );
        $records[] = [
            'local'      => $local,
            'domain'     => $domain,
            'address'    => $address,
            'created_at' => current_time( 'mysql' ),
        ];

        update_post_meta( absint( $request_id ), 'client_created_mailboxes', $records );
    }

    /**
     * Get website requests that belong to a client email.
     *
     * @param string $email Client email.
     * @return array<int,WP_Post>
     */
    protected function get_client_dashboard_requests_for_email( $email ) {
        $email = sanitize_email( $email );

        if ( ! is_email( $email ) ) {
            return [];
        }

        return get_posts(
            [
                'post_type'      => self::REQUEST_POST_TYPE,
                'post_status'    => [ 'pending', 'publish', 'draft', 'private' ],
                'posts_per_page' => 20,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'meta_query'     => [
                    [
                        'key'   => 'email',
                        'value' => $email,
                    ],
                ],
            ]
        );
    }

    /**
     * Whether the current user can view a request on the client dashboard.
     *
     * @param int $request_id Request ID.
     * @return bool
     */
    protected function current_user_can_view_client_request( $request_id ) {
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }

        $user = wp_get_current_user();
        if ( ! $user || empty( $user->user_email ) ) {
            return false;
        }

        $request_email = sanitize_email( get_post_meta( $request_id, 'email', true ) );
        return is_email( $request_email ) && strtolower( $request_email ) === strtolower( sanitize_email( $user->user_email ) );
    }

    /**
     * Build a friendly client dashboard status.
     *
     * @param string $status Request status.
     * @param string $bootstrap_status Bootstrap status.
     * @param string $welcome_status Welcome email status.
     * @return string
     */
    protected function client_dashboard_status_label( $status, $bootstrap_status, $welcome_status ) {
        if ( 'sent' === $welcome_status ) {
            return __( 'Website ready and welcome email sent', 'twentyi-hosting-browser' );
        }

        if ( 'created' === $bootstrap_status ) {
            return __( 'WordPress admin account created', 'twentyi-hosting-browser' );
        }

        if ( in_array( $bootstrap_status, [ 'queued', 'checking_clone', 'waiting_for_clone' ], true ) ) {
            return __( 'Website created, waiting for Blueprint clone', 'twentyi-hosting-browser' );
        }

        if ( 'created' === $status ) {
            return __( 'Hosting package created', 'twentyi-hosting-browser' );
        }

        if ( 'pending_review' === $status || 'needs_review' === $status || 'pending' === $status || '' === $status ) {
            return __( 'Request received and waiting for review', 'twentyi-hosting-browser' );
        }

        if ( 'rejected' === $status ) {
            return __( 'Request not approved', 'twentyi-hosting-browser' );
        }

        return ucwords( str_replace( '_', ' ', $status ) );
    }

    /**
     * Whether the frontend request form is enabled.
     *
     * @return bool
     */
    protected function is_frontend_form_enabled() {
        return (bool) get_option( self::OPTION_FRONTEND_ENABLED, 0 );
    }

    /**
     * Whether frontend request form requires login.
     *
     * @return bool
     */
    protected function frontend_requires_login() {
        return (bool) get_option( self::OPTION_FRONTEND_REQUIRE_LOGIN, 0 );
    }

    /**
     * Get frontend provisioning mode.
     *
     * @return string
     */
    protected function get_frontend_mode() {
        $mode = sanitize_key( (string) get_option( self::OPTION_FRONTEND_MODE, 'review' ) );
        return in_array( $mode, [ 'review', 'auto' ], true ) ? $mode : 'review';
    }

    /**
     * Get default frontend package type.
     *
     * @return string
     */
    protected function get_frontend_package_type() {
        return sanitize_text_field( (string) get_option( self::OPTION_FRONTEND_PACKAGE_TYPE, '' ) );
    }

    /**
     * Whether clients can choose a package type on the frontend form.
     *
     * @return bool
     */
    protected function frontend_package_selection_enabled() {
        return (bool) get_option( self::OPTION_FRONTEND_PACKAGE_SELECT, 0 );
    }

    /**
     * Get package type ids allowed on the frontend form.
     *
     * @return array<int,string>
     */
    protected function get_frontend_allowed_package_types() {
        $types = get_option( self::OPTION_FRONTEND_ALLOWED_PACKAGE_TYPES, [] );

        if ( ! is_array( $types ) ) {
            $types = is_string( $types ) ? explode( ',', $types ) : [];
        }

        $clean = [];
        foreach ( $types as $type ) {
            foreach ( explode( ',', (string) $type ) as $single_type ) {
                $single_type = trim( sanitize_text_field( $single_type ) );
                if ( '' !== $single_type ) {
                    $clean[] = $single_type;
                }
            }
        }

        return array_values( array_unique( $clean ) );
    }

    /**
     * Get package types that can be shown on the frontend form.
     *
     * @return array<int,array<string,string>>
     */
    protected function get_frontend_public_package_types() {
        $package_types = $this->get_package_types();

        if ( is_wp_error( $package_types ) || empty( $package_types ) ) {
            return [];
        }

        $allowed = $this->get_frontend_allowed_package_types();

        if ( empty( $allowed ) ) {
            return $package_types;
        }

        return array_values(
            array_filter(
                $package_types,
                static function ( $package_type ) use ( $allowed ) {
                    return isset( $package_type['id'] ) && in_array( (string) $package_type['id'], $allowed, true );
                }
            )
        );
    }

    /**
     * Check whether a frontend package type is currently allowed.
     *
     * @param string $package_type Package type id.
     * @return bool
     */
    protected function is_frontend_package_type_allowed( $package_type ) {
        $package_type = sanitize_text_field( (string) $package_type );

        if ( '' === $package_type ) {
            return false;
        }

        $allowed = $this->get_frontend_public_package_types();

        if ( empty( $allowed ) ) {
            return false;
        }

        foreach ( $allowed as $item ) {
            if ( isset( $item['id'] ) && $package_type === (string) $item['id'] ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the package type submitted by the frontend request form.
     *
     * @param string $posted_type Submitted package type.
     * @return string
     */
    protected function resolve_frontend_submitted_package_type( $posted_type ) {
        $posted_type  = sanitize_text_field( (string) $posted_type );
        $default_type = $this->get_frontend_package_type();

        if ( $this->frontend_package_selection_enabled() ) {
            if ( $this->is_frontend_package_type_allowed( $posted_type ) ) {
                return $posted_type;
            }

            if ( $this->is_frontend_package_type_allowed( $default_type ) ) {
                return $default_type;
            }

            return '';
        }

        return $default_type ?: $posted_type;
    }

    /**
     * Get a readable package type label.
     *
     * @param string $package_type Package type id.
     * @return string
     */
    protected function get_package_type_label( $package_type ) {
        $package_type  = sanitize_text_field( (string) $package_type );
        $package_types = $this->get_package_types();

        if ( is_wp_error( $package_types ) || empty( $package_types ) ) {
            return $package_type;
        }

        foreach ( $package_types as $item ) {
            if ( isset( $item['id'] ) && $package_type === (string) $item['id'] ) {
                return (string) $item['label'];
            }
        }

        return $package_type;
    }

    /**
     * Get frontend success message.
     *
     * @return string
     */
    protected function get_frontend_success_message() {
        $message = trim( (string) get_option( self::OPTION_FRONTEND_SUCCESS, '' ) );

        if ( '' === $message ) {
            $message = __( 'Thanks. Your website request has been received and we will review it shortly.', 'twentyi-hosting-browser' );
        }

        return $message;
    }

    /**
     * Filter StackCP users by search string.
     *
     * @param array  $stack_users Users.
     * @param string $search Search term.
     * @return array
     */
    protected function filter_stack_users( $stack_users, $search ) {
        if ( '' === $search ) {
            return $stack_users;
        }

        $needle = strtolower( $search );

        return array_values(
            array_filter(
                $stack_users,
                static function ( $stack_user ) use ( $needle ) {
                    foreach ( [ 'id', 'label', 'name', 'email', 'company' ] as $key ) {
                        if ( ! empty( $stack_user[ $key ] ) && false !== strpos( strtolower( (string) $stack_user[ $key ] ), $needle ) ) {
                            return true;
                        }
                    }

                    return false;
                }
            )
        );
    }

    /**
     * Find a StackCP user by ID.
     *
     * @param string $stack_user_id StackCP user ID.
     * @param array  $stack_users User list.
     * @return array|null
     */
    protected function find_stack_user_by_id( $stack_user_id, $stack_users = [] ) {
        if ( empty( $stack_users ) ) {
            $stack_users = $this->get_stack_users();
            if ( is_wp_error( $stack_users ) ) {
                return null;
            }
        }

        foreach ( $stack_users as $stack_user ) {
            if ( isset( $stack_user['id'] ) && (string) $stack_user['id'] === (string) $stack_user_id ) {
                return $stack_user;
            }
        }

        return null;
    }

    /**
     * Build package map by StackCP user.
     *
     * @return array<string,array<int,array<string,mixed>>>
     */
    protected function build_stack_user_package_map() {
        $packages = $this->get_packages();
        $map      = [];

        if ( is_wp_error( $packages ) || ! is_array( $packages ) ) {
            return $map;
        }

        foreach ( $packages as $package ) {
            if ( empty( $package['stackUsers'] ) || ! is_array( $package['stackUsers'] ) ) {
                continue;
            }

            foreach ( $package['stackUsers'] as $stack_user_id ) {
                $stack_user_id = (string) $stack_user_id;
                if ( '' === $stack_user_id ) {
                    continue;
                }

                if ( ! isset( $map[ $stack_user_id ] ) ) {
                    $map[ $stack_user_id ] = [];
                }

                $map[ $stack_user_id ][] = $package;
            }
        }

        return $map;
    }

    /**
     * Get StackCP users attached to a package summary.
     *
     * @param array $summary Package summary.
     * @return array<int,array<string,mixed>>
     */
    protected function get_stack_users_for_package_summary( $summary ) {
        $assigned_ids = isset( $summary['stackUsers'] ) && is_array( $summary['stackUsers'] ) ? $summary['stackUsers'] : [];
        $stack_users  = $this->get_stack_users();
        $results      = [];

        if ( is_wp_error( $stack_users ) ) {
            return $results;
        }

        foreach ( $assigned_ids as $stack_user_id ) {
            $stack_user = $this->find_stack_user_by_id( (string) $stack_user_id, $stack_users );
            if ( ! empty( $stack_user ) ) {
                $results[] = $stack_user;
            } else {
                $results[] = [
                    'id'    => (string) $stack_user_id,
                    'label' => (string) $stack_user_id,
                    'name'  => '',
                    'email' => '',
                ];
            }
        }

        return $results;
    }

    /**
     * Build a short summary of a grant map.
     *
     * @param array $grant_map Grant map.
     * @return string
     */
    protected function summarize_grant_map( $grant_map ) {
        if ( ! is_array( $grant_map ) || empty( $grant_map ) ) {
            return '';
        }

        $parts = [];
        foreach ( $grant_map as $key => $value ) {
            if ( is_array( $value ) ) {
                $parts[] = sprintf( '%1$s: %2$d', (string) $key, count( $value ) );
            } elseif ( is_scalar( $value ) && '' !== (string) $value ) {
                $parts[] = sprintf( '%1$s: %2$s', (string) $key, (string) $value );
            }

            if ( count( $parts ) >= 3 ) {
                break;
            }
        }

        return implode( ', ', $parts );
    }

    /**
     * Build a subject line for client handoff.
     *
     * @param string $package_label Package label.
     * @return string
     */
    protected function build_handoff_subject( $package_label ) {
        return sprintf( __( '%1$s hosting access for %2$s', 'twentyi-hosting-browser' ), $this->get_brand_name(), $package_label );
    }

    /**
     * Build a handoff message for a StackCP user.
     *
     * @param array $stack_user StackCP user details.
     * @param array $packages Packages to mention.
     * @param array $context Optional context.
     * @return string
     */
    protected function build_stackcp_handoff_message( $stack_user, $packages = [], $context = [] ) {
        $name         = ! empty( $stack_user['name'] ) ? (string) $stack_user['name'] : __( 'there', 'twentyi-hosting-browser' );
        $brand_name   = $this->get_brand_name();
        $login_url    = $this->get_stackcp_login_url();
        $support_email = $this->get_support_email();
        $username     = ! empty( $stack_user['email'] ) ? (string) $stack_user['email'] : ( ! empty( $stack_user['id'] ) ? (string) $stack_user['id'] : '' );

        $lines   = [];
        $lines[] = sprintf( __( 'Hi %s,', 'twentyi-hosting-browser' ), $name );
        $lines[] = '';
        $lines[] = sprintf( __( 'Your hosting access is ready from %s.', 'twentyi-hosting-browser' ), $brand_name );
        $lines[] = sprintf( __( 'StackCP login: %s', 'twentyi-hosting-browser' ), $login_url );

        if ( '' !== $username ) {
            $lines[] = sprintf( __( 'Username: %s', 'twentyi-hosting-browser' ), $username );
        }

        if ( ! empty( $packages ) ) {
            $lines[] = '';
            $lines[] = __( 'Services included:', 'twentyi-hosting-browser' );
            foreach ( $packages as $package ) {
                $package_name = isset( $package['name'] ) ? (string) $package['name'] : '';
                $package_id   = isset( $package['id'] ) ? (string) $package['id'] : '';
                $line         = '- ' . ( $package_name ?: $package_id );
                if ( ! empty( $package['label'] ) ) {
                    $line .= ' (' . (string) $package['label'] . ')';
                }
                $lines[] = $line;
            }
        }

        if ( ! empty( $context['site_url'] ) ) {
            $lines[] = '';
            $lines[] = sprintf( __( 'Website URL: %s', 'twentyi-hosting-browser' ), (string) $context['site_url'] );
        }

        $lines[] = '';
        $lines[] = __( 'If you already have a StackCP password, you can use it to sign in now. If not, set or reset the password from the StackCP login screen or from My20i before sending this handoff.', 'twentyi-hosting-browser' );

        if ( '' !== $support_email ) {
            $lines[] = sprintf( __( 'Support: %s', 'twentyi-hosting-browser' ), $support_email );
        }

        $lines[] = '';
        $lines[] = sprintf( __( 'Thanks,%s%s', 'twentyi-hosting-browser' ), PHP_EOL, $brand_name );

        return implode( PHP_EOL, $lines );
    }


    /**
     * Search for a domain via the 20i API.
     *
     * @param string $domain Domain name.
     * @return array|WP_Error
     */
    protected function search_domain_availability( $domain ) {
        $domain = $this->sanitize_domain( $domain );

        if ( '' === $domain ) {
            return new WP_Error( 'twentyi_invalid_domain_search', __( 'Please enter a valid domain name to search.', 'twentyi-hosting-browser' ) );
        }

        return $this->request( '/domain-search/' . rawurlencode( $domain ) );
    }

    /**
     * Normalize a domain search response for display.
     *
     * @param string $domain Domain name.
     * @param array  $result Raw result.
     * @return array<string,mixed>
     */
    protected function normalize_domain_search_result( $domain, $result ) {
        $flat       = $this->flatten_scalar_data( $result );
        $available  = $this->extract_boolish_flag_from_flattened_data( $flat, [ 'available', 'is_available', 'isavailable', 'availability', 'free', 'can_register' ] );
        $fields     = [];
        $summary    = '';
        $suggestions = [];

        if ( null === $available ) {
            foreach ( $flat as $key => $value ) {
                if ( is_string( $value ) ) {
                    $lower = strtolower( $value );
                    if ( false !== strpos( $lower, 'available' ) && false === strpos( $lower, 'unavailable' ) ) {
                        $available = true;
                        break;
                    }
                    if ( false !== strpos( $lower, 'unavailable' ) || false !== strpos( $lower, 'taken' ) || false !== strpos( $lower, 'registered' ) ) {
                        $available = false;
                        break;
                    }
                }
            }
        }

        foreach ( $flat as $key => $value ) {
            if ( count( $fields ) >= 8 ) {
                break;
            }

            $label = ucwords( str_replace( [ '_', '.', '-' ], ' ', (string) $key ) );
            $value = is_scalar( $value ) ? (string) $value : '';
            if ( '' === $value ) {
                continue;
            }

            $fields[] = [
                'field' => $label,
                'value' => $value,
            ];
        }

        $suggestions = $this->extract_domain_suggestions_from_result( $result );

        if ( true === $available ) {
            $summary = __( 'This domain appears to be available for registration.', 'twentyi-hosting-browser' );
        } elseif ( false === $available ) {
            $summary = __( 'This domain appears to be unavailable, so you may need an alternative or a transfer instead.', 'twentyi-hosting-browser' );
        } else {
            $summary = __( '20i returned a domain search response, but the availability flag was not obvious.', 'twentyi-hosting-browser' );
        }

        return [
            'domain'      => $domain,
            'available'   => $available,
            'summary'     => $summary,
            'fields'      => $fields,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Extract boolish availability flags from flattened data.
     *
     * @param array $flat Flattened scalar data.
     * @param array $candidate_keys Key names to inspect.
     * @return bool|null
     */
    protected function extract_boolish_flag_from_flattened_data( $flat, $candidate_keys ) {
        foreach ( $flat as $key => $value ) {
            $last_key = strtolower( preg_replace( '/^.*[._]/', '', (string) $key ) );

            if ( ! in_array( $last_key, $candidate_keys, true ) ) {
                continue;
            }

            if ( is_bool( $value ) ) {
                return $value;
            }

            $value_string = strtolower( trim( (string) $value ) );

            if ( in_array( $value_string, [ '1', 'true', 'yes', 'available', 'free' ], true ) ) {
                return true;
            }

            if ( in_array( $value_string, [ '0', 'false', 'no', 'unavailable', 'taken', 'registered' ], true ) ) {
                return false;
            }
        }

        return null;
    }

    /**
     * Extract domain suggestions from a search response.
     *
     * @param mixed $data Search response.
     * @return array<int,string>
     */
    protected function extract_domain_suggestions_from_result( $data ) {
        $suggestions = [];

        $walker = static function ( $value ) use ( &$walker, &$suggestions ) {
            if ( is_array( $value ) ) {
                foreach ( $value as $item ) {
                    $walker( $item );
                }
                return;
            }

            if ( ! is_scalar( $value ) ) {
                return;
            }

            $string = strtolower( trim( (string) $value ) );
            $string = preg_replace( '#^https?://#', '', $string );
            $string = trim( (string) $string, ". \t\n\r\0\x0B" );

            if ( preg_match( '/^[a-z0-9.-]+\.[a-z]{2,}$/', $string ) ) {
                $suggestions[] = $string;
            }
        };

        $walker( $data );

        $suggestions = array_values( array_unique( array_filter( array_map( [ $this, 'sanitize_domain' ], $suggestions ) ) ) );

        return array_slice( $suggestions, 0, 8 );
    }

    /**
     * Return the canonical 20i nameserver set.
     *
     * @return array<int,string>
     */
    protected function get_twentyi_nameservers() {
        return [
            'ns1.stackdns.com',
            'ns2.stackdns.com',
            'ns3.stackdns.com',
            'ns4.stackdns.com',
        ];
    }

    /**
     * Get nameserver and SSL readiness data for a domain.
     *
     * @param string $domain Domain name.
     * @return array<string,mixed>
     */
    protected function get_domain_nameserver_status( $domain ) {
        $domain        = $this->sanitize_domain( $domain );
        $nameservers   = [];
        $expected      = $this->get_twentyi_nameservers();
        $using_twentyi = null;
        $ssl_ready     = null;
        $acme_cname    = '';

        if ( function_exists( 'dns_get_record' ) && defined( 'DNS_NS' ) ) {
            $ns_records = @dns_get_record( $domain, DNS_NS );
            if ( is_array( $ns_records ) ) {
                foreach ( $ns_records as $record ) {
                    if ( empty( $record['target'] ) ) {
                        continue;
                    }
                    $nameservers[] = strtolower( rtrim( (string) $record['target'], '.' ) );
                }
            }

            $nameservers = array_values( array_unique( array_filter( $nameservers ) ) );

            if ( ! empty( $nameservers ) ) {
                $diff_from_expected = array_diff( $nameservers, $expected );
                $matches_expected   = count( array_intersect( $nameservers, $expected ) );

                if ( 0 === count( $diff_from_expected ) && $matches_expected > 0 ) {
                    $using_twentyi = true;
                } else {
                    $using_twentyi = false;
                }
            }

            if ( defined( 'DNS_CNAME' ) ) {
                $acme_records = @dns_get_record( '_acme-challenge.' . $domain, DNS_CNAME );
                if ( is_array( $acme_records ) ) {
                    foreach ( $acme_records as $record ) {
                        if ( ! empty( $record['target'] ) ) {
                            $acme_cname = strtolower( rtrim( (string) $record['target'], '.' ) );
                            break;
                        }
                    }
                }
            }
        }

        if ( true === $using_twentyi ) {
            $ssl_ready = '' === $acme_cname;
        } elseif ( false === $using_twentyi ) {
            $ssl_ready = false;
        }

        return [
            'nameservers'         => $nameservers,
            'expected'            => $expected,
            'using_twentyi'       => $using_twentyi,
            'ssl_ready'           => $ssl_ready,
            'acme_challenge_cname'=> $acme_cname,
        ];
    }

    /**
     * Build a public DNS snapshot for a domain.
     *
     * @param string $domain Domain name.
     * @return array<string,mixed>
     */
    protected function get_public_dns_snapshot( $domain ) {
        $domain = $this->sanitize_domain( $domain );
        $rows   = [];
        $www    = 'www.' . $domain;

        if ( ! function_exists( 'dns_get_record' ) ) {
            return [
                'rows'       => [],
                'www_target' => '',
            ];
        }

        $queries = [
            [ 'host' => $domain, 'type' => 'A',     'flag' => defined( 'DNS_A' ) ? DNS_A : null ],
            [ 'host' => $domain, 'type' => 'AAAA',  'flag' => defined( 'DNS_AAAA' ) ? DNS_AAAA : null ],
            [ 'host' => $domain, 'type' => 'CNAME', 'flag' => defined( 'DNS_CNAME' ) ? DNS_CNAME : null ],
            [ 'host' => $domain, 'type' => 'MX',    'flag' => defined( 'DNS_MX' ) ? DNS_MX : null ],
            [ 'host' => $domain, 'type' => 'TXT',   'flag' => defined( 'DNS_TXT' ) ? DNS_TXT : null ],
            [ 'host' => $domain, 'type' => 'CAA',   'flag' => defined( 'DNS_CAA' ) ? DNS_CAA : null ],
            [ 'host' => $www,    'type' => 'A',     'flag' => defined( 'DNS_A' ) ? DNS_A : null ],
            [ 'host' => $www,    'type' => 'AAAA',  'flag' => defined( 'DNS_AAAA' ) ? DNS_AAAA : null ],
            [ 'host' => $www,    'type' => 'CNAME', 'flag' => defined( 'DNS_CNAME' ) ? DNS_CNAME : null ],
        ];

        foreach ( $queries as $query ) {
            if ( empty( $query['flag'] ) ) {
                continue;
            }

            $records = @dns_get_record( $query['host'], $query['flag'] );
            if ( ! is_array( $records ) ) {
                continue;
            }

            foreach ( $records as $record ) {
                $value = '';
                $notes = '';

                switch ( $query['type'] ) {
                    case 'A':
                        $value = isset( $record['ip'] ) ? (string) $record['ip'] : '';
                        break;
                    case 'AAAA':
                        $value = isset( $record['ipv6'] ) ? (string) $record['ipv6'] : '';
                        break;
                    case 'CNAME':
                        $value = isset( $record['target'] ) ? rtrim( (string) $record['target'], '.' ) : '';
                        break;
                    case 'MX':
                        $value = isset( $record['target'] ) ? rtrim( (string) $record['target'], '.' ) : '';
                        $notes = isset( $record['pri'] ) ? sprintf( __( 'Priority %d', 'twentyi-hosting-browser' ), intval( $record['pri'] ) ) : '';
                        break;
                    case 'TXT':
                        if ( isset( $record['txt'] ) ) {
                            $value = (string) $record['txt'];
                        } elseif ( isset( $record['entries'] ) && is_array( $record['entries'] ) ) {
                            $value = implode( ' ', array_map( 'strval', $record['entries'] ) );
                        }
                        break;
                    case 'CAA':
                        $tag   = isset( $record['tag'] ) ? (string) $record['tag'] : 'caa';
                        $entry = isset( $record['value'] ) ? (string) $record['value'] : '';
                        $value = trim( $tag . ' ' . $entry );
                        if ( isset( $record['flags'] ) ) {
                            $notes = sprintf( __( 'Flags %d', 'twentyi-hosting-browser' ), intval( $record['flags'] ) );
                        }
                        break;
                }

                if ( '' === $value ) {
                    continue;
                }

                if ( 'TXT' === $query['type'] && strlen( $value ) > 110 ) {
                    $value = substr( $value, 0, 107 ) . '...';
                }

                $rows[] = [
                    'host'  => $query['host'],
                    'type'  => $query['type'],
                    'value' => $value,
                    'notes' => $notes,
                ];
            }
        }

        $rows = array_values(
            array_map(
                'unserialize',
                array_unique(
                    array_map(
                        'serialize',
                        $rows
                    )
                )
            )
        );

        $www_target = '';
        foreach ( $rows as $row ) {
            if ( $www === $row['host'] && in_array( $row['type'], [ 'A', 'AAAA', 'CNAME' ], true ) ) {
                $www_target = $row['value'];
                break;
            }
        }

        return [
            'rows'       => $rows,
            'www_target' => $www_target,
        ];
    }

    /**
     * Build a copy-ready Google Workspace DNS snippet.
     *
     * @param string $domain Domain name.
     * @return string
     */
    protected function build_google_workspace_zone_snippet( $domain ) {
        $domain = $this->sanitize_domain( $domain );

        return implode(
            PHP_EOL,
            [
                '; Google Workspace preset for ' . $domain,
                '@ IN MX 1 smtp.google.com.',
                '@ IN TXT "v=spf1 include:_spf.google.com ~all"',
                '; Add your Google verification TXT separately if required.',
            ]
        );
    }

    /**
     * Render StackCP user detail box.
     *
     * @param array $stack_user StackCP user.
     * @param array $packages Linked packages.
     */
    protected function render_stack_user_detail_box( $stack_user, $packages = [] ) {
        ?>
        <div style="margin:16px 0 24px; background:#fff; border:1px solid #dcdcde; padding:16px 20px;">
            <h2 style="margin-top:0;"><?php esc_html_e( 'StackCP User Details', 'twentyi-hosting-browser' ); ?></h2>
            <table class="widefat striped" style="max-width:920px; margin-bottom:16px;">
                <tbody>
                    <tr>
                        <th style="width:220px;"><?php esc_html_e( 'Name', 'twentyi-hosting-browser' ); ?></th>
                        <td><?php echo esc_html( $stack_user['name'] ?: '—' ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Email', 'twentyi-hosting-browser' ); ?></th>
                        <td><?php echo ! empty( $stack_user['email'] ) ? esc_html( $stack_user['email'] ) : '—'; ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'StackCP User ID', 'twentyi-hosting-browser' ); ?></th>
                        <td><code><?php echo esc_html( $stack_user['id'] ); ?></code></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Company', 'twentyi-hosting-browser' ); ?></th>
                        <td><?php echo ! empty( $stack_user['company'] ) ? esc_html( $stack_user['company'] ) : '—'; ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Grant Summary', 'twentyi-hosting-browser' ); ?></th>
                        <td><?php echo ! empty( $stack_user['grants_summary'] ) ? esc_html( $stack_user['grants_summary'] ) : '—'; ?></td>
                    </tr>
                </tbody>
            </table>

            <h3><?php esc_html_e( 'Client Handoff Template', 'twentyi-hosting-browser' ); ?></h3>
            <p><strong><?php esc_html_e( 'Suggested subject:', 'twentyi-hosting-browser' ); ?></strong> <?php echo esc_html( $this->build_handoff_subject( ! empty( $packages[0]['name'] ) ? (string) $packages[0]['name'] : __( 'your hosting package', 'twentyi-hosting-browser' ) ) ); ?></p>
            <textarea readonly rows="12" class="large-text code"><?php echo esc_textarea( $this->build_stackcp_handoff_message( $stack_user, $packages ) ); ?></textarea>

            <?php if ( ! empty( $packages ) ) : ?>
                <h3><?php esc_html_e( 'Accessible Packages', 'twentyi-hosting-browser' ); ?></h3>
                <table class="widefat striped fixed">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Primary Domain', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Package ID', 'twentyi-hosting-browser' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'twentyi-hosting-browser' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $packages as $package ) : ?>
                            <tr>
                                <td><?php echo esc_html( $package['name'] ?? '—' ); ?></td>
                                <td><code><?php echo esc_html( $package['id'] ?? '—' ); ?></code></td>
                                <td><a class="button button-small" href="<?php echo esc_url( $this->get_package_page_url( (string) $package['id'] ) ); ?>"><?php esc_html_e( 'View Package', 'twentyi-hosting-browser' ); ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Parse domain list from textarea.
     *
     * @param string $value Raw textarea value.
     * @return array<int,string>
     */
    protected function parse_domain_list( $value ) {
        $value   = str_replace( [ "\r\n", "\r" ], "\n", (string) $value );
        $value   = str_replace( ',', "\n", $value );
        $domains = array_filter( array_map( 'trim', explode( "\n", $value ) ) );
        $clean   = [];

        foreach ( $domains as $domain ) {
            $domain = $this->sanitize_domain( $domain );
            if ( '' !== $domain ) {
                $clean[] = $domain;
            }
        }

        return array_values( array_unique( $clean ) );
    }

    /**
     * Parse document root mappings.
     *
     * @param string $value Raw textarea value.
     * @return array<string,string>
     */
    protected function parse_document_roots( $value ) {
        $value   = str_replace( [ "\r\n", "\r" ], "\n", (string) $value );
        $lines   = array_filter( array_map( 'trim', explode( "\n", $value ) ) );
        $results = [];

        foreach ( $lines as $line ) {
            if ( false === strpos( $line, '=' ) ) {
                continue;
            }

            list( $domain, $root ) = array_map( 'trim', explode( '=', $line, 2 ) );
            $domain = $this->sanitize_domain( $domain );
            $root   = sanitize_text_field( $root );

            if ( '' !== $domain && '' !== $root ) {
                $results[ $domain ] = $root;
            }
        }

        return $results;
    }

    /**
     * Sanitize domain-like strings.
     *
     * @param string $domain Raw domain.
     * @return string
     */
    protected function sanitize_domain( $domain ) {
        $domain = strtolower( trim( (string) $domain ) );
        $domain = preg_replace( '#^https?://#', '', $domain );
        $domain = preg_replace( '#/.*$#', '', $domain );
        $domain = trim( (string) $domain, ". \t\n\r\0\x0B" );

        if ( ! preg_match( '/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain ) ) {
            return '';
        }

        return $domain;
    }

    /**
     * Whether post-clone WordPress admin bootstrap is enabled.
     *
     * @return bool
     */
    protected function bootstrap_enabled() {
        return (bool) get_option( self::OPTION_BOOTSTRAP_ENABLED, 0 );
    }

    /**
     * Get or create the shared Bootstrap helper secret.
     *
     * @return string
     */
    protected function get_bootstrap_secret() {
        $secret = trim( (string) get_option( self::OPTION_BOOTSTRAP_SECRET, '' ) );

        if ( '' === $secret ) {
            $secret = wp_generate_password( 40, false, false );
            update_option( self::OPTION_BOOTSTRAP_SECRET, $secret, false );
        }

        return $secret;
    }

    /**
     * Role to assign on the cloned WordPress site.
     *
     * @return string
     */
    protected function get_bootstrap_role() {
        return $this->sanitize_bootstrap_role( get_option( self::OPTION_BOOTSTRAP_ROLE, 'administrator' ) );
    }

    /**
     * Whether the cloned site should email the client a password setup link.
     *
     * @return bool
     */
    protected function bootstrap_sends_reset_email() {
        return (bool) get_option( self::OPTION_BOOTSTRAP_SEND_RESET, 1 );
    }

    /**
     * Get maximum bootstrap attempts.
     *
     * @return int
     */
    protected function get_bootstrap_max_attempts() {
        return max( 1, absint( get_option( self::OPTION_BOOTSTRAP_MAX_ATTEMPTS, 12 ) ) );
    }

    /**
     * Get delay between bootstrap attempts in seconds.
     *
     * @return int
     */
    protected function get_bootstrap_retry_delay() {
        return max( 60, absint( get_option( self::OPTION_BOOTSTRAP_RETRY_DELAY, 180 ) ) );
    }

    /**
     * Whether to try the 20i temporary URL before the live domain.
     *
     * @return bool
     */
    protected function bootstrap_uses_temporary_url() {
        return (bool) get_option( self::OPTION_BOOTSTRAP_USE_TEMP_URL, 1 );
    }

    /**
     * Get the 20i temporary URL base domain.
     *
     * @return string
     */
    protected function get_bootstrap_temp_url_domain() {
        $domain = $this->sanitize_domain( get_option( self::OPTION_BOOTSTRAP_TEMP_URL_DOMAIN, 'stackstaging.com' ) );

        return '' !== $domain ? $domain : 'stackstaging.com';
    }

    /**
     * Build the 20i temporary URL host from a package domain.
     *
     * 20i temporary URLs use the format example-co-uk.stackstaging.com.
     *
     * @param string $domain Live/package domain.
     * @return string
     */
    protected function get_bootstrap_temporary_host( $domain ) {
        $domain = $this->sanitize_domain( $domain );
        $base   = $this->get_bootstrap_temp_url_domain();

        if ( '' === $domain ) {
            return '';
        }

        if ( $domain === $base || substr( $domain, -1 * ( strlen( $base ) + 1 ) ) === '.' . $base ) {
            return $domain;
        }

        return str_replace( '.', '-', $domain ) . '.' . $base;
    }

    /**
     * Queue a post-clone WordPress admin bootstrap job.
     *
     * @param int    $request_id Request post ID.
     * @param string $package_id 20i package ID.
     * @param string $domain Domain name.
     * @param string $email Client email.
     * @param string $name Client name.
     * @param string $business Business/site name.
     * @param bool   $force Reset existing attempts.
     * @return bool
     */
    protected function schedule_wordpress_admin_bootstrap( $request_id, $package_id, $domain, $email, $name = '', $business = '', $force = false ) {
        $request_id = absint( $request_id );
        $domain     = $this->sanitize_domain( $domain );
        $email      = sanitize_email( $email );

        if ( ! $this->bootstrap_enabled() || ! $request_id || '' === $domain || ! is_email( $email ) ) {
            return false;
        }

        if ( $force ) {
            update_post_meta( $request_id, 'wp_admin_bootstrap_attempts', 0 );
            delete_post_meta( $request_id, 'wp_admin_bootstrap_error' );
        }

        update_post_meta( $request_id, 'wp_admin_bootstrap_status', 'queued' );
        update_post_meta( $request_id, 'wp_admin_bootstrap_package_id', sanitize_text_field( $package_id ) );
        update_post_meta( $request_id, 'wp_admin_bootstrap_domain', $domain );
        update_post_meta( $request_id, 'wp_admin_bootstrap_email', $email );
        update_post_meta( $request_id, 'wp_admin_bootstrap_name', sanitize_text_field( $name ) );
        update_post_meta( $request_id, 'wp_admin_bootstrap_business', sanitize_text_field( $business ) );
        update_post_meta( $request_id, 'wp_admin_bootstrap_role', $this->get_bootstrap_role() );

        $args = [ $request_id ];
        wp_clear_scheduled_hook( self::CRON_BOOTSTRAP_HOOK, $args );
        wp_schedule_single_event( time() + 60, self::CRON_BOOTSTRAP_HOOK, $args );

        $this->add_activity_log(
            'wp_admin_bootstrap_queue',
            sprintf(
                /* translators: %s: domain */
                __( 'Queued WordPress admin bootstrap for %s.', 'twentyi-hosting-browser' ),
                $domain
            ),
            [
                'request_id' => $request_id,
                'package_id' => $package_id,
                'domain'     => $domain,
                'email'      => $email,
            ],
            'info'
        );

        return true;
    }

    /**
     * Process one post-clone WordPress admin bootstrap job.
     *
     * @param int $request_id Request ID.
     */
    public function process_wordpress_admin_bootstrap_job( $request_id ) {
        $request_id = absint( $request_id );

        if ( ! $request_id || self::REQUEST_POST_TYPE !== get_post_type( $request_id ) ) {
            return;
        }

        if ( ! $this->bootstrap_enabled() ) {
            update_post_meta( $request_id, 'wp_admin_bootstrap_status', 'disabled' );
            return;
        }

        $status = (string) get_post_meta( $request_id, 'wp_admin_bootstrap_status', true );
        if ( 'created' === $status ) {
            return;
        }

        $domain   = $this->sanitize_domain( get_post_meta( $request_id, 'wp_admin_bootstrap_domain', true ) ?: get_post_meta( $request_id, 'domain_name', true ) );
        $email    = sanitize_email( get_post_meta( $request_id, 'wp_admin_bootstrap_email', true ) ?: get_post_meta( $request_id, 'email', true ) );
        $name     = sanitize_text_field( get_post_meta( $request_id, 'wp_admin_bootstrap_name', true ) ?: get_post_meta( $request_id, 'contact_name', true ) );
        $business = sanitize_text_field( get_post_meta( $request_id, 'wp_admin_bootstrap_business', true ) ?: get_post_meta( $request_id, 'business_name', true ) );
        $package  = sanitize_text_field( get_post_meta( $request_id, 'wp_admin_bootstrap_package_id', true ) ?: get_post_meta( $request_id, 'package_id', true ) );
        $attempts = (int) get_post_meta( $request_id, 'wp_admin_bootstrap_attempts', true );
        $attempts++;

        update_post_meta( $request_id, 'wp_admin_bootstrap_attempts', $attempts );
        update_post_meta( $request_id, 'wp_admin_bootstrap_status', 'checking_clone' );

        if ( '' === $domain || ! is_email( $email ) ) {
            update_post_meta( $request_id, 'wp_admin_bootstrap_status', 'failed' );
            update_post_meta( $request_id, 'wp_admin_bootstrap_error', __( 'Missing domain or valid client email for WordPress admin bootstrap.', 'twentyi-hosting-browser' ) );
            return;
        }

        $payload = [
            'token'      => $this->get_bootstrap_secret(),
            'email'      => $email,
            'name'       => $name,
            'business'   => $business,
            'role'       => $this->get_bootstrap_role(),
            'send_reset'      => $this->bootstrap_sends_reset_email(),
            'return_password' => $this->welcome_email_includes_temporary_password(),
            'request_id'      => $request_id,
            'package_id' => $package,
        ];

        $result = $this->call_blueprint_bootstrap_endpoint( $domain, $payload );

        if ( is_wp_error( $result ) ) {
            $message = $result->get_error_message();
            update_post_meta( $request_id, 'wp_admin_bootstrap_error', $message );

            if ( $attempts < $this->get_bootstrap_max_attempts() ) {
                update_post_meta( $request_id, 'wp_admin_bootstrap_status', 'waiting_for_clone' );
                wp_schedule_single_event( time() + $this->get_bootstrap_retry_delay(), self::CRON_BOOTSTRAP_HOOK, [ $request_id ] );
                $this->add_activity_log( 'wp_admin_bootstrap_retry', $message, [ 'request_id' => $request_id, 'domain' => $domain, 'attempt' => $attempts ], 'info' );
                return;
            }

            update_post_meta( $request_id, 'wp_admin_bootstrap_status', 'failed' );
            $this->add_activity_log( 'wp_admin_bootstrap_failed', $message, [ 'request_id' => $request_id, 'domain' => $domain, 'attempts' => $attempts ], 'error' );
            return;
        }

        update_post_meta( $request_id, 'wp_admin_bootstrap_status', 'created' );
        update_post_meta( $request_id, 'wp_admin_bootstrap_created_at', current_time( 'mysql' ) );
        update_post_meta( $request_id, 'wp_admin_bootstrap_response', $result );
        if ( is_array( $result ) && ! empty( $result['_endpoint'] ) ) {
            update_post_meta( $request_id, 'wp_admin_bootstrap_endpoint', esc_url_raw( $result['_endpoint'] ) );
        }
        delete_post_meta( $request_id, 'wp_admin_bootstrap_error' );

        $welcome_result = $this->send_client_welcome_email( $request_id, is_array( $result ) ? $result : [], false );
        if ( is_wp_error( $welcome_result ) ) {
            $this->add_activity_log( 'welcome_email_failed', $welcome_result->get_error_message(), [ 'request_id' => $request_id, 'domain' => $domain, 'email' => $email ], 'error' );
        }

        $this->add_activity_log(
            'wp_admin_bootstrap_created',
            sprintf(
                /* translators: 1: email, 2: domain */
                __( 'Created WordPress admin %1$s on cloned site %2$s.', 'twentyi-hosting-browser' ),
                $email,
                $domain
            ),
            [
                'request_id' => $request_id,
                'package_id' => $package,
                'domain'     => $domain,
                'email'      => $email,
                'password_included' => ( is_array( $result ) && ! empty( $result['temporary_password'] ) ) ? 'yes' : 'no',
            ],
            'success'
        );
    }

    /**
     * Call the Blueprint Bootstrap Helper endpoint on the cloned site.
     *
     * @param string $domain Domain name.
     * @param array  $payload Payload.
     * @return array|WP_Error
     */
    protected function call_blueprint_bootstrap_endpoint( $domain, $payload ) {
        $domain          = $this->sanitize_domain( $domain );
        $temporary_host  = $this->get_bootstrap_temporary_host( $domain );
        $candidate_hosts = [];

        if ( $this->bootstrap_uses_temporary_url() && '' !== $temporary_host ) {
            $candidate_hosts[] = $temporary_host;
        }

        if ( '' !== $domain ) {
            $candidate_hosts[] = $domain;
        }

        $candidate_hosts = array_values( array_unique( array_filter( $candidate_hosts ) ) );
        $endpoints       = [];

        foreach ( $candidate_hosts as $host ) {
            $endpoints[] = 'https://' . $host . '/wp-json/twentyi-blueprint-bootstrap/v1/create-admin';
            $endpoints[] = 'http://' . $host . '/wp-json/twentyi-blueprint-bootstrap/v1/create-admin';
        }

        $errors = [];

        foreach ( $endpoints as $endpoint ) {
            $response = wp_remote_post(
                $endpoint,
                [
                    'timeout' => 20,
                    'headers' => [
                        'Accept'                    => 'application/json',
                        'Content-Type'              => 'application/json',
                        'X-TwentyI-Bootstrap-Token' => (string) $payload['token'],
                    ],
                    'body'    => wp_json_encode( $payload ),
                ]
            );

            if ( is_wp_error( $response ) ) {
                $errors[] = $endpoint . ': ' . $response->get_error_message();
                continue;
            }

            $code = (int) wp_remote_retrieve_response_code( $response );
            $body = (string) wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );

            if ( $code >= 200 && $code < 300 && is_array( $data ) && ! empty( $data['success'] ) ) {
                $data['_endpoint']       = $endpoint;
                $data['_temporary_host'] = $temporary_host;
                $data['_live_domain']    = $domain;

                return $data;
            }

            $message = '';
            if ( is_array( $data ) && ! empty( $data['message'] ) ) {
                $message = (string) $data['message'];
            } elseif ( '' !== trim( $body ) ) {
                $message = wp_strip_all_tags( $body );
            } else {
                $message = sprintf( 'HTTP %d', $code );
            }

            $errors[] = $endpoint . ': ' . $message;
        }

        return new WP_Error(
            'twentyi_bootstrap_endpoint_unavailable',
            sprintf(
                /* translators: 1: domain, 2: temporary URL, 3: error list */
                __( 'The cloned WordPress site is not ready yet. Checked live domain %1$s and temporary URL %2$s. Last checks: %3$s', 'twentyi-hosting-browser' ),
                '' !== $domain ? $domain : __( 'unknown', 'twentyi-hosting-browser' ),
                '' !== $temporary_host ? $temporary_host : __( 'not available', 'twentyi-hosting-browser' ),
                implode( ' | ', array_slice( $errors, -4 ) )
            )
        );
    }


    /**
     * Whether client welcome emails are enabled.
     *
     * @return bool
     */
    protected function welcome_email_enabled() {
        return (bool) get_option( self::OPTION_WELCOME_EMAIL_ENABLED, 1 );
    }

    /**
     * Whether to BCC admin on welcome emails.
     *
     * @return bool
     */
    protected function welcome_email_sends_admin_copy() {
        return (bool) get_option( self::OPTION_WELCOME_EMAIL_ADMIN_COPY, 0 );
    }


    /**
     * Welcome email password delivery mode.
     *
     * @return string
     */
    protected function get_welcome_email_password_mode() {
        return $this->sanitize_welcome_email_password_mode( get_option( self::OPTION_WELCOME_EMAIL_PASSWORD_MODE, 'reset_link' ) );
    }

    /**
     * Whether to request a one-time temporary password from the Blueprint helper.
     *
     * @return bool
     */
    protected function welcome_email_includes_temporary_password() {
        return 'temporary_password' === $this->get_welcome_email_password_mode();
    }

    /**
     * Default welcome email subject.
     *
     * @return string
     */
    protected function get_default_welcome_email_subject() {
        return __( 'Your {brand_name} website login is ready', 'twentyi-hosting-browser' );
    }

    /**
     * Default welcome email body.
     *
     * @return string
     */
    protected function get_default_welcome_email_body() {
        return __( "Hi {client_name},\n\nYour new website has been created and your WordPress admin login is ready.\n\nWebsite: {site_url}\nTemporary preview: {temporary_url}\nWordPress login: {wp_login_url}\nUsername / email: {client_email}\n\nYou should receive a separate password setup email from the website. Use that email to set your password before logging in.\n\nNeed a hand? Contact us at {support_email}.\n\nThanks,\n{brand_name}", 'twentyi-hosting-browser' );
    }

    /**
     * Get welcome email subject template.
     *
     * @return string
     */
    protected function get_welcome_email_subject_template() {
        $subject = trim( (string) get_option( self::OPTION_WELCOME_EMAIL_SUBJECT, '' ) );
        return '' !== $subject ? $subject : $this->get_default_welcome_email_subject();
    }

    /**
     * Get welcome email body template.
     *
     * @return string
     */
    protected function get_welcome_email_body_template() {
        $body = trim( (string) get_option( self::OPTION_WELCOME_EMAIL_BODY, '' ) );
        return '' !== $body ? $body : $this->get_default_welcome_email_body();
    }

    /**
     * Send the client welcome email for a website request.
     *
     * @param int   $request_id Request ID.
     * @param array $bootstrap_result Result returned by the Blueprint helper.
     * @param bool  $force Send even if already sent or auto-send is disabled.
     * @return true|WP_Error
     */
    protected function send_client_welcome_email( $request_id, $bootstrap_result = [], $force = false ) {
        $request_id = absint( $request_id );

        if ( ! $request_id || self::REQUEST_POST_TYPE !== get_post_type( $request_id ) ) {
            return new WP_Error( 'twentyi_welcome_invalid_request', __( 'Website request not found for welcome email.', 'twentyi-hosting-browser' ) );
        }

        if ( ! $force && ! $this->welcome_email_enabled() ) {
            update_post_meta( $request_id, 'welcome_email_status', 'disabled' );
            return true;
        }

        if ( ! $force && 'sent' === (string) get_post_meta( $request_id, 'welcome_email_status', true ) ) {
            return true;
        }

        $email = sanitize_email( get_post_meta( $request_id, 'email', true ) );
        if ( ! is_email( $email ) ) {
            update_post_meta( $request_id, 'welcome_email_status', 'failed' );
            update_post_meta( $request_id, 'welcome_email_error', __( 'Cannot send welcome email because the request email address is invalid.', 'twentyi-hosting-browser' ) );
            return new WP_Error( 'twentyi_welcome_invalid_email', __( 'Cannot send welcome email because the request email address is invalid.', 'twentyi-hosting-browser' ) );
        }

        $domain         = $this->sanitize_domain( get_post_meta( $request_id, 'domain_name', true ) );
        $temporary_host = $domain ? $this->get_bootstrap_temporary_host( $domain ) : '';
        $endpoint       = isset( $bootstrap_result['_endpoint'] ) ? esc_url_raw( $bootstrap_result['_endpoint'] ) : esc_url_raw( (string) get_post_meta( $request_id, 'wp_admin_bootstrap_endpoint', true ) );

        $temporary_password = '';
        if ( $this->welcome_email_includes_temporary_password() && is_array( $bootstrap_result ) && ! empty( $bootstrap_result['temporary_password'] ) ) {
            $temporary_password = (string) $bootstrap_result['temporary_password'];
        }

        $password_note = '' !== $temporary_password
            ? __( 'This is a temporary password. Please log in and change it as soon as possible.', 'twentyi-hosting-browser' )
            : __( 'Use the password setup/reset link from WordPress to set your password before logging in.', 'twentyi-hosting-browser' );

        $placeholders = [
            '{brand_name}'         => $this->get_brand_name(),
            '{business_name}'      => sanitize_text_field( get_post_meta( $request_id, 'business_name', true ) ),
            '{client_name}'        => sanitize_text_field( get_post_meta( $request_id, 'contact_name', true ) ) ?: $email,
            '{client_email}'       => $email,
            '{domain}'             => $domain,
            '{site_url}'           => $domain ? 'https://' . $domain : '',
            '{temporary_url}'      => $temporary_host ? 'https://' . $temporary_host : '',
            '{wp_login_url}'       => $temporary_host ? 'https://' . $temporary_host . '/wp-login.php' : ( $domain ? 'https://' . $domain . '/wp-login.php' : '' ),
            '{wp_temp_password}'   => '' !== $temporary_password ? $temporary_password : __( 'Password setup/reset email will be sent separately.', 'twentyi-hosting-browser' ),
            '{password_note}'      => $password_note,
            '{stackcp_login_url}'  => $this->get_stackcp_login_url(),
            '{support_email}'      => $this->get_support_email() ?: get_option( 'admin_email' ),
            '{package_id}'         => sanitize_text_field( get_post_meta( $request_id, 'package_id', true ) ),
            '{bootstrap_endpoint}' => $endpoint,
        ];

        $subject = strtr( $this->get_welcome_email_subject_template(), $placeholders );
        $body    = strtr( $this->get_welcome_email_body_template(), $placeholders );

        $headers = [];
        $from_email = $this->get_support_email();
        if ( is_email( $from_email ) ) {
            $headers[] = 'Reply-To: ' . $this->get_brand_name() . ' <' . $from_email . '>';
        }

        if ( $this->welcome_email_sends_admin_copy() ) {
            $admin_email = get_option( 'admin_email' );
            if ( is_email( $admin_email ) ) {
                $headers[] = 'Bcc: ' . $admin_email;
            }
        }

        $sent = wp_mail( $email, $subject, $body, $headers );

        if ( ! $sent ) {
            update_post_meta( $request_id, 'welcome_email_status', 'failed' );
            update_post_meta( $request_id, 'welcome_email_error', __( 'wp_mail() could not send the welcome email. Check SMTP/mail delivery settings.', 'twentyi-hosting-browser' ) );
            return new WP_Error( 'twentyi_welcome_mail_failed', __( 'wp_mail() could not send the welcome email. Check SMTP/mail delivery settings.', 'twentyi-hosting-browser' ) );
        }

        update_post_meta( $request_id, 'welcome_email_status', 'sent' );
        update_post_meta( $request_id, 'welcome_email_sent_at', current_time( 'mysql' ) );
        update_post_meta( $request_id, 'welcome_email_subject', sanitize_text_field( $subject ) );
        update_post_meta( $request_id, 'welcome_email_password_mode', $this->get_welcome_email_password_mode() );
        update_post_meta( $request_id, 'welcome_email_included_temp_password', '' !== $temporary_password ? 1 : 0 );
        delete_post_meta( $request_id, 'welcome_email_error' );

        $this->add_activity_log(
            'welcome_email_sent',
            sprintf(
                /* translators: 1: email, 2: domain */
                __( 'Sent client welcome email to %1$s for %2$s.', 'twentyi-hosting-browser' ),
                $email,
                $domain ?: __( 'website request', 'twentyi-hosting-browser' )
            ),
            [
                'request_id'        => $request_id,
                'domain'            => $domain,
                'email'             => $email,
                'password_included' => '' !== $temporary_password ? 'yes' : 'no',
            ],
            'success'
        );

        return true;
    }

    /**
     * Render the 20i package fields on WooCommerce product edit screens.
     */
    public function render_woocommerce_product_package_fields() {
        if ( ! function_exists( 'woocommerce_wp_checkbox' ) || ! function_exists( 'woocommerce_wp_select' ) ) {
            return;
        }

        global $post;
        if ( ! $post || 'product' !== get_post_type( $post ) ) {
            return;
        }

        echo '<div class="options_group">';
        echo '<p class="form-field"><strong>' . esc_html__( '20i Hosting Package', 'twentyi-hosting-browser' ) . '</strong></p>';

        woocommerce_wp_checkbox(
            [
                'id'          => '_twentyi_wc_enable_provisioning',
                'label'       => __( 'Create 20i hosting package', 'twentyi-hosting-browser' ),
                'description' => __( 'When this WooCommerce product is purchased and paid, create the linked 20i hosting package.', 'twentyi-hosting-browser' ),
            ]
        );

        $options       = [ '' => __( 'Choose a 20i package type', 'twentyi-hosting-browser' ) ];
        $package_types = $this->get_package_types();

        if ( ! is_wp_error( $package_types ) && ! empty( $package_types ) ) {
            foreach ( $package_types as $package_type ) {
                if ( empty( $package_type['id'] ) ) {
                    continue;
                }
                $label = ! empty( $package_type['label'] ) ? (string) $package_type['label'] : (string) $package_type['id'];
                $options[ (string) $package_type['id'] ] = $label . ' (' . (string) $package_type['id'] . ')';
            }
        }

        woocommerce_wp_select(
            [
                'id'          => '_twentyi_wc_package_type',
                'label'       => __( '20i package type', 'twentyi-hosting-browser' ),
                'description' => __( 'Usually this should be one of your WordPress Blueprint package types.', 'twentyi-hosting-browser' ),
                'options'     => $options,
            ]
        );

        woocommerce_wp_text_input(
            [
                'id'          => '_twentyi_wc_package_label',
                'label'       => __( 'Package label override', 'twentyi-hosting-browser' ),
                'description' => __( 'Optional. Leave blank to use the customer website/business name.', 'twentyi-hosting-browser' ),
                'desc_tip'    => true,
            ]
        );

        echo '<p class="form-field"><span class="description">' . esc_html__( 'Checkout will ask the customer for their domain and website details. After payment, the plugin creates a Website Request record, provisions the 20i package, runs Blueprint admin bootstrap, and sends the welcome email.', 'twentyi-hosting-browser' ) . '</span></p>';
        echo '</div>';
    }

    /**
     * Save WooCommerce product package fields.
     *
     * @param int $post_id Product ID.
     */
    public function save_woocommerce_product_package_fields( $post_id ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $enabled = ! empty( $_POST['_twentyi_wc_enable_provisioning'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_twentyi_wc_enable_provisioning', $enabled );

        $package_type = sanitize_text_field( wp_unslash( $_POST['_twentyi_wc_package_type'] ?? '' ) );
        update_post_meta( $post_id, '_twentyi_wc_package_type', $package_type );

        $label = sanitize_text_field( wp_unslash( $_POST['_twentyi_wc_package_label'] ?? '' ) );
        update_post_meta( $post_id, '_twentyi_wc_package_label', $label );
    }

    /**
     * Render checkout fields when the cart contains a linked hosting product.
     *
     * @param WC_Checkout $checkout Checkout object.
     */
    public function render_woocommerce_checkout_fields( $checkout ) {
        if ( ! function_exists( 'WC' ) || ! function_exists( 'woocommerce_form_field' ) || ! $this->woocommerce_cart_contains_hosting_product() ) {
            return;
        }

        echo '<div id="twentyi-woocommerce-hosting-fields" style="margin:24px 0;padding:18px;border:1px solid #e5e7eb;border-radius:14px;background:#f8fafc;">';
        echo '<h3>' . esc_html__( 'Website Setup Details', 'twentyi-hosting-browser' ) . '</h3>';
        echo '<p>' . esc_html__( 'Add the details for the website we should create after your order is paid.', 'twentyi-hosting-browser' ) . '</p>';

        woocommerce_form_field(
            'twentyi_website_name',
            [
                'type'        => 'text',
                'class'       => [ 'form-row-wide' ],
                'label'       => __( 'Website / Business Name', 'twentyi-hosting-browser' ),
                'required'    => true,
                'placeholder' => __( 'Example: Pip\'s Pizza Shop', 'twentyi-hosting-browser' ),
            ],
            $checkout->get_value( 'twentyi_website_name' )
        );

        woocommerce_form_field(
            'twentyi_website_domain',
            [
                'type'        => 'text',
                'class'       => [ 'form-row-wide' ],
                'label'       => __( 'Domain Name', 'twentyi-hosting-browser' ),
                'required'    => true,
                'placeholder' => __( 'example.co.uk', 'twentyi-hosting-browser' ),
            ],
            $checkout->get_value( 'twentyi_website_domain' )
        );

        woocommerce_form_field(
            'twentyi_website_brief',
            [
                'type'        => 'textarea',
                'class'       => [ 'form-row-wide' ],
                'label'       => __( 'Website Notes', 'twentyi-hosting-browser' ),
                'required'    => false,
                'placeholder' => __( 'Tell us anything useful for setting up the website.', 'twentyi-hosting-browser' ),
            ],
            $checkout->get_value( 'twentyi_website_brief' )
        );

        echo '</div>';
    }

    /**
     * Validate checkout hosting fields.
     */
    public function validate_woocommerce_checkout_fields() {
        if ( ! $this->woocommerce_cart_contains_hosting_product() ) {
            return;
        }

        $domain = $this->sanitize_domain( wp_unslash( $_POST['twentyi_website_domain'] ?? '' ) );
        $name   = sanitize_text_field( wp_unslash( $_POST['twentyi_website_name'] ?? '' ) );

        if ( '' === $name ) {
            wc_add_notice( __( 'Please add the website or business name for your hosting package.', 'twentyi-hosting-browser' ), 'error' );
        }

        if ( '' === $domain ) {
            wc_add_notice( __( 'Please add a valid domain name for your hosting package.', 'twentyi-hosting-browser' ), 'error' );
        }
    }

    /**
     * Save checkout hosting fields to the WooCommerce order.
     *
     * @param int $order_id Order ID.
     */
    public function save_woocommerce_checkout_fields( $order_id ) {
        if ( ! $order_id ) {
            return;
        }

        if ( isset( $_POST['twentyi_website_name'] ) ) {
            update_post_meta( $order_id, '_twentyi_website_name', sanitize_text_field( wp_unslash( $_POST['twentyi_website_name'] ) ) );
        }

        if ( isset( $_POST['twentyi_website_domain'] ) ) {
            update_post_meta( $order_id, '_twentyi_website_domain', $this->sanitize_domain( wp_unslash( $_POST['twentyi_website_domain'] ) ) );
        }

        if ( isset( $_POST['twentyi_website_brief'] ) ) {
            update_post_meta( $order_id, '_twentyi_website_brief', sanitize_textarea_field( wp_unslash( $_POST['twentyi_website_brief'] ) ) );
        }
    }

    /**
     * Provision a linked 20i package after a paid WooCommerce order.
     *
     * @param int $order_id Order ID.
     */
    public function maybe_provision_woocommerce_order( $order_id ) {
        if ( ! function_exists( 'wc_get_order' ) ) {
            return;
        }

        $order_id = absint( $order_id );
        $order    = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        if ( get_post_meta( $order_id, '_twentyi_hosting_request_id', true ) ) {
            return;
        }

        if ( method_exists( $order, 'is_paid' ) && ! $order->is_paid() && ! in_array( $order->get_status(), [ 'processing', 'completed' ], true ) ) {
            return;
        }

        $linked_item = $this->get_woocommerce_order_hosting_item( $order );

        if ( empty( $linked_item ) ) {
            return;
        }

        $domain = $this->sanitize_domain( get_post_meta( $order_id, '_twentyi_website_domain', true ) );
        $site   = sanitize_text_field( get_post_meta( $order_id, '_twentyi_website_name', true ) );
        $brief  = sanitize_textarea_field( get_post_meta( $order_id, '_twentyi_website_brief', true ) );

        if ( '' === $domain || '' === $site ) {
            $message = __( '20i provisioning skipped: the order is missing website setup details.', 'twentyi-hosting-browser' );
            $this->add_order_note( $order, $message );
            $this->add_activity_log( 'woocommerce_provision_missing_details', $message, [ 'order_id' => $order_id ], 'error' );
            return;
        }

        $product_id   = absint( $linked_item['product_id'] );
        $package_type = sanitize_text_field( (string) $linked_item['package_type'] );
        $label        = sanitize_text_field( (string) $linked_item['package_label'] );
        $client_email = sanitize_email( $order->get_billing_email() );
        $client_name  = trim( sanitize_text_field( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) );
        $phone        = sanitize_text_field( $order->get_billing_phone() );

        if ( ! is_email( $client_email ) ) {
            $message = __( '20i provisioning skipped: the order does not have a valid billing email address.', 'twentyi-hosting-browser' );
            $this->add_order_note( $order, $message );
            $this->add_activity_log( 'woocommerce_provision_missing_email', $message, [ 'order_id' => $order_id, 'domain' => $domain ], 'error' );
            return;
        }

        $request_id = $this->create_woocommerce_website_request(
            [
                'order_id'      => $order_id,
                'product_id'    => $product_id,
                'business_name' => $site,
                'contact_name'  => $client_name ?: $client_email,
                'domain_name'   => $domain,
                'email'         => $client_email,
                'phone'         => $phone,
                'website_brief' => $brief,
                'package_type'  => $package_type,
            ]
        );

        if ( is_wp_error( $request_id ) ) {
            $this->add_order_note( $order, $request_id->get_error_message() );
            $this->add_activity_log( 'woocommerce_request_create_failed', $request_id->get_error_message(), [ 'order_id' => $order_id, 'domain' => $domain ], 'error' );
            return;
        }

        update_post_meta( $order_id, '_twentyi_hosting_request_id', (int) $request_id );

        $result = $this->create_hosting_package(
            [
                'domain_name' => $domain,
                'type'        => $package_type,
                'label'       => $label ?: $site,
            ]
        );

        if ( is_wp_error( $result ) ) {
            update_post_meta( $request_id, 'status', 'needs_review' );
            update_post_meta( $request_id, 'last_error', $result->get_error_message() );
            update_post_meta( $order_id, '_twentyi_hosting_status', 'failed' );
            $this->add_order_note( $order, sprintf( __( '20i hosting package creation failed: %s', 'twentyi-hosting-browser' ), $result->get_error_message() ) );
            $this->add_activity_log( 'woocommerce_package_create_failed', $result->get_error_message(), [ 'order_id' => $order_id, 'request_id' => (int) $request_id, 'domain' => $domain, 'package_type' => $package_type ], 'error' );
            return;
        }

        update_post_meta( $request_id, 'status', 'created' );
        update_post_meta( $request_id, 'package_id', $result['package_id'] );
        update_post_meta( $request_id, 'created_result', $result['result'] );
        update_post_meta( $order_id, '_twentyi_hosting_status', 'created' );
        update_post_meta( $order_id, '_twentyi_hosting_package_id', $result['package_id'] );
        delete_post_meta( $request_id, 'last_error' );

        $this->sync_woocommerce_order_billing_status( $order_id, 'provisioned' );

        $this->schedule_wordpress_admin_bootstrap( (int) $request_id, (string) $result['package_id'], $domain, $client_email, $client_name, $site );

        $this->add_order_note( $order, sprintf( __( '20i hosting package created for %1$s. Package ID: %2$s', 'twentyi-hosting-browser' ), $domain, (string) $result['package_id'] ) );
        $this->add_activity_log(
            'woocommerce_package_created',
            sprintf( __( 'WooCommerce order #%1$d created a 20i hosting package for %2$s.', 'twentyi-hosting-browser' ), $order_id, $domain ),
            [
                'order_id'      => $order_id,
                'request_id'    => (int) $request_id,
                'product_id'    => $product_id,
                'domain'        => $domain,
                'package_type'  => $package_type,
                'package_id'    => $result['package_id'],
            ],
            'success'
        );
    }

    /**
     * Register WooCommerce order admin metabox.
     */
    public function register_woocommerce_order_metabox() {
        if ( ! function_exists( 'wc_get_order' ) ) {
            return;
        }

        add_meta_box(
            'twentyi_woocommerce_hosting',
            __( '20i Hosting', 'twentyi-hosting-browser' ),
            [ $this, 'render_woocommerce_order_metabox' ],
            'shop_order',
            'side',
            'default'
        );

        add_meta_box(
            'twentyi_woocommerce_hosting',
            __( '20i Hosting', 'twentyi-hosting-browser' ),
            [ $this, 'render_woocommerce_order_metabox' ],
            'woocommerce_page_wc-orders',
            'side',
            'default'
        );
    }

    /**
     * Render WooCommerce order admin metabox.
     *
     * @param mixed $post_or_order_object Post or WC_Order.
     */
    public function render_woocommerce_order_metabox( $post_or_order_object ) {
        if ( ! function_exists( 'wc_get_order' ) ) {
            esc_html_e( 'WooCommerce is not available.', 'twentyi-hosting-browser' );
            return;
        }

        $order = is_object( $post_or_order_object ) && method_exists( $post_or_order_object, 'get_id' ) ? $post_or_order_object : wc_get_order( is_object( $post_or_order_object ) && isset( $post_or_order_object->ID ) ? $post_or_order_object->ID : 0 );

        if ( ! $order ) {
            esc_html_e( 'Order not found.', 'twentyi-hosting-browser' );
            return;
        }

        $order_id    = $order->get_id();
        $request_id  = absint( get_post_meta( $order_id, '_twentyi_hosting_request_id', true ) );
        $status      = sanitize_key( (string) get_post_meta( $order_id, '_twentyi_hosting_status', true ) );
        $package_id  = sanitize_text_field( get_post_meta( $order_id, '_twentyi_hosting_package_id', true ) );
        $billing_status = sanitize_key( (string) get_post_meta( $order_id, '_twentyi_billing_status', true ) );
        $billing_source = sanitize_key( (string) get_post_meta( $order_id, '_twentyi_billing_source', true ) );
        $domain      = $this->sanitize_domain( get_post_meta( $order_id, '_twentyi_website_domain', true ) );
        $linked_item = $this->get_woocommerce_order_hosting_item( $order );

        if ( empty( $linked_item ) ) {
            echo '<p>' . esc_html__( 'No 20i hosting product is linked to this order.', 'twentyi-hosting-browser' ) . '</p>';
            return;
        }

        echo '<p><strong>' . esc_html__( 'Domain:', 'twentyi-hosting-browser' ) . '</strong><br />' . esc_html( $domain ?: __( 'Not added', 'twentyi-hosting-browser' ) ) . '</p>';
        echo '<p><strong>' . esc_html__( 'Package type:', 'twentyi-hosting-browser' ) . '</strong><br />' . esc_html( $linked_item['package_label_readable'] ) . '</p>';
        echo '<p><strong>' . esc_html__( 'Provisioning status:', 'twentyi-hosting-browser' ) . '</strong><br />' . esc_html( $status ? ucwords( str_replace( '_', ' ', $status ) ) : __( 'Not started', 'twentyi-hosting-browser' ) ) . '</p>';
        echo '<p><strong>' . esc_html__( 'Billing/subscription status:', 'twentyi-hosting-browser' ) . '</strong><br />' . esc_html( $billing_status ? $this->format_billing_status_label( $billing_status ) : __( 'Not synced yet', 'twentyi-hosting-browser' ) ) . ( $billing_source ? '<br /><small>' . esc_html( ucwords( str_replace( '_', ' ', $billing_source ) ) ) . '</small>' : '' ) . '</p>';

        if ( $package_id ) {
            echo '<p><strong>' . esc_html__( '20i Package ID:', 'twentyi-hosting-browser' ) . '</strong><br /><code>' . esc_html( $package_id ) . '</code></p>';
        }

        if ( $request_id ) {
            $request_url = admin_url( 'admin.php?page=' . self::REQUESTS_SLUG . '&s=' . rawurlencode( (string) $request_id ) );
            echo '<p><a class="button button-secondary" href="' . esc_url( $request_url ) . '">' . esc_html__( 'View Website Request', 'twentyi-hosting-browser' ) . '</a></p>';
        }

        $retry_url = wp_nonce_url(
            add_query_arg(
                [
                    'action'   => 'twentyi_hosting_browser_wc_order_action',
                    'wc_action'=> 'provision',
                    'order_id' => $order_id,
                ],
                admin_url( 'admin-post.php' )
            ),
            'twentyi_hosting_browser_wc_order_' . $order_id
        );

        echo '<p><a class="button" href="' . esc_url( $retry_url ) . '">' . esc_html__( 'Run / Retry Provisioning', 'twentyi-hosting-browser' ) . '</a></p>';

        $sync_url = wp_nonce_url(
            add_query_arg(
                [
                    'action'   => 'twentyi_hosting_browser_wc_order_action',
                    'wc_action'=> 'sync_billing',
                    'order_id' => $order_id,
                ],
                admin_url( 'admin-post.php' )
            ),
            'twentyi_hosting_browser_wc_order_' . $order_id
        );
        echo '<p><a class="button button-secondary" href="' . esc_url( $sync_url ) . '">' . esc_html__( 'Sync Billing Status', 'twentyi-hosting-browser' ) . '</a></p>';
    }

    /**
     * Handle admin order retry action.
     */
    public function handle_woocommerce_order_action() {
        if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to do that.', 'twentyi-hosting-browser' ) );
        }

        $order_id = absint( $_GET['order_id'] ?? 0 );
        $action   = sanitize_key( wp_unslash( $_GET['wc_action'] ?? '' ) );
        check_admin_referer( 'twentyi_hosting_browser_wc_order_' . $order_id );

        if ( 'provision' === $action && $order_id ) {
            delete_post_meta( $order_id, '_twentyi_hosting_request_id' );
            delete_post_meta( $order_id, '_twentyi_hosting_status' );
            delete_post_meta( $order_id, '_twentyi_hosting_package_id' );
            $this->maybe_provision_woocommerce_order( $order_id );
        }

        if ( 'sync_billing' === $action && $order_id ) {
            $this->sync_woocommerce_order_billing_status( $order_id, 'manual' );
        }

        wp_safe_redirect( wp_get_referer() ?: admin_url( 'post.php?post=' . $order_id . '&action=edit' ) );
        exit;
    }

    /**
     * Whether the cart contains a 20i hosting product.
     *
     * @return bool
     */
    protected function woocommerce_cart_contains_hosting_product() {
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            return false;
        }

        foreach ( WC()->cart->get_cart() as $cart_item ) {
            $product_id = $this->resolve_woocommerce_product_id_for_hosting( absint( $cart_item['product_id'] ?? 0 ), absint( $cart_item['variation_id'] ?? 0 ) );
            if ( $product_id && $this->is_woocommerce_product_hosting_enabled( $product_id ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find the first linked hosting product in an order.
     *
     * @param WC_Order $order Order.
     * @return array<string,mixed>
     */
    protected function get_woocommerce_order_hosting_item( $order ) {
        if ( ! $order || ! method_exists( $order, 'get_items' ) ) {
            return [];
        }

        foreach ( $order->get_items() as $item ) {
            $product_id   = absint( $item->get_product_id() );
            $variation_id = absint( method_exists( $item, 'get_variation_id' ) ? $item->get_variation_id() : 0 );
            $host_product = $this->resolve_woocommerce_product_id_for_hosting( $product_id, $variation_id );

            if ( ! $host_product || ! $this->is_woocommerce_product_hosting_enabled( $host_product ) ) {
                continue;
            }

            $package_type = sanitize_text_field( get_post_meta( $host_product, '_twentyi_wc_package_type', true ) );

            if ( '' === $package_type ) {
                continue;
            }

            $package_label = sanitize_text_field( get_post_meta( $host_product, '_twentyi_wc_package_label', true ) );

            return [
                'product_id'             => $host_product,
                'order_item_id'          => method_exists( $item, 'get_id' ) ? $item->get_id() : 0,
                'package_type'           => $package_type,
                'package_label'          => $package_label,
                'package_label_readable' => $this->get_package_type_label( $package_type ) . ' (' . $package_type . ')',
            ];
        }

        return [];
    }

    /**
     * Resolve a product/variation to the product that stores 20i package meta.
     *
     * @param int $product_id Product ID.
     * @param int $variation_id Variation ID.
     * @return int
     */
    protected function resolve_woocommerce_product_id_for_hosting( $product_id, $variation_id = 0 ) {
        if ( $variation_id && $this->is_woocommerce_product_hosting_enabled( $variation_id ) ) {
            return $variation_id;
        }

        if ( $product_id && $this->is_woocommerce_product_hosting_enabled( $product_id ) ) {
            return $product_id;
        }

        return 0;
    }

    /**
     * Whether a WooCommerce product is linked to a 20i package type.
     *
     * @param int $product_id Product ID.
     * @return bool
     */
    protected function is_woocommerce_product_hosting_enabled( $product_id ) {
        $product_id = absint( $product_id );

        if ( ! $product_id ) {
            return false;
        }

        return 'yes' === get_post_meta( $product_id, '_twentyi_wc_enable_provisioning', true ) && '' !== sanitize_text_field( get_post_meta( $product_id, '_twentyi_wc_package_type', true ) );
    }

    /**
     * Create a Website Request record from a WooCommerce order.
     *
     * @param array<string,mixed> $args Request data.
     * @return int|WP_Error
     */
    protected function create_woocommerce_website_request( $args ) {
        $business_name = sanitize_text_field( (string) ( $args['business_name'] ?? '' ) );
        $domain_name   = $this->sanitize_domain( (string) ( $args['domain_name'] ?? '' ) );

        $request_id = wp_insert_post(
            [
                'post_type'   => self::REQUEST_POST_TYPE,
                'post_status' => 'pending',
                'post_title'  => sprintf(
                    __( '%1$s - %2$s', 'twentyi-hosting-browser' ),
                    $business_name ?: __( 'WooCommerce Website', 'twentyi-hosting-browser' ),
                    $domain_name
                ),
            ],
            true
        );

        if ( is_wp_error( $request_id ) ) {
            return $request_id;
        }

        $request_id = (int) $request_id;
        $map        = [
            'business_name' => 'business_name',
            'contact_name'  => 'contact_name',
            'domain_name'   => 'domain_name',
            'email'         => 'email',
            'phone'         => 'phone',
            'website_brief' => 'website_brief',
            'package_type'  => 'package_type',
        ];

        foreach ( $map as $arg_key => $meta_key ) {
            update_post_meta( $request_id, $meta_key, sanitize_text_field( (string) ( $args[ $arg_key ] ?? '' ) ) );
        }

        update_post_meta( $request_id, 'status', 'woocommerce_paid' );
        update_post_meta( $request_id, 'submitted_at', current_time( 'mysql' ) );
        update_post_meta( $request_id, 'source', 'woocommerce' );
        update_post_meta( $request_id, 'woo_order_id', absint( $args['order_id'] ?? 0 ) );
        update_post_meta( $request_id, 'woo_product_id', absint( $args['product_id'] ?? 0 ) );

        return $request_id;
    }


    /**
     * Handle WooCommerce order status changes and mirror billing state.
     *
     * @param int    $order_id Order ID.
     * @param string $old_status Old status.
     * @param string $new_status New status.
     * @param mixed  $order Order object.
     */
    public function handle_woocommerce_order_status_changed( $order_id, $old_status = '', $new_status = '', $order = null ) {
        if ( ! $this->wc_billing_sync_enabled() ) {
            return;
        }

        $this->sync_woocommerce_order_billing_status( absint( $order_id ), 'order_status_change' );
    }

    /**
     * Handle WooCommerce Subscriptions status changes.
     *
     * @param mixed  $subscription Subscription object.
     * @param string $new_status New status.
     * @param string $old_status Old status.
     */
    public function handle_woocommerce_subscription_status_updated( $subscription, $new_status = '', $old_status = '' ) {
        if ( ! $this->wc_billing_sync_enabled() || ! is_object( $subscription ) ) {
            return;
        }

        $subscription_id = method_exists( $subscription, 'get_id' ) ? absint( $subscription->get_id() ) : 0;
        $parent_id       = method_exists( $subscription, 'get_parent_id' ) ? absint( $subscription->get_parent_id() ) : 0;

        if ( $parent_id ) {
            $this->sync_woocommerce_order_billing_status( $parent_id, 'subscription_status_change', $subscription );
        }

        // Some stores create/renew subscriptions without a direct parent order reference on renewals.
        $requests = get_posts(
            [
                'post_type'      => self::REQUEST_POST_TYPE,
                'post_status'    => [ 'pending', 'publish', 'draft', 'private' ],
                'posts_per_page' => 20,
                'meta_key'       => 'woo_subscription_id',
                'meta_value'     => $subscription_id,
                'fields'         => 'ids',
            ]
        );

        foreach ( $requests as $request_id ) {
            $this->update_request_billing_status( (int) $request_id, $this->normalize_billing_status( $new_status ), 'subscription', $subscription_id );
        }
    }

    /**
     * Sync billing status for a WooCommerce order and linked website request.
     *
     * @param int         $order_id Order ID.
     * @param string      $context Context label.
     * @param object|null $known_subscription Optional subscription object.
     * @return bool
     */
    protected function sync_woocommerce_order_billing_status( $order_id, $context = 'sync', $known_subscription = null ) {
        if ( ! $this->wc_billing_sync_enabled() || ! function_exists( 'wc_get_order' ) ) {
            return false;
        }

        $order_id = absint( $order_id );
        $order    = wc_get_order( $order_id );

        if ( ! $order ) {
            return false;
        }

        $request_id = absint( get_post_meta( $order_id, '_twentyi_hosting_request_id', true ) );

        if ( ! $request_id || self::REQUEST_POST_TYPE !== get_post_type( $request_id ) ) {
            return false;
        }

        $source          = 'order';
        $source_id       = $order_id;
        $raw_status      = method_exists( $order, 'get_status' ) ? (string) $order->get_status() : '';
        $subscription    = $known_subscription ?: $this->get_subscription_for_order( $order_id );

        if ( $subscription && is_object( $subscription ) ) {
            $source    = 'subscription';
            $source_id = method_exists( $subscription, 'get_id' ) ? absint( $subscription->get_id() ) : 0;
            if ( method_exists( $subscription, 'get_status' ) ) {
                $raw_status = (string) $subscription->get_status();
            }
            if ( $source_id ) {
                update_post_meta( $request_id, 'woo_subscription_id', $source_id );
                update_post_meta( $order_id, '_twentyi_subscription_id', $source_id );
            }
        }

        $status = $this->normalize_billing_status( $raw_status );

        update_post_meta( $order_id, '_twentyi_billing_status', $status );
        update_post_meta( $order_id, '_twentyi_billing_source', $source );
        update_post_meta( $order_id, '_twentyi_billing_source_id', $source_id );
        update_post_meta( $order_id, '_twentyi_billing_updated_at', current_time( 'mysql' ) );

        $this->update_request_billing_status( $request_id, $status, $source, $source_id );

        $note = sprintf(
            /* translators: 1: billing status, 2: source */
            __( '20i billing status synced: %1$s via %2$s.', 'twentyi-hosting-browser' ),
            $this->format_billing_status_label( $status ),
            $source
        );
        $this->add_order_note( $order, $note );

        $this->add_activity_log(
            'woocommerce_billing_sync',
            sprintf( __( 'WooCommerce billing status synced for order #%1$d: %2$s.', 'twentyi-hosting-browser' ), $order_id, $status ),
            [
                'order_id'   => $order_id,
                'request_id' => $request_id,
                'source'     => $source,
                'source_id'  => $source_id,
                'status'     => $status,
                'context'    => $context,
            ],
            $this->is_unhealthy_billing_status( $status ) ? 'error' : 'success'
        );

        return true;
    }

    /**
     * Update a Website Request with billing status metadata.
     *
     * @param int    $request_id Request ID.
     * @param string $status Billing status.
     * @param string $source Source type.
     * @param int    $source_id Source object ID.
     */
    protected function update_request_billing_status( $request_id, $status, $source = 'order', $source_id = 0 ) {
        $request_id = absint( $request_id );
        $status     = $this->normalize_billing_status( $status );
        $source     = sanitize_key( (string) $source );
        $source_id  = absint( $source_id );

        if ( ! $request_id || self::REQUEST_POST_TYPE !== get_post_type( $request_id ) ) {
            return;
        }

        update_post_meta( $request_id, 'wc_billing_status', $status );
        update_post_meta( $request_id, 'wc_billing_source', $source );
        update_post_meta( $request_id, 'wc_billing_source_id', $source_id );
        update_post_meta( $request_id, 'wc_billing_updated_at', current_time( 'mysql' ) );

        if ( $this->is_unhealthy_billing_status( $status ) ) {
            update_post_meta( $request_id, 'wc_billing_needs_attention', 1 );
            update_post_meta( $request_id, 'wc_billing_note', __( 'Your subscription or payment status needs attention. Some self-service actions may be paused.', 'twentyi-hosting-browser' ) );
        } else {
            delete_post_meta( $request_id, 'wc_billing_needs_attention' );
            delete_post_meta( $request_id, 'wc_billing_note' );
        }
    }

    /**
     * Get the first subscription linked to an order, if WooCommerce Subscriptions is available.
     *
     * @param int $order_id Order ID.
     * @return object|null
     */
    protected function get_subscription_for_order( $order_id ) {
        $order_id = absint( $order_id );

        if ( ! $order_id || ! function_exists( 'wcs_get_subscriptions_for_order' ) ) {
            return null;
        }

        $subscriptions = wcs_get_subscriptions_for_order( $order_id, [ 'order_type' => 'any' ] );

        if ( empty( $subscriptions ) && function_exists( 'wcs_get_subscriptions_for_renewal_order' ) ) {
            $subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );
        }

        if ( empty( $subscriptions ) || ! is_array( $subscriptions ) ) {
            return null;
        }

        foreach ( $subscriptions as $subscription ) {
            if ( is_object( $subscription ) ) {
                return $subscription;
            }
        }

        return null;
    }

    /**
     * Normalize billing/order/subscription status values.
     *
     * @param string $status Raw status.
     * @return string
     */
    protected function normalize_billing_status( $status ) {
        $status = sanitize_key( (string) $status );

        if ( '' === $status ) {
            return 'unknown';
        }

        $map = [
            'wc-active'         => 'active',
            'wc-processing'     => 'processing',
            'wc-completed'      => 'completed',
            'wc-on-hold'        => 'on-hold',
            'wc-cancelled'      => 'cancelled',
            'wc-canceled'       => 'cancelled',
            'wc-expired'        => 'expired',
            'wc-pending-cancel' => 'pending-cancel',
            'wc-failed'         => 'failed',
            'wc-pending'        => 'pending',
        ];

        return $map[ $status ] ?? $status;
    }

    /**
     * Whether a billing/subscription status should pause client self-service.
     *
     * @param string $status Billing status.
     * @return bool
     */
    protected function is_unhealthy_billing_status( $status ) {
        $status = $this->normalize_billing_status( $status );

        return in_array( $status, [ 'on-hold', 'cancelled', 'canceled', 'expired', 'pending-cancel', 'failed', 'pending', 'refunded', 'trash' ], true );
    }

    /**
     * Whether a Website Request currently has an unhealthy Woo billing status.
     *
     * @param int $request_id Request ID.
     * @return bool
     */
    protected function request_has_unhealthy_billing( $request_id ) {
        $request_id = absint( $request_id );
        if ( ! $request_id ) {
            return false;
        }

        $status = sanitize_key( (string) get_post_meta( $request_id, 'wc_billing_status', true ) );

        return '' !== $status && $this->is_unhealthy_billing_status( $status );
    }

    /**
     * Format a billing/subscription status label.
     *
     * @param string $status Status.
     * @return string
     */
    protected function format_billing_status_label( $status ) {
        $status = $this->normalize_billing_status( $status );

        if ( 'unknown' === $status ) {
            return __( 'Unknown', 'twentyi-hosting-browser' );
        }

        return ucwords( str_replace( '-', ' ', $status ) );
    }

    /**
     * Whether WooCommerce billing sync is enabled.
     *
     * @return bool
     */
    protected function wc_billing_sync_enabled() {
        return (bool) get_option( self::OPTION_WC_BILLING_SYNC_ENABLED, 1 );
    }

    /**
     * Whether client dashboard actions should be blocked when billing is unhealthy.
     *
     * @return bool
     */
    protected function wc_billing_blocks_client_actions() {
        return (bool) get_option( self::OPTION_WC_BILLING_BLOCK_ACTIONS, 1 );
    }

    /**
     * Add an order note safely.
     *
     * @param WC_Order $order Order object.
     * @param string   $message Message.
     */
    protected function add_order_note( $order, $message ) {
        if ( $order && method_exists( $order, 'add_order_note' ) ) {
            $order->add_order_note( wp_strip_all_tags( (string) $message ) );
        }
    }

    /**
     * Redirect back to the create page with an error message.
     *
     * @param string $message Error message.
     */
    protected function redirect_create_page_with_message( $message ) {
        wp_safe_redirect(
            add_query_arg(
                [
                    'page'    => self::CREATE_SLUG,
                    'message' => $message,
                ],
                admin_url( 'admin.php' )
            )
        );
        exit;
    }
}
