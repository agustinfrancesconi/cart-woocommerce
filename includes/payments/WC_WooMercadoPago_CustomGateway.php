<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 *
 * WC_WooMercadoPago_CustomGateway
 *
 */
class WC_WooMercadoPago_CustomGateway extends WC_WooMercadoPago_PaymentAbstract
{
    const ID = 'woo-mercado-pago-custom';

    /**
     * WC_WooMercadoPago_CustomGateway constructor.
     * @throws WC_WooMercadoPago_Exception
     */
    public function __construct()
    {
        $this->id = self::ID;

        if (!$this->validateSection()) {
            return;
        }

        $this->form_fields = array();
        $this->method_title = __('Mercado Pago - Checkout personalizado', 'woocommerce-mercadopago');
        $this->title = __('Paga con tarjetas de débito y crédito', 'woocommerce-mercadopago');
        $this->method_description = $this->getMethodDescription('Acepta pagos con tarjeta en tu sitio web con la mejor financiación posible y maximiza la conversión de tu negocio. Con el checkout personalizado tus clientes pagan ¡sin salir de tu tienda!');
        $this->coupon_mode = $this->getOption('coupon_mode', 'no');
        $this->field_forms_order = $this->get_fields_sequence();
        parent::__construct();
        $this->form_fields = $this->getFormFields('Custom');
        $this->hook = new WC_WooMercadoPago_Hook_Custom($this);
        $this->notification = new WC_WooMercadoPago_Notification_Webhook($this);
    }

    /**
     * @param $label
     * @return array
     */
    public function getFormFields($label)
    {
        if (is_admin()) {
            wp_enqueue_script('woocommerce-mercadopago-custom-config-script', plugins_url('../assets/js/custom_config_mercadopago.js', plugin_dir_path(__FILE__)));
        }

        if (empty($this->checkout_country)) {
            $this->field_forms_order = array_slice($this->field_forms_order, 0, 7);
        }

        if(!empty($this->checkout_country) && empty($this->getAccessToken())) {
            $this->field_forms_order = array_slice($this->field_forms_order, 0, 22);
        }

        $form_fields = array();
        $form_fields['checkout_custom_header'] = $this->field_checkout_custom_header();
        if (!empty($this->checkout_country) && !empty($this->getAccessToken())) {
            $form_fields['checkout_custom_options_title'] = $this->field_checkout_custom_options_title();
            $form_fields['checkout_custom_options_subtitle'] = $this->field_checkout_custom_options_subtitle();
            $form_fields['checkout_custom_payments_title'] = $this->field_checkout_custom_payments_title();
            $form_fields['checkout_payments_subtitle'] = $this->field_checkout_payments_subtitle();
            $form_fields['binary_mode'] = $this->field_binary_mode();
            $form_fields['checkout_custom_payments_advanced_title'] = $this->field_checkout_custom_payments_advanced_title();
            $form_fields['coupon_mode'] = $this->field_coupon_mode();
        }
        $form_fields_abs = parent::getFormFields($label);
        if (count($form_fields_abs) == 1) {
            return $form_fields_abs;
        }
        $form_fields_merge = array_merge($form_fields_abs, $form_fields);
        $fields = $this->sortFormFields($form_fields_merge, $this->field_forms_order);

        return $fields;
    }

    /**
     * get_fields_sequence
     *
     * @return array
     */
    public function get_fields_sequence()
    {
        return [
            // Necessary to run
            'title',
            'description',
            // Checkout de pagos con tarjetas de débito y crédito<br> Aceptá pagos al instante y maximizá la conversión de tu negocio 
            'checkout_custom_header',
            'checkout_steps',
            // ¿En qué país vas a activar tu tienda?
            'checkout_country_title',
            'checkout_country',
            'checkout_btn_save',
            // Carga tus credenciales
            'checkout_credential_title',
            'checkout_credential_mod_test_title',
            'checkout_credential_mod_test_description',
            'checkout_credential_mod_prod_title',
            'checkout_credential_mod_prod_description',
            'checkout_credential_production',
            'checkout_credential_link',
            'checkout_credential_title_test',
            'checkout_credential_description_test',
            '_mp_public_key_test',
            '_mp_access_token_test',
            'checkout_credential_title_prod',
            'checkout_credential_description_prod',
            '_mp_public_key_prod',
            '_mp_access_token_prod',
            // No olvides de homologar tu cuenta
            'checkout_homolog_title',
            'checkout_homolog_subtitle',
            'checkout_homolog_link',
            // Configura la experiencia de pago en tu tienda
            'checkout_custom_options_title',
            'checkout_custom_options_subtitle',
            'mp_statement_descriptor',
            '_mp_category_id',
            '_mp_store_identificator',
            // Ajustes avanzados
            'checkout_advanced_settings',
            '_mp_debug_mode',
            '_mp_custom_domain',
            // Configura la experiencia de pago personalizada en tu tienda
            'checkout_custom_payments_title',
            'checkout_payments_subtitle',
            'checkout_payments_description',
            'enabled',
            // Configuración avanzada de la experiencia de pago personalizada
            'checkout_custom_payments_advanced_title',
            'checkout_payments_advanced_description',
            'coupon_mode',
            'binary_mode',
            'gateway_discount',
            'commission',
            // ¿Todo listo para el despegue de tus ventas?
            'checkout_ready_title',
            'checkout_ready_description',
            'checkout_ready_description_link'
        ];
    }

    /**
     * @return array
     */
    public function field_checkout_custom_header()
    {
        $checkout_custom_header = array(
            'title' => sprintf(
                __('Checkout de pagos con tarjetas de débito y crédito<br> Acepta pagos al instante y maximiza la conversión de tu negocio %s', 'woocommerce-mercadopago'),
                '<div class="row">
              <div class="mp-col-md-12">
                <p class="text-checkout-body mp-mb-0">
                  ' . __('Convierte tu tienda online en una pasarela de pagos segura y fácil de usar para tus clientes. Con el checkout personalizado tus clientes pagan ¡sin salir de tu tienda!', 'woocommerce-mercadopago') . '
                </p>
              </div>
            </div>'
            ),
            'type' => 'title',
            'class' => 'mp_title_checkout'
        );
        return $checkout_custom_header;
    }

    /**
     * @return array
     */
    public function field_checkout_custom_options_title()
    {
        $checkout_custom_options_title = array(
            'title' => __('Configura la experiencia de pago en tu tienda', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_title_bd'
        );
        return $checkout_custom_options_title;
    }

    /**
     * @return array
     */
    public function field_checkout_custom_options_subtitle()
    {
        $checkout_custom_options_subtitle = array(
            'title' => __('Ve a lo básico. Coloca la información de tu negocio.', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_subtitle mp-mt-5'
        );
        return $checkout_custom_options_subtitle;
    }

    /**
     * @return array
     */
    public function field_checkout_custom_payments_title()
    {
        $checkout_custom_payments_title = array(
            'title' => __('Configura la experiencia de pago personalizada en tu tienda', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_title_bd'
        );
        return $checkout_custom_payments_title;
    }

    /**
     * @return array
     */
    public function field_checkout_custom_payments_advanced_title()
    {
        $checkout_custom_payments_advanced_title = array(
            'title' => __('Configuración avanzada de la experiencia de pago personalizada', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_subtitle_bd'
        );
        return $checkout_custom_payments_advanced_title;
    }

    /**
     * @param $status_detail
     * @return mixed
     */
    public function get_order_status($status_detail)
    {
        switch ($status_detail) {
            case 'accredited':
                return __('Listo, su pago ha sido aceptado!', 'woocommerce-mercadopago');
            case 'pending_contingency':
                return __('Estamos procesando su pago. En menos de una hora le enviaremos el resultado por correo electrónico.', 'woocommerce-mercadopago');
            case 'pending_review_manual':
                return __('Estamos procesando su pago. En menos de 2 días le enviaremos por correo electrónico si se ha aprobado el pago o si se necesita información adicional.', 'woocommerce-mercadopago');
            case 'cc_rejected_bad_filled_card_number':
                return __('Compruebe el número de tarjeta.', 'woocommerce-mercadopago');
            case 'cc_rejected_bad_filled_date':
                return __('Compruebe la fecha de expiración.', 'woocommerce-mercadopago');
            case 'cc_rejected_bad_filled_other':
                return __('Compruebe la información informada.', 'woocommerce-mercadopago');
            case 'cc_rejected_bad_filled_security_code':
                return __('Compruebe el código de seguridad informado.', 'woocommerce-mercadopago');
            case 'cc_rejected_blacklist':
                return __('No se puede procesar su pago.', 'woocommerce-mercadopago');
            case 'cc_rejected_call_for_authorize':
                return __('Usted debe autorizar los pagos de sus órdenes.', 'woocommerce-mercadopago');
            case 'cc_rejected_card_disabled':
                return __('Póngase en contacto con el emisor de su tarjeta para activarla. El teléfono se encuentra en la parte posterior de su tarjeta.', 'woocommerce-mercadopago');
            case 'cc_rejected_card_error':
                return __('No se puede procesar su pago.', 'woocommerce-mercadopago');
            case 'cc_rejected_duplicated_payment':
                return __('Usted ya realizó un pago de este importe. Si tiene que pagar de nuevo, utilizar otra tarjeta u otro medio de pago.', 'woocommerce-mercadopago');
            case 'cc_rejected_high_risk':
                return __('Su pago fue rechazado. Por favor seleccione otro medio de pago. Se recomienda en efectivo.', 'woocommerce-mercadopago');
            case 'cc_rejected_insufficient_amount':
                return __('Su pago no tiene fondos suficientes.', 'woocommerce-mercadopago');
            case 'cc_rejected_invalid_installments':
                return __('El pago no puede procesar la cuota seleccionada.', 'woocommerce-mercadopago');
            case 'cc_rejected_max_attempts':
                return __('Has alcanzado el límite de intentos permitidos. Elija otra tarjeta u otro medio de pago.', 'woocommerce-mercadopago');
            case 'cc_rejected_other_reason':
                return __('Esta medio de pago no puede procesar su pago.', 'woocommerce-mercadopago');
            default:
                return __('Esta medio de pago no puede procesar su pago.', 'woocommerce-mercadopago');
        }
    }

    /**
     * Payment Fields
     */
    public function payment_fields()
    {
        //add css
        wp_enqueue_style(
            'woocommerce-mercadopago-basic-checkout-styles',
            plugins_url('../assets/css/basic_checkout_mercadopago.css', plugin_dir_path(__FILE__))
        );
        
        $amount = $this->get_order_total();
        $logged_user_email = (wp_get_current_user()->ID != 0) ? wp_get_current_user()->user_email : null;
        $customer = isset($logged_user_email) ? $this->mp->get_or_create_customer($logged_user_email) : null;
        $discount_action_url = get_site_url() . '/index.php/woocommerce-mercadopago/?wc-api=' . get_class($this);

        $currency_ratio = 1;
        $_mp_currency_conversion_v1 = $this->getOption('_mp_currency_conversion_v1', '');
        if (!empty($_mp_currency_conversion_v1)) {
            $currency_ratio = WC_WooMercadoPago_Module::get_conversion_rate($this->site_data['currency']);
            $currency_ratio = $currency_ratio > 0 ? $currency_ratio : 1;
        }

        $banner_url = $this->getOption('_mp_custom_banner');
        if (!isset($banner_url) || empty($banner_url)) {
            $banner_url = $this->site_data['checkout_banner_custom'];
        }

        //credit or debit card
        $debit_card = array();
        $credit_card = array();
        $tarjetas = get_option('_checkout_payments_methods', '');

        foreach ($tarjetas as $tarjeta) {
          if ($tarjeta['type'] == 'credit_card') {
              $credit_card[] = $tarjeta['image'];
          } elseif ($tarjeta['type'] == 'debit_card' || $tarjeta['type'] == 'prepaid_card') {
              $debit_card[] = $tarjeta['image'];
          }
        }

        $parameters = array(
            'amount' => $amount,
            'site_id' => $this->getOption('_site_id_v1'),
            'public_key' => $this->getPublicKey(),
            'coupon_mode' => isset($logged_user_email) ? $this->coupon_mode : 'no',
            'discount_action_url' => $discount_action_url,
            'payer_email' => $logged_user_email,
            'images_path' => plugins_url('../assets/images/', plugin_dir_path(__FILE__)),
            'banner_path' => $banner_url,
            'customer_cards' => isset($customer) ? (isset($customer['cards']) ? $customer['cards'] : array()) : array(),
            'customerId' => isset($customer) ? (isset($customer['id']) ? $customer['id'] : null) : null,
            'currency_ratio' => $currency_ratio,
            'woocommerce_currency' => get_woocommerce_currency(),
            'account_currency' => $this->site_data['currency'],
            'path_to_javascript' => plugins_url('../assets/js/credit-card.js', plugin_dir_path(__FILE__)),
            'debit_card' => $debit_card,
            'credit_card' => $credit_card,
        );

        wc_get_template('checkout/custom_checkout.php', $parameters, 'woo/mercado/pago/module/', WC_WooMercadoPago_Module::get_templates_path());
    }

    /**
     * @param int $order_id
     * @return array|void
     */
    public function process_payment($order_id)
    {
        $custom_checkout = $_POST['mercadopago_custom'];
        $this->log->write_log(__FUNCTION__, 'POST Custom: ' . json_encode($custom_checkout, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $order = wc_get_order($order_id);
        if (method_exists($order, 'update_meta_data')) {
            $order->update_meta_data('_used_gateway', get_class($this));
            $order->save();
        } else {
            update_post_meta($order_id, '_used_gateway', get_class($this));
        }
        // Mexico country case.
        if (!isset($custom_checkout['paymentMethodId']) || empty($custom_checkout['paymentMethodId'])) {
            $custom_checkout['paymentMethodId'] = $custom_checkout['paymentMethodSelector'];
        }
        if (
            isset($custom_checkout['amount']) && !empty($custom_checkout['amount']) &&
            isset($custom_checkout['token']) && !empty($custom_checkout['token']) &&
            isset($custom_checkout['paymentMethodId']) && !empty($custom_checkout['paymentMethodId']) &&
            isset($custom_checkout['installments']) && !empty($custom_checkout['installments']) &&
            $custom_checkout['installments'] != -1
        ) {
            $response = $this->create_preference($order, $custom_checkout);

            // Check for card save.
            if (method_exists($order, 'update_meta_data')) {
                if (isset($custom_checkout['doNotSaveCard'])) {
                    $order->update_meta_data('_save_card', 'no');
                } else {
                    $order->update_meta_data('_save_card', 'yes');
                }
                $order->save();
            } else {
                if (isset($custom_checkout['doNotSaveCard'])) {
                    update_post_meta($order_id, '_save_card', 'no');
                } else {
                    update_post_meta($order_id, '_save_card', 'yes');
                }
            }
            if (!is_array($response)) {
                return array(
                    'result' => 'fail',
                    'redirect' => '',
                );
            }
            // Switch on response.
            if (array_key_exists('status', $response)) {
                switch ($response['status']) {
                    case 'approved':
                        WC()->cart->empty_cart();
                        wc_add_notice('<p>' . $this->get_order_status('accredited') . '</p>', 'notice');
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_order_received_url()
                        );
                        break;
                    case 'pending':
                        // Order approved/pending, we just redirect to the thankyou page.
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_order_received_url()
                        );
                        break;
                    case 'in_process':
                        // For pending, we don't know if the purchase will be made, so we must inform this status.
                        WC()->cart->empty_cart();
                        wc_add_notice(
                            '<p>' . $this->get_order_status($response['status_detail']) . '</p>' .
                                '<p><a class="button" href="' . esc_url($order->get_checkout_order_received_url()) . '">' .
                                __('Ver su hoja de pedido', 'woocommerce-mercadopago') .
                                '</a></p>',
                            'notice'
                        );
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_payment_url(true)
                        );
                        break;
                    case 'rejected':
                        // If rejected is received, the order will not proceed until another payment try, so we must inform this status.
                        wc_add_notice(
                            '<p>' . __('Su pago fue rechazado. Puede intentarlo de nuevo.', 'woocommerce-mercadopago') . '<br>' .
                                $this->get_order_status($response['status_detail']) .
                                '</p>' .
                                '<p><a class="button" href="' . esc_url($order->get_checkout_payment_url()) . '">' .
                                __('Haz clic para intentarlo de nuevo', 'woocommerce-mercadopago') .
                                '</a></p>',
                            'error'
                        );
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_payment_url(true)
                        );
                        break;
                    case 'cancelled':
                    case 'in_mediation':
                    case 'charged-back':
                        // If we enter here (an order generating a direct [cancelled, in_mediation, or charged-back] status),
                        // them there must be something very wrong!
                        break;
                    default:
                        break;
                }
            } else {
                // Process when fields are imcomplete.
                $this->log->write_log(__FUNCTION__, 'A problem was occurred when processing your payment. Are you sure you have correctly filled all information in the checkout form? ');

                wc_add_notice('<p>' . __('A problem was occurred when processing your payment. Are you sure you have correctly filled all information in the checkout form?', 'woocommerce-mercadopago') . ' MERCADO PAGO: ' .
                    WC_WooMercadoPago_Module::get_common_error_messages($response) . '</p>', 'error');
                return array(
                    'result' => 'fail',
                    'redirect' => '',
                );
            }
        } else {
            $this->log->write_log(__FUNCTION__, 'A problem was occurred when processing your payment. Please, try again.' );
            wc_add_notice('<p>' . __('A problem was occurred when processing your payment. Please, try again.', 'woocommerce-mercadopago') . '</p>', 'error');
            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }
    }

    /**
     * @param $order
     * @param $custom_checkout
     * @return string|array
     */
    protected function create_preference($order, $custom_checkout)
    {
        $preferencesCustom = new WC_WooMercadoPago_PreferenceCustom($this, $order, $custom_checkout);
        $preferences = $preferencesCustom->get_preference();
        try {
            $checkout_info = $this->mp->post('/v1/payments', json_encode($preferences));
            $this->log->write_log(__FUNCTION__, 'Preference created: ' . json_encode($checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            if ($checkout_info['status'] < 200 || $checkout_info['status'] >= 300) {
                $this->log->write_log(__FUNCTION__, 'mercado pago gave error, payment creation failed with error: ' . $checkout_info['response']['message']);
                return $checkout_info['response']['message'];
            } elseif (is_wp_error($checkout_info)) {
                $this->log->write_log(__FUNCTION__, 'wordpress gave error, payment creation failed with error: ' . $checkout_info['response']['message']);
                return $checkout_info['response']['message'];
            } else {
                $this->log->write_log(__FUNCTION__, 'payment link generated with success from mercado pago, with structure as follow: ' . json_encode($checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                return $checkout_info['response'];
            }
        } catch (WC_WooMercadoPago_Exception $ex) {
            $this->log->write_log(__FUNCTION__, 'payment creation failed with exception: ' . json_encode($ex, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $ex->getMessage();
        }
    }

    /**
     * @param $checkout_info
     */
    public function check_and_save_customer_card($checkout_info)
    {
        $this->log->write_log(__FUNCTION__, 'checking info to create card: ' . json_encode($checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $custId = null;
        $token = null;
        $issuer_id = null;
        $payment_method_id = null;
        if (isset($checkout_info['payer']['id']) && !empty($checkout_info['payer']['id'])) {
            $custId = $checkout_info['payer']['id'];
        } else {
            return;
        }
        if (isset($checkout_info['metadata']['token']) && !empty($checkout_info['metadata']['token'])) {
            $token = $checkout_info['metadata']['token'];
        } else {
            return;
        }
        if (isset($checkout_info['issuer_id']) && !empty($checkout_info['issuer_id'])) {
            $issuer_id = (int) ($checkout_info['issuer_id']);
        }
        if (isset($checkout_info['payment_method_id']) && !empty($checkout_info['payment_method_id'])) {
            $payment_method_id = $checkout_info['payment_method_id'];
        }
        try {
            $this->mp->create_card_in_customer($custId, $token, $payment_method_id, $issuer_id);
        } catch (WC_WooMercadoPago_Exception $ex) {
            $this->log->write_log(__FUNCTION__, 'card creation failed: ' . json_encode($ex, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * @return bool
     */
    public function is_available()
    {
        if (!parent::is_available()) {
            return false;
        }

        $_mp_access_token = get_option('_mp_access_token_prod');
        $is_prod_credentials = strpos($_mp_access_token, 'TEST') === false;

        if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') && $is_prod_credentials) {
            $this->log->write_log(__FUNCTION__, 'NO HTTPS, Custom unavailable.');
            return false;
        }

        return true;
    }
}
