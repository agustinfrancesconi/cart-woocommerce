<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 *
 * WC_WooMercadoPago_BasicGateway
 *
 */
class WC_WooMercadoPago_BasicGateway extends WC_WooMercadoPago_PaymentAbstract
{
    const ID = 'woo-mercado-pago-basic';
    /**
     * WC_WooMercadoPago_BasicGateway constructor.
     * @throws WC_WooMercadoPago_Exception
     */
    public function __construct()
    {
        $this->id = self::ID;

        if (!$this->validateSection()) {
            return;
        }

        $this->form_fields = array();
        $this->method_title = __('Mercado Pago - Checkout básico', 'woocommerce-mercadopago');
        $this->method = $this->getOption('method', 'redirect');
        $this->title = __('Paga con el medio de pago que prefieras', 'woocommerce-mercadopago');
        $this->method_description = $this->getMethodDescription('Ofrece todos los medios de pago: tarjetas de crédito y débito, dinero en efectivo y dinero en cuenta. Tus clientes eligen si pagan como invitados o desde su cuenta de Mercado Pago.');
        $this->auto_return = $this->getOption('auto_return', 'yes');
        $this->success_url = $this->getOption('success_url', '');
        $this->failure_url = $this->getOption('failure_url', '');
        $this->pending_url = $this->getOption('pending_url', '');
        $this->installments = $this->getOption('installments', '24');
        $this->gateway_discount = $this->getOption('gateway_discount', 0);
        $this->clientid_old_version = $this->getClientId();
        $this->field_forms_order = $this->get_fields_sequence();
        $this->ex_payments = $this->getExPayments();
        parent::__construct();
        $this->form_fields = $this->getFormFields('Basic');
        $this->hook = new WC_WooMercadoPago_Hook_Basic($this);
        $this->notification = new WC_WooMercadoPago_Notification_IPN($this);
    }

    /**
     * @param $label
     * @return array
     */
    public function getFormFields($label)
    {
        if (is_admin()) {
            wp_enqueue_script(
                'woocommerce-mercadopago-basic-config-script',
                plugins_url('../assets/js/basic_config_mercadopago.js', plugin_dir_path(__FILE__))
            );
        }

        if (empty($this->checkout_country)) {
            $this->field_forms_order = array_slice($this->field_forms_order, 0, 7);
        }

        if (!empty($this->checkout_country) && empty($this->getAccessToken())) {
            $this->field_forms_order = array_slice($this->field_forms_order, 0, 22);
        }

        $form_fields = array();

        $form_fields['checkout_header'] = $this->field_checkout_header();

        if (!empty($this->checkout_country) && !empty($this->getAccessToken())) {
            $form_fields['checkout_options_title'] = $this->field_checkout_options_title();
            $form_fields['checkout_options_subtitle'] = $this->field_checkout_options_subtitle();
            $form_fields['checkout_payments_title'] = $this->field_checkout_payments_title();
            $form_fields['checkout_payments_subtitle'] = $this->field_checkout_payments_subtitle();
            $form_fields['binary_mode'] = $this->field_binary_mode();
            $form_fields['installments'] = $this->field_installments();
            $form_fields['checkout_payments_advanced_title'] = $this->field_checkout_payments_advanced_title();
            $form_fields['method'] = $this->field_method();
            $form_fields['success_url'] = $this->field_success_url();
            $form_fields['failure_url'] = $this->field_failure_url();
            $form_fields['pending_url'] = $this->field_pending_url();
            $form_fields['auto_return'] = $this->field_auto_return();
            foreach ($this->field_ex_payments() as $key => $value) {
                $form_fields[$key] = $value;
            }
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
            // Checkout Básico. Acepta todos los medios de pago y lleva tus cobros a otro nivel
            'checkout_header',
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
            // Configura WooCommerce Mercado Pago
            'checkout_options_title',
            'checkout_options_subtitle',
            'mp_statement_descriptor',
            '_mp_category_id',
            '_mp_store_identificator',
            // Ajustes avanzados
            'checkout_advanced_settings',
            '_mp_debug_mode',
            '_mp_custom_domain',
            // Configura la experiencia de pago en tu tienda
            'checkout_payments_title',
            'checkout_payments_subtitle',
            'checkout_payments_description',
            'enabled',
            'installments',
            // Configuración Avanzada de la experiencia de pago
            'checkout_payments_advanced_title',
            'checkout_payments_advanced_description',
            'method',
            'auto_return',
            'success_url',
            'failure_url',
            'pending_url',
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
     * @return bool
     */
    public function is_available()
    {
        if (parent::is_available()) {
            return true;
        }

        if ($this->settings['enabled'] == 'yes') {
            if ($this->mp instanceof MP) {
                $accessToken = $this->mp->get_access_token();
                if (strpos($accessToken, 'APP_USR') === false && strpos($accessToken, 'TEST') === false) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Get clientID when update version 3.0.17 to 4 latest
     * @return string
     */
    public function getClientId() {
        $clientId = get_option( '_mp_client_id', '');
         if(!empty($clientId)){
           return true;
         }
         return false;
   }
  

    /**
     * @return array
     */
    private function getExPayments()
    {
        $ex_payments = array();
        $get_ex_payment_options = $this->getOption('_all_payment_methods_v0', '');
        if (!empty($get_ex_payment_options)) {
            foreach ($get_ex_payment_options = explode(',', $get_ex_payment_options) as $get_ex_payment_option) {
                if ($this->getOption('ex_payments_' . $get_ex_payment_option, 'yes') == 'no') {
                    $ex_payments[] = $get_ex_payment_option;
                }
            }
        }
        return $ex_payments;
    }

    /**
     * @return array
     */
    public function field_checkout_header()
    {
        $checkout_header = array(
            'title' => sprintf(
                __('Checkout Básico. Acepta todos los medios de pago y lleva tus cobros a otro nivel %s', 'woocommerce-mercadopago'),
                '<div class="row">
              <div class="mp-col-md-12">
                <p class="text-checkout-body mp-mb-0">
                  ' . __('Convierte tu tienda online en la pasarela de pagos preferida de tus clientes. Elige si la experiencia de pago final será dentro o fuera de tu tienda.', 'woocommerce-mercadopago') . '
                </p>
              </div>
            </div>'
            ),
            'type' => 'title',
            'class' => 'mp_title_checkout'
        );
        return $checkout_header;
    }

    /**
     * @return array
     */
    public function field_checkout_options_title()
    {
        $checkout_options_title = array(
            'title' => __('Configura WooCommerce Mercado Pago', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_title_bd'
        );
        return $checkout_options_title;
    }

    /**
     * @return array
     */
    public function field_checkout_options_description()
    {
        $checkout_options_description = array(
            'title' => __('Habilitá la experiencia del checkout básico de Mercado Pago en tu tienda online, seleccioná los medios de pago disponibles para tus clientes y<br> definí el máximo de cuotas en el que podrán pagarte.', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_small_text'
        );
        return $checkout_options_description;
    }

    /**
     * @return array
     */
    public function field_checkout_options_subtitle()
    {
        $checkout_options_subtitle = array(
            'title' => __('Ve a lo básico. Coloca la información de tu negocio.', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_subtitle mp-mt-5'
        );
        return $checkout_options_subtitle;
    }

    /**
     * @return array
     */
    public function field_checkout_payments_title()
    {
        $checkout_payments_title = array(
            'title' => __('Configura las preferencias de pago en tu tienda', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_title_bd'
        );
        return $checkout_payments_title;
    }

    /**
     * @return array
     */
    public function field_checkout_payments_advanced_title()
    {
        $checkout_payments_advanced_title = array(
            'title' => __('Configuración Avanzada de la experiencia de pago', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_subtitle_bd'
        );
        return $checkout_payments_advanced_title;
    }

    /**
     * @return array
     */
    public function field_method()
    {
        $method = array(
            'title' => __('Método de integración', 'woocommerce-mercadopago'),
            'type' => 'select',
            'description' => __('Define qué experiencia de pago tendrán tus clientes, si dentro o fuera de tu tienda.', 'woocommerce-mercadopago'),
            'default' => ($this->method == 'iframe') ? 'redirect' : $this->method,
            'options' => array(
                'redirect' => __('Redirect', 'woocommerce-mercadopago'),
                'modal' => __('Modal', 'woocommerce-mercadopago')
            )
        );
        return $method;
    }

    /**
     * @return array
     */
    public function field_success_url()
    {
        // Validate back URL.
        if (!empty($this->success_url) && filter_var($this->success_url, FILTER_VALIDATE_URL) === FALSE) {
            $success_back_url_message = '<img width="14" height="14" src="' . plugins_url('assets/images/warning.png', plugin_dir_path(__FILE__)) . '"> ' .
                __('Esto parece ser una URL no válida.', 'woocommerce-mercadopago') . ' ';
        } else {
            $success_back_url_message = __('Elige la URL que mostraremos a tus clientes cuando terminen su compra.', 'woocommerce-mercadopago');
        }
        $success_url = array(
            'title' => __('URL de éxito', 'woocommerce-mercadopago'),
            'type' => 'text',
            'description' => $success_back_url_message,
            'default' => ''
        );
        return $success_url;
    }

    /**
     * @return array
     */
    public function field_failure_url()
    {
        if (!empty($this->failure_url) && filter_var($this->failure_url, FILTER_VALIDATE_URL) === FALSE) {
            $fail_back_url_message = '<img width="14" height="14" src="' . plugins_url('assets/images/warning.png', plugin_dir_path(__FILE__)) . '"> ' .
                __('Esto parece ser una URL no válida.', 'woocommerce-mercadopago') . ' ';
        } else {
            $fail_back_url_message = __('Elige la URL que mostraremos a tus clientes cuando rechacemos su compra. Asegurate de que incluya un mensaje adecuado a la situación y dales información útil para que puedan solucionarlo.', 'woocommerce-mercadopago');
        }
        $failure_url = array(
            'title' => __('URL de pago rechazado', 'woocommerce-mercadopago'),
            'type' => 'text',
            'description' => $fail_back_url_message,
            'default' => ''
        );
        return $failure_url;
    }

    /**
     * @return array
     */
    public function field_pending_url()
    {
        // Validate back URL.
        if (!empty($this->pending_url) && filter_var($this->pending_url, FILTER_VALIDATE_URL) === FALSE) {
            $pending_back_url_message = '<img width="14" height="14" src="' . plugins_url('assets/images/warning.png', plugin_dir_path(__FILE__)) . '"> ' .
                __('Esto parece ser una URL no válida.', 'woocommerce-mercadopago') . ' ';
        } else {
            $pending_back_url_message = __('Elige la URL que mostraremos a tus clientes cuando tengan un pago pendiente de aprobación.', 'woocommerce-mercadopago');
        }
        $pending_url = array(
            'title' => __('URL de pago pendiente', 'woocommerce-mercadopago'),
            'type' => 'text',
            'description' => $pending_back_url_message,
            'default' => ''
        );
        return $pending_url;
    }

    /**
     * @return array
     */
    public function field_ex_payments()
    {
        $ex_payments = array();
        $ex_payments_sort = array();

        $all_payments = get_option('_checkout_payments_methods', '');
        $get_payment_methods = get_option('_all_payment_methods_v0', '');

        if (!empty($get_payment_methods)) {
            $get_payment_methods = explode(',', $get_payment_methods);
        }

        //change type atm to ticket
        foreach ($all_payments as $key => $value) {
            if ($value['type'] == 'atm') {
                $all_payments[$key]['type'] = 'ticket';
            } else {
                continue;
            }
        }

        //sort array by type asc
        usort($all_payments, function($a, $b) {
            if($a['type'] == $b['type']) return 0;
            return $b['type'] < $a['type'] ? 1 : -1;
        });

        $count_payment = 0;

        foreach ($all_payments as $payment_method) {
            if ($payment_method['type'] == 'account_money') { 
                continue; 
            } else {
                if ($payment_method['type'] == 'credit_card') {
                    $element = array(
                        'label' => $payment_method['name'],
                        'id' => 'woocommerce_mercadopago_' . $payment_method['id'],
                        'default' => 'yes',
                        'type' => 'checkbox',
                        'class' => 'online_payment_method',
                        'custom_attributes' => array(
                            'data-translate' => __('Selecciona tarjetas de crédito', 'woocommerce-mercadopago')
                        ),
                    );
                } elseif ($payment_method['type'] == 'debit_card' || $payment_method['type'] == 'prepaid_card') {
                    $element = array(
                        'label' => $payment_method['name'],
                        'id' => 'woocommerce_mercadopago_' . $payment_method['id'],
                        'default' => 'yes',
                        'type' => 'checkbox',
                        'class' => 'debit_payment_method',
                        'custom_attributes' => array(
                            'data-translate' => __('Selecciona tarjetas de débito', 'woocommerce-mercadopago')
                        ),
                    );
                } else {
                    $element = array(
                        'label' => $payment_method['name'],
                        'id' => 'woocommerce_mercadopago_' . $payment_method['id'],
                        'default' => 'yes',
                        'type' => 'checkbox',
                        'class' => 'offline_payment_method',
                        'custom_attributes' => array(
                            'data-translate' => __('Selecciona pagos offline', 'woocommerce-mercadopago')
                        ),
                    );
                }
            }

            $count_payment++;

            if ($count_payment == 1) {
                $element['title'] = __('Medios de pago', 'woocommerce-mercadopago');
                $element['desc_tip'] = __('Selecciona los medios de pago disponibles en tu tienda.', 'woocommerce-services');
            }
            if ($count_payment == count($get_payment_methods)) {
                $element['description'] = __('Habilita los medios de pago disponibles para tus clientes.', 'woocommerce-mercadopago');
            }

            $ex_payments["ex_payments_" . $payment_method['id']] = $element;
            $ex_payments_sort[] = "ex_payments_" . $payment_method['id'];
        }

        array_splice($this->field_forms_order, 37, 0, $ex_payments_sort);

        return $ex_payments;
    }

    /**
     * @return array
     */
    public function field_auto_return()
    {
        $auto_return = array(
            'title' => __('Volver a la tienda', 'woocommerce-mercadopago'),
            'type' => 'select',
            'default' => 'yes',
            'description' => __('Que tu cliente vuelva automáticamente a la tienda después del pago.', 'woocommerce-mercadopago'),
            'options' => array(
                'yes' => __('Sí', 'woocommerce-mercadopago'),
                'no' => __('No', 'woocommerce-mercadopago'),
            )
        );
        return $auto_return;
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

        //validate active payments methods
        $debito = 0;
        $credito = 0;
        $efectivo = 0;
        $tarjetas = get_option('_checkout_payments_methods', '');
        $installments = $this->getOption('installments');
        $str_cuotas = "cuotas";
        $cho_tarjetas = array();

        if ($installments == 1) {
            $str_cuotas = "cuota";
        }

        //change type account_money to ticket
        foreach ($tarjetas as $key => $value) {
            if ($value['type'] == 'account_money') {
                $all_payments[$key]['type'] = 'ticket';
            } else {
                continue;
            }
        }

        foreach ($tarjetas as $tarjeta) {
            if ($this->get_option($tarjeta['config'], '') == 'yes') {
                $cho_tarjetas[] = $tarjeta;
                if ($tarjeta['type'] == 'credit_card') {
                    $credito += 1;
                } elseif ($tarjeta['type'] == 'debit_card' || $tarjeta['type'] == 'prepaid_card') {
                    $debito += 1;
                } else {
                    $efectivo += 1;
                }
            }
        }

        $parameters = array(
            "debito" => $debito,
            "credito" => $credito,
            "efectivo" => $efectivo,
            "tarjetas" => $cho_tarjetas,
            "str_cuotas" => $str_cuotas,
            "installments" => $installments,
            "cho_image" => plugins_url('../assets/images/redirect_checkout.png', plugin_dir_path(__FILE__)),
            "path_to_javascript" => plugins_url('../assets/js/basic-cho.js', plugin_dir_path(__FILE__)),
        );

        wc_get_template('checkout/basic_checkout.php', $parameters, 'woo/mercado/pago/module/', WC_WooMercadoPago_Module::get_templates_path());
    }

    /**
     * @param $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);
        if (method_exists($order, 'update_meta_data')) {
            $order->update_meta_data('_used_gateway', get_class($this));
            $order->save();
        } else {
            update_post_meta($order_id, '_used_gateway', get_class($this));
        }

        if ('redirect' == $this->method || 'iframe' == $this->method) {
            $this->log->write_log(__FUNCTION__, 'customer being redirected to Mercado Pago.');
            return array(
                'result' => 'success',
                'redirect' => $this->create_preference($order)
            );
        } elseif ('modal' == $this->method) {
            $this->log->write_log(__FUNCTION__, 'preparing to render Mercado Pago checkout view.');
            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true)
            );
        }
    }

    /**
     * @param $order
     * @return bool
     */
    public function create_preference($order)
    {
        $preferencesBasic = new WC_WooMercadoPago_PreferenceBasic($this, $order);
        $preferences = $preferencesBasic->get_preference();
        try {
            $checkout_info = $this->mp->create_preference(json_encode($preferences));
            $this->log->write_log(__FUNCTION__, 'Created Preference: ' . json_encode($checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            if ($checkout_info['status'] < 200 || $checkout_info['status'] >= 300) {
                $this->log->write_log(__FUNCTION__, 'mercado pago gave error, payment creation failed with error: ' . $checkout_info['response']['message']);
                return false;
            } elseif (is_wp_error($checkout_info)) {
                $this->log->write_log(__FUNCTION__, 'wordpress gave error, payment creation failed with error: ' . $checkout_info['response']['message']);
                return false;
            } else {
                $this->log->write_log(__FUNCTION__, 'payment link generated with success from mercado pago, with structure as follow: ' . json_encode($checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                if ($this->sandbox) {
                    return $checkout_info['response']['sandbox_init_point'];
                }
                return $checkout_info['response']['init_point'];
            }
        } catch (WC_WooMercadoPago_Exception $ex) {
            $this->log->write_log(__FUNCTION__, 'payment creation failed with exception: ' . json_encode($ex, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return false;
        }
    }
}