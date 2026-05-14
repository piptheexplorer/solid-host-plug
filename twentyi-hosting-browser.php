<?php
/**
 * Plugin Name: 20i Hosting Browser
 * Plugin URI: https://zapstartdigital.co.uk/
 * Description: Connect WordPress to the 20i API, save your API key, view websites hosted in your 20i account, create new 20i hosting packages from the WordPress admin, view a single package dashboard, run package maintenance actions, manage StackCP users with client handoff tools, handle package mailboxes and forwarders, plus domain search, DNS onboarding helpers, StackCP user creation tooling, a local activity/audit log for admin actions, and a frontend website request/onboarding form for clients, plus admin-only delete site/package controls and client-selectable frontend package options, Blueprint clone admin-user bootstrapping, and StackStaging temporary URL bootstrap support, and automated client welcome emails after WordPress admin creation, plus a frontend client website dashboard and optional temporary WordPress password delivery in the client welcome email, client dashboard mailbox creation, and WooCommerce product-to-20i package provisioning, plus WooCommerce Subscriptions billing status sync and client self-service pausing.
 * Version: 2.9.0
 * Author: PYork
 * License: GPL-2.0-or-later
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: twentyi-hosting-browser
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'TWENTYI_HOSTING_BROWSER_FILE' ) ) {
    define( 'TWENTYI_HOSTING_BROWSER_FILE', __FILE__ );
}

if ( ! defined( 'TWENTYI_HOSTING_BROWSER_PATH' ) ) {
    define( 'TWENTYI_HOSTING_BROWSER_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'TWENTYI_HOSTING_BROWSER_URL' ) ) {
    define( 'TWENTYI_HOSTING_BROWSER_URL', plugin_dir_url( __FILE__ ) );
}

require_once TWENTYI_HOSTING_BROWSER_PATH . 'includes/class-twentyi-hosting-browser.php';

TwentyI_Hosting_Browser::instance();
