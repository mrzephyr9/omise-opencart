<?php
class ControllerPaymentOmise extends Controller {
    /**
     * $error
     */
    private $error = array();

    /**
     * This method will fire when user click `install` button from `extension/payment` page
     * It will call `model/payment/omise.php` file and run `install` method for installl something
     * that necessary to use in Omise Payment Gateway module
     * @return void
     */
    public function install() {
        $this->load->model('payment/omise');
        $this->load->language('payment/omise');

        try {
            // Create new table for contain Omise Keys.
            if (!$this->model_payment_omise->install())
                throw new Exception($this->language->get('error_omise_table_install_failed'), 1);
        } catch (Exception $e) {
            // Uninstall Omise extension if it failed to install.
            $this->load->controller('extension/payment/uninstall');

            $this->session->data['error'] = $e->getMessage();
        }
    }

    /**
     * This method will fire when user click `Uninstall` button from `extension/payment` page
     * Uninstall anything about Omise Payment Gateway module that installed.
     * @return void
     */
    public function uninstall() {
        $this->load->model('payment/omise');
        $this->model_payment_omise->uninstall();;
    }

    public function index() {
        $this->load->model('payment/omise');
        $this->load->model('setting/setting');
        $this->load->language('payment/omise');
        $this->document->setTitle('Omise Payment Gateway Configuration');
        
        $data = array();

        /**
         * POST Request handle.
         *
         */
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $update = $this->request->post;
            $update['omise_3ds'] = isset($update['omise_3ds']) ? $update['omise_3ds'] : 0;

            // Update
            $this->model_setting_setting->editSetting('omise', $update);

            $this->session->data['success'] = $this->language->get('text_session_save');
            $this->response->redirect($this->url->link('payment/omise', 'token=' . $this->session->data['token'], 'SSL'));
        }

        // Setup error warning message
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        // Setup success message
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        // Page data. Setting tab
        $data = array_merge($data, array(
            'omise_status'    => $this->config->get('omise_status'),
            'omise_test_mode' => $this->config->get('omise_test_mode'),
            'omise_3ds'       => $this->config->get('omise_3ds'),
            'omise_pkey_test' => $this->config->get('omise_pkey_test'),
            'omise_skey_test' => $this->config->get('omise_skey_test'),
            'omise_pkey'      => $this->config->get('omise_pkey'),
            'omise_skey'      => $this->config->get('omise_skey')
        ));

        // Page data. Dashboard tab
        $data = array_merge($data, array(
            'omise_dashboard' => array(
                'error_warning' => '',
                'enabled'       => $this->config->get('omise_status')
            )
        ));

        if (!$data['omise_dashboard']['enabled']) {
            $data['omise_dashboard']['error_warning'] = $this->language->get('error_extension_disabled');
        } else {
            try {
                // Retrieve Omise Account.
                $omise_account = $this->model_payment_omise->getOmiseAccount();
                if (isset($omise_account['error']))
                    throw new Exception('Omise Account:: '.$omise_account['error'], 1); 

                $data['omise_dashboard']['account']['email']     = $omise_account['email'];
                $data['omise_dashboard']['account']['created']   = $omise_account['created'];

                // Retrieve Omise Balance.
                $omise_balance = $this->model_payment_omise->getOmiseBalance();
                if (isset($omise_balance['error']))
                    throw new Exception('Omise Balance:: '.$omise_balance['error'], 1);

                $data['omise_dashboard']['balance']['livemode']  = $omise_balance['livemode'];
                $data['omise_dashboard']['balance']['available'] = $omise_balance['available'];
                $data['omise_dashboard']['balance']['total']     = $omise_balance['total'];
                $data['omise_dashboard']['balance']['currency']  = $omise_balance['currency'];

                // Retrieve Omise Transfer List.
                $omise_transfer = $this->model_payment_omise->getOmiseTransferList();
                if (isset($omise_transfer['error']))
                    throw new Exception('Omise Transfer:: '.$omise_transfer['error'], 1);

                $data['omise_dashboard']['transfer']['data']     = array_reverse($omise_transfer['data']);
                $data['omise_dashboard']['transfer']['total']    = $omise_transfer['total'];
            } catch (Exception $e) {
                $data['omise_dashboard']['error_warning'] = $e->getMessage();
            }
            
        }

        // Page labels
        $data = array_merge($data, array(
            'heading_title'         => $this->language->get('heading_title'),
            'button_save'           => $this->language->get('button_save'),
            'action'                => $this->url->link('payment/omise', 'token=' . $this->session->data['token'], 'SSL'),
            'button_cancel'         => $this->language->get('button_cancel'),
            'cancel'                => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'text_form'             => $this->language->get('text_form'),
            'entry_status'          => $this->language->get('entry_status'),
            'text_enabled'          => $this->language->get('text_enabled'),
            'text_disabled'         => $this->language->get('text_disabled'),
            'transfer_url'          => $this->url->link('payment/omise/submittransfer', 'token=' . $this->session->data['token'], 'SSL'),
            'label_omise_pkey_test' => $this->language->get('label_omise_pkey_test'),
            'label_omise_skey_test' => $this->language->get('label_omise_skey_test'),
            'label_omise_pkey'      => $this->language->get('label_omise_pkey'),
            'label_omise_skey'      => $this->language->get('label_omise_skey'),
            'label_omise_mode_test' => $this->language->get('label_omise_mode_test'),
            'label_omise_mode_live' => $this->language->get('label_omise_mode_live'),
            'label_omise_3ds'       => $this->language->get('label_omise_3ds'),
        ));

        // Page templates
        $data = array_merge($data, array(
            'header'        => $this->load->controller('common/header'),
            'breadcrumbs'   => $this->_setBreadcrumb(null),
            'column_left'   => $this->load->controller('common/column_left'),
            'footer'        => $this->load->controller('common/footer')
        ));

        $this->response->setOutput($this->load->view('payment/omise.tpl', $data));
    }

    /**
     * Submit a `transfer` request to Omise server
     * @return void
     */
    public function submitTransfer() {
        $this->load->model('payment/omise');
        $this->load->language('payment/omise');

        try {
            // POST request handler.
            if (!$this->request->server['REQUEST_METHOD'] == 'POST')
                throw new Exception($this->language->get('error_needed_post_request'), 1);

            if (!isset($this->request->post['transfer_amount']))
                throw new Exception($this->language->get('error_need_amount_value'), 1);

            $transferring = $this->model_payment_omise->createOmiseTransfer($this->request->post['transfer_amount']);
            if (isset($transferring['error']))
                throw new Exception('Omise Transfer:: '.$transferring['error'], 1);
            else
                $this->session->data['success'] = $this->language->get('api_transfer_success');
        } catch (Exception $e) {
            $this->session->data['error'] = $e->getMessage();
        }
        
        $this->response->redirect($this->url->link('payment/omise', 'token=' . $this->session->data['token'], 'SSL'));
    }

    /**
     * Set page breadcrumb
     * @return self
     */
    private function _setBreadcrumb($current = null) {
        $this->load->language('payment/omise');

        // Set Breadcrumbs.
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $breadcrumbs[] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $breadcrumbs[] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('payment/omise', 'token=' . $this->session->data['token'], 'SSL'),             
            'separator' => ' :: '
        );

        if (!is_null($current)) {
            $breadcrumbs[] = $current;
        }

        return $breadcrumbs;
    }
}