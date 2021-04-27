<?php

namespace Subatomic\WordPress\WooCommerceVisualStatus;

class Core
{
    private static $instance = null;

    public static function boot( $resourcesDir, $resourcesUrl, $resourcesPath )
    {
        // Prevents the initialization from running twice
        if ( ! is_null( static::$instance ) ) {
            return;
        }
        static::$instance = new static( $resourcesDir, $resourcesUrl, $resourcesPath );
    }

    public static function preventDirectAccess()
    {
        if ( ! defined( 'ABSPATH' ) ) {
            http_response_code( 403 );
            exit;
        }
    }
    
    private $resourcesDir   = '';
    private $resourcesUrl   = '';
    private $resourcesPath  = '';

    private function __construct( $resourcesDir, $resourcesUrl, $resourcesPath )
    {
        // Sets the basepath for the icons, etc
        $this->resourcesDir = $resourcesDir;
        $this->resourcesUrl = $resourcesUrl . '/' . $resourcesDir . '/';
        $this->resourcesPath = $resourcesPath . '/' . $resourcesDir . '/';

        // Set-up all hooks here.
        \add_action( 'init', array( $this, 'handleInit' ) );
        \wp_enqueue_style( 'wc_visual_status', $this->getResourceUrlFromThemeOrPlugin( 'styles/wc-visual-status.css' ) );

        // Default filter values
        \add_filter( 'wc_visual_status_groups', array( $this, 'getDefaultStatuses' ), PHP_INT_MIN );
    }

    public function handleInit()
    {
        // Queues the rendering action, it can optionally be changed by the "wc_visual_status_hook" filter
        \add_action( apply_filters( 'wc_visual_status_hook', 'woocommerce_order_details_before_order_table' ), array( $this, 'renderVisualStatusTemplate' ) );
    }

    public function getDefaultStatuses( $groups )
    {
        return array(
            'pending_payment' => array(
                'title'     => 'Payment',
                'includes'  => array( 'pending', 'on-hold' ),
                'icon'      => $this->getResourceUrlFromThemeOrPlugin( 'icons/pending-payment.svg' ),
                'progress'  => 0.00,
            ),
            'processing' => array(
                'title'     => 'Processing',
                'includes'  => array( 'processing' ),
                'icon'      => $this->getResourceUrlFromThemeOrPlugin( 'icons/processing.svg' ),
                'progress'  => 0.35,
            ),
            'shipping' => array(
                'title'     => 'Shipping',
                'includes'  => array(),
                'icon'      => $this->getResourceUrlFromThemeOrPlugin( 'icons/shipping.svg' ),
                'progress'  => 0.65,
            ),
            'completed' => array(
                'title'     => 'Completed',
                'includes'  => array( 'completed' ),
                'icon'      => $this->getResourceUrlFromThemeOrPlugin( 'icons/completed.svg' ),
                'progress'  => 1.00,
                'fixed'     => true,
            ),
            'cancelled' => array(
                'title'     => 'Cancelled',
                'includes'  => array( 'cancelled', 'failed' ),
                'icon'      => $this->getResourceUrlFromThemeOrPlugin( 'icons/cancelled.svg' ),
                'progress'  => 1.00,
            ),
            'refunded' => array(
                'title'     => 'Refunded',
                'includes'  => array( 'refunded' ),
                'icon'      => $this->getResourceUrlFromThemeOrPlugin( 'icons/refunded.svg' ),
                'progress'  => 1.00,
            ),
        );
    }

    public function getResourceUrlFromThemeOrPlugin( $file )
    {
        // The default resource directory name
        $resourceDirectory = $this->resourcesDir;

        // Properly escapes paths
        $file = str_replace( '\\', '/', $file );
        $filePath = str_replace( '/', DIRECTORY_SEPARATOR, $file );

        // Does the theme has a dedicated resource directory AND the requested resource exists?
        if (
            is_dir( \get_template_directory() . DIRECTORY_SEPARATOR . $resourceDirectory ) &&
            file_exists( \get_template_directory() . DIRECTORY_SEPARATOR . $resourceDirectory . DIRECTORY_SEPARATOR . $filePath )
        ) {
            // Returns the found file from theme
            return \get_template_directory_uri() . '/' . $resourceDirectory . '/' . $file;
        }

        // Otherwise, returns the resource from the plugin files
        return $this->resourcesUrl . $file;
    }

    public function getResourcePathFromThemeOrPlugin( $file )
    {
        // The default resource directory name
        $resourceDirectory = $this->resourcesDir;

        // Properly escapes paths
        $file = str_replace( '\\', '/', $file );
        $filePath = str_replace( '/', DIRECTORY_SEPARATOR, $file );

        // Does the theme has a dedicated resource directory AND the requested resource exists?
        if (
            is_dir( \get_template_directory() . DIRECTORY_SEPARATOR . $resourceDirectory ) &&
            file_exists( \get_template_directory() . DIRECTORY_SEPARATOR . $resourceDirectory . DIRECTORY_SEPARATOR . $filePath )
        ) {
            // Returns the found file from theme
            return \get_template_directory() . DIRECTORY_SEPARATOR . $resourceDirectory . DIRECTORY_SEPARATOR . $file;
        }

        // Otherwise, returns the resource from the plugin files
        return $this->resourcesPath . $file;
    }

    public function getStatusGroups()
    {
        // Gets the list of status groups
        $groups = \apply_filters( 'wc_visual_status_groups', array() );

        // Updates them to map the key into the 'id' field
        foreach ( $groups as $id => $group ) {
            $groups[ $id ][ 'id' ] = $id;
        }

        // Returns the modified status groups
        return $groups;
    }

    public function renderVisualStatusTemplate( $order = null )
    {
        // Gets details of the order, etc
        $statusGroups = $this->getStatusGroups();
        $status = $this->getOrderStatusGroup( $order );

        // Renders the template file isolated
        $template = $this->getResourcePathFromThemeOrPlugin( 'partials/wc-visual-status.php' );
        (function ( $template, $status, $statusGroups ) {
            include $template;
        })( $template, $status, $statusGroups );
    }

    private function getOrderStatusGroup( $order = null )
    {
        // Grabs the current order if none is provided
        if ( is_null( $order ) ) {
            global $post;
            $order = new \WC_Order( $post->ID );
        }

        // Gets the WC current status
        $wcCurrentStatus = $order->get_status();

        // Gets the order status groupings
        $statusGroups = $this->getStatusGroups();

        // Finds the proper status
        $currentStatus = null;
        foreach ( $statusGroups as $id => $group ) {
            // Checks for inclusion test
            if ( isset ( $group[ 'includes' ] ) ) {
                // If we're a string, convert into array for next step!
                if ( is_string( $group[ 'includes' ] ) )
                    $group[ 'includes' ] = array( $group[ 'incldues' ] );
                    
                // Tests if we're checking a list of status codes via array
                if (
                    is_array( $group[ 'includes' ] ) &&
                    in_array( $wcCurrentStatus, $group[ 'includes' ] )
                ) {
                    $currentStatus = $group;
                }

                // Last test, checks if we've been passed a Closure, which should determine whether this is the right status or not
                if (
                    is_callable( $group[ 'includes' ] ) &&
                    call_user_func( $group[ 'includes' ], array(
                        'order' => $order,
                        'post'  => $post,
                    ) )
                ) {
                    $currentStatus = $group;
                }
            }
        }

        // Returns the final result
        return $currentStatus;
    }
}

// Prevents direct access to the file, though it would cause no harm anyways...
Core::preventDirectAccess();