# WooCommerce Visual Order Status

[![Donate using PayPal](https://img.shields.io/badge/Donate-PayPal-8886F9)](https://www.paypal.com/donate?hosted_button_id=7GM64HDD7FH3G) [![Donate using Pix](https://img.shields.io/badge/Donate-Pix-8886F9)](https://nubank.com.br/pagar/4rs96/dk2MZHOjFN)

This plugins allows you to show order statuses in a visual, icon-based way:

![Screenshot](example.gif?raw=true)

It is built with customization and extension in mind, so you're free to add your own custom statuses and even custom handlers (such as shipping/tracking integrations).

## Installation

As this is a still a development/prototype version, the plugin itself hasn't been published on WordPress's main repositories yet.

To install it, simply download the repository as a `.zip` file or run `git clone` directly to your `/wp-content/plugins/` directory, then enable it on your WordPress administration section. If you opt for the Git option, getting updated versions of the plugin is pretty much straightforward, as you only will need to `git pull` once in a while to have the latest and greatest!

## Extending the Plugin

As mentioned above, one of the main things about this plugin is being as flexible as possible for theme and plugin developers. To achieve that, the plugin makes extensive use of Filters and also allows for quick and easy replacement of resources, from icons to entire templates.

### Custom Resources: Icons, Styles and Templates

The first level of customization available is specially tailored for theme developers. Replacing a resource file is as simple as creating its corresponding version in your theme's directory.

For example, if you want to replace the Shipping status icon, you will find it at the path `res/icons/shipping.svg` inside the plugin, this is the same path that the counterpart of your theme is expected to be: `/wp-content/themes/your-cool-theme/res/icons/shipping.svg`

### Custom Statuses: The Basics

Other than the default WooCommerce statuses, you can also add your own, custom, statuses! Doing it is very simple and all you need to do is add a filter to the `wc_visual_status_groups` tag and append your status. You can also replace all statuses with your own custom versions!

This is an example of what can be achieved:

```php
add_filter( 'wc_visual_status_groups', 'my_plugin_add_status' );
function my_plugin_add_status( $groups )
{
    $groups[ 'my-custom-status' ] = array(
        // The status name that will show on the UI
        'title'     => 'Custom Status',

        // The internal WooCommerce status codes
        'includes'  => array( 'custom-status-1', 'custom-status-2' ),

        // The progress amount this should be considered on the progress bar
        'progress'  => 0.50,

        // The custom icon we'll be using
        'icon'      => get_template_directory_uri() . '/images/custom-status.svg',
    );

    // Done!
    return $groups;
}
```

With the code above you will have a new custom icon on the progress bar, with your own title and custom statuses codes. You can also use standard WooCommerce status codes too.

### Custom Statuses: Extended Functionality

In some cases you might want to integrate with an external API to fetch useful information about the order, such as which stage of shipping it is, etc. For these cases the plugin also supports passing a `Closure` or `callable` to the `includes` option.

This function will be executed whenever a status check is in place and will receive an associative array containing the `order` and `post` objects, so you don't have to do this check manually.

For example, let's say we want to extend the built-in `shipping` status, which by default is never reached:

```php
add_filter( 'wc_visual_status_groups', 'my_plugin_extend_shipping' );
function my_plugin_extend_shipping()
{
    // Updates the "shipping" step, "includes" option, and tells it to use the "my_plugin_handle_shipping_test" function to test whether it's this step or not
    $groups[ 'shipping' ][ 'includes' ] = 'my_plugin_handle_shipping_test';

    // Done!
    return $groups;
}

function my_plugin_handle_shipping_test( $info )
{
    // Grabs the WooCommerce Order object
    $order = $info[ 'order' ];

    // Now, let's say we have a post meta in our Order that has the tracking ID
    $trackingId = get_post_meta( $order->id, 'my_plugin_tracking_id' );

    // Checks if the ID is valid
    if ( ! empty( $trackingId ) ) {
        // Now we call some external API or do something with it here
        // I'll just use a random class that would return a generic response with the following structure:
        // { collected: bool, last_location: string }
        $trackingResult = MyPluginTracking::track( $trackingId );

        // Check if the response's "collected" is true
        // Otherwise the code might have been generated but the item was not collected by the shipping company yet
        if ( $tracingResult->collected ) {
            // Returning true on this function will tell the UI that we're on the shipping step
            return true;
        }
    }

    // If all previous conditions fail, return false to tell the UI not to consider this step
    return false;
}
```

### Custom Placement

If you want to change where the status is displayed, simply add a filter to the `wc_visual_status_hook` tag and return a string containing the hook name that should be called for rendering. The plugin will take care of the rest.

## License

This project is licensed under the very permissive [BSD 3-Clause License](LICENSE), which basically allows you to use the plugin free of charge, both for personal and commercial work. If you feel like contributing to the development of this plugin, please consider donating through the buttons presented at the top of this file. Your support is very welcome!

## Special Thanks

Thanks for the Free Preloaders team at freeicons.io for their awesome [Shipping and Delivery](https://freeicons.io/icon-list/shipping-and-delivery-icons) icon pack that is shipped as the default icon scheme!