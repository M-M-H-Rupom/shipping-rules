<?php
add_action('plugins_loaded',function(){
    class WC_Custom_shipping_rate extends WC_Shipping_Method {

        /**
         * Cost passed to [fee] shortcode.
         *
         * @var string Cost.
         */
        protected $fee_cost = '';

        /**
         * Shipping method cost.
         *
         * @var string
         */
        public $cost;

        /**
         * Shipping method type.
         *
         * @var string
         */
        public $type;

        /**
         * Constructor.
         *
         * @param int $instance_id Shipping method instance ID.
         */
        public function __construct( $instance_id = 0 ) {
            $this->id                 = 'custom_shipping_rate';
            $this->instance_id        = absint( $instance_id );
            $this->method_title       = __( 'Custom shipping rate', 'woocommerce' );
            $this->method_description = __( 'Lets you charge a fixed rate for shipping.', 'woocommerce' );
            $this->supports           = array(
                'shipping-zones',
                'instance-settings',
                'instance-settings-modal',
            );
            $this->init();
            add_filter('woocommerce_shipping_method_add_rate', array($this,'update_cost'), 10, 3);
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }
        public function update_cost( $rate, $args, $the_rate ){
                // if( $rate->get_method_id() == $this->id ) {
                $rate->set_cost( 13 );
                // }
                // return $rate;
                $instance_id = $rate->get_instance_id();
                $shipping_method = WC_Shipping_Zones::get_shipping_method( $instance_id );
                $data = $shipping_method->get_post_data();
                $data['data']['woocommerce_custom_shipping_rate_cost'] = 13;
                $shipping_method->set_post_data( $data );
                return $rate;
                // return array(
                //     'id'      => $this->get_rate_id(),
                //     'label'   => $this->title,
                //     'cost'    => 22,
                //     'taxes'   => false
                //     // 'package' => $package,
                // );
        }
        /**
         * Init user set variables.
         */
        public function init() {
            $this->instance_form_fields = include __DIR__ . '/templates/shipping_setting.php';
            $this->title                = $this->get_option( 'title' );
            $this->tax_status           = $this->get_option( 'tax_status' );
            $this->cost                 = $this->get_option( 'cost' );
            $this->type                 = $this->get_option( 'type', 'class' );
        }

        /**
         * Evaluate a cost from a sum/string.
         *
         * @param  string $sum Sum of shipping.
         * @param  array  $args Args, must contain `cost` and `qty` keys. Having `array()` as default is for back compat reasons.
         * @return string
         */
        protected function evaluate_cost( $sum, $args = array() ) {
            // Add warning for subclasses.
            if ( ! is_array( $args ) || ! array_key_exists( 'qty', $args ) || ! array_key_exists( 'cost', $args ) ) {
                wc_doing_it_wrong( __FUNCTION__, '$args must contain `cost` and `qty` keys.', '4.0.1' );
            }

            include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';

            // Allow 3rd parties to process shipping cost arguments.
            $args           = apply_filters( 'woocommerce_evaluate_shipping_cost_args', $args, $sum, $this );
            $locale         = localeconv();
            $decimals       = array( wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'], ',' );
            $this->fee_cost = $args['cost'];

            // Expand shortcodes.
            add_shortcode( 'fee', array( $this, 'fee' ) );

            $sum = do_shortcode(
                str_replace(
                    array(
                        '[qty]',
                        '[cost]',
                    ),
                    array(
                        $args['qty'],
                        $args['cost'],
                    ),
                    $sum
                )
            );

            remove_shortcode( 'fee', array( $this, 'fee' ) );

            // Remove whitespace from string.
            $sum = preg_replace( '/\s+/', '', $sum );

            // Remove locale from string.
            $sum = str_replace( $decimals, '.', $sum );

            // Trim invalid start/end characters.
            $sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

            // Do the math.
            return $sum ? WC_Eval_Math::evaluate( $sum ) : 0;
        }

        /**
         * Work out fee (shortcode).
         *
         * @param  array $atts Attributes.
         * @return string
         */
        public function fee( $atts ) {
            $atts = shortcode_atts(
                array(
                    'percent' => '',
                    'min_fee' => '',
                    'max_fee' => '',
                ),
                $atts,
                'fee'
            );

            $calculated_fee = 0;

            if ( $atts['percent'] ) {
                $calculated_fee = $this->fee_cost * ( floatval( $atts['percent'] ) / 100 );
            }

            if ( $atts['min_fee'] && $calculated_fee < $atts['min_fee'] ) {
                $calculated_fee = $atts['min_fee'];
            }

            if ( $atts['max_fee'] && $calculated_fee > $atts['max_fee'] ) {
                $calculated_fee = $atts['max_fee'];
            }
            return 10;
            return $calculated_fee;
        }

        /**
         * Calculate the shipping costs.
         *
         * @param array $package Package of items from cart.
         */
        public function calculate_shipping( $package = array() ) {
            $this->add_rate(
                array(
                    'label'   => $this->title,
                    'cost'    => 21,
                    'taxes'   => false,
                    'package' => $package,
                )
            );
        }

        /**
         * Get items in package.
         *
         * @param  array $package Package of items from cart.
         * @return int
         */
        public function get_package_item_qty( $package ) {
            $total_quantity = 0;
            foreach ( $package['contents'] as $item_id => $values ) {
                if ( $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
                    $total_quantity += $values['quantity'];
                }
            }
            return $total_quantity;
        }

        /**
         * Finds and returns shipping classes and the products with said class.
         *
         * @param mixed $package Package of items from cart.
         * @return array
         */
        public function find_shipping_classes( $package ) {
            $found_shipping_classes = array();

            foreach ( $package['contents'] as $item_id => $values ) {
                if ( $values['data']->needs_shipping() ) {
                    $found_class = $values['data']->get_shipping_class();

                    if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
                        $found_shipping_classes[ $found_class ] = array();
                    }

                    $found_shipping_classes[ $found_class ][ $item_id ] = $values;
                }
            }

            return $found_shipping_classes;
        }

        /**
         * Sanitize the cost field.
         *
         * @since 3.4.0
         * @param string $value Unsanitized value.
         * @throws Exception Last error triggered.
         * @return string
         */
        public function sanitize_cost( $value ) {
            $value = is_null( $value ) ? '' : $value;
            $value = wp_kses_post( trim( wp_unslash( $value ) ) );
            $value = str_replace( array( get_woocommerce_currency_symbol(), html_entity_decode( get_woocommerce_currency_symbol() ) ), '', $value );
            // Thrown an error on the front end if the evaluate_cost will fail.
            $dummy_cost = $this->evaluate_cost(
                $value,
                array(
                    'cost' => 1,
                    'qty'  => 1,
                )
            );
            if ( false === $dummy_cost ) {
                throw new Exception( WC_Eval_Math::$last_error );
            }
            return $value;
        }
    }

    add_filter('woocommerce_shipping_methods','shp_shipping_methods');
    function shp_shipping_methods( $methods ) {
        $methods['custom_shipping_rate'] = 'WC_Custom_shipping_rate';
        return $methods;
    }
});