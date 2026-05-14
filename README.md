# solid-host-plug
A hosting plugin for WordPress 
=== 20i Hosting Browser ===
Version 2.9.0 adds WooCommerce product-to-20i package provisioning.

Link a WooCommerce product to a 20i package type from the product edit screen. When the product is purchased and paid, the plugin collects website setup details at checkout, creates a Website Request record, provisions the 20i hosting package, queues Blueprint admin bootstrap, and continues the existing welcome email/client dashboard flow.


= 2.9.0 =
* Added WooCommerce order/subscription billing status sync for linked 20i Website Requests.
* Added billing status display in client dashboard and WooCommerce order metabox.
* Added safe client self-service pause for mailbox creation when payment/subscription status needs attention.
* Added manual Sync Billing Status action in WooCommerce orders.
