<?php

defined('_JEXEC') or die();

jimport('joomla.log.log');

require_once JPATH_ROOT . '/components/vendor/autoload.php';
require_once __DIR__ . '/payment_aggregate.php';
require_once __DIR__ . '/payment_state.php';

use PaynetEasy\PaynetEasyApi\PaymentData\QueryConfig;

/**
 * Class pm_pay2pay
 */
abstract class pm_payneteasy_abstract extends PaymentRoot
{
    /**
     * Payment aggregate class
     *
     * @var string
     */
    static protected $aggregateClass;

    /**
     * Default payment plugin parameters
     *
     * @var array
     */
    static protected $defaultParams = array
    (
        'end_point'                     => '',
        'login'                         => '',
        'production_gateway'            => '',
        'sandbox_gateway'               => '',
        'signing_key'                   => '',
        'gateway_mode'                  => '',
        'transaction_end_status'        => '',
        'transaction_pending_status'    => '',
        'transaction_failed_status'     => ''
    );

    /**
     * Gateway mode list
     *
     * @var array
     */
    static protected $gatewayModeList = array
    (
        array
        (
            'text'  => 'Sandbox',
            'value' => QueryConfig::GATEWAY_MODE_SANDBOX
        ),
        array
        (
            'text'  => 'Production',
            'value' => QueryConfig::GATEWAY_MODE_PRODUCTION
        )
    );

    /**
     * Load language file
     */
    public function __construct()
    {
        if (empty(static::$aggregateClass))
        {
            throw new RuntimeException('aggregateClass can not be empty');
        }

        $this->loadLanguageFile();
    }

    /**
     * Display payment plugin parameters in admin interface
     *
     * @param       array       $savedParams         Saved payment plugin config
     */
    public function showAdminFormParams($savedParams)
    {
        if (empty($savedParams))
        {
            $params = static::$defaultParams;
        }
        else
        {
            $params = array_merge(static::$defaultParams, $savedParams);
        }

        $orderStatusList = JModelLegacy::getInstance('orders', 'JshoppingModel')
                                ->getAllOrderStatus();

        $gatewayModeList = static::$gatewayModeList;

        require_once __DIR__ . '/admin_params_form.php';
    }

    /**
     * Show additional fields in payment form
     *
     * @param       array       $paymentParams              Payment plugin config
     * @param       array       $paymentMethodConfig        Payment method config
     */
    public function showPaymentForm($paymentParams, $paymentMethodConfig)
    {
        $paymentPluginClass = $this->getPaymentPluginClass();

        require_once __DIR__ . '/payment_form.php';
    }

    /**
     * Start payment
     *
     * @param       array           $paymentParams      Payment plugin config
     * @param       jshopOrder      $order              Joomla order
     */
    public function showEndForm($paymentConfig, $order)
    {
        if ($order->order_status != $paymentConfig['transaction_pending_status'])
        {
            $this->showError(_JSHOP_PAYNETEASY_PENDING_STATUS_ERROR);
            return;
        }

        $response = $this->startSale($paymentConfig, $order);

        $this->redirect($response->getRedirectUrl());
    }

    /**
     * Normalize input parameters
     *
     * @param       array           $paymentParams      Payment plugin config
     *
     * @return      array                               Normalized input parameters
     */
    public function getUrlParams($paymentParams)
    {
        $input                       = JFactory::$application->input;

        return array
        (
            'order_id'          => $input->getInt('order_id', null),
            // The return and callback URLs carry the order hash: JoomShopping
            // rejects a request for an order with another hash
            'hash'              => $input->getString('hash', ''),
            'checkHash'         => 1,
            'checkReturnParams' => 1
        );
    }

    /**
     * Finish payment
     *
     * @param       array           $paymentParams      Payment plugin config
     * @param       jshopOrder      $order              Joomla order
     * @param       string          $action             Checkout action (notify, return, cancel)
     *
     * @return      array                               [<check_result_code>, <check_result_message>]
     */
    public function checkTransaction($paymentParams, $order, $action)
    {
        $this->loadPaymentStatus($order);

        // The customer return and the gateway callback both land here. Once the
        // payment is approved, a later call must not change the order: the
        // library rejects it as already finished, which used to cancel a paid order.
        if ($order->transaction_status === 'approved')
        {
            return array(1, '');
        }

        try
        {
            $response = $this->getPaymentAggregate($paymentParams)
                ->finishSale($order, $_REQUEST);
        }
        catch (Exception $e)
        {
            $this->logException($e);
            $this->savePaymentStatus($order, true);

            // An error is not a decline: leave the order status unchanged.
            return array(0, _JSHOP_PAYNETEASY_PAYMENT_NOT_PASSED);
        }

        $this->savePaymentStatus($order, true);

        if ($response->isApproved())
        {
            return array(1, '');
        }
        else
        {
            return array(3, _JSHOP_PAYNETEASY_PAYMENT_DECLINED);
        }
    }

    /**
     * Start sale
     *
     * @param       array           $paymentParams      Payment plugin config
     * @param       jshopOrder      $order              Joomla order
     */
    protected function startSale($paymentConfig, jshopOrder $order)
    {
        try
        {
            $response = $this
                ->getPaymentAggregate($paymentConfig)
                ->startSale($order, $this->getReturnUrl($order));
        }
        catch (Exception $e)
        {
            $this->logException($e);
            $this->savePaymentStatus($order);
            $this->showError(_JSHOP_PAYNETEASY_PAYMENT_NOT_PASSED);
            $this->cancelOrder($order);

            return;
        }

        $this->savePaymentStatus($order);

        return $response;
    }


    /**
     * Updates payment status
     *
     * @param       array           $paymentParams      Payment plugin config
     * @param       jshopOrder      $order              Joomla order
     */
    protected function updatePaymentStatus($paymentConfig, $order)
    {
        $this->loadPaymentStatus($order);

        try
        {
            $response = $this
                ->getPaymentAggregate($paymentConfig)
                ->updateStatus($order);
        }
        catch (Exception $e)
        {
            $this->logException($e);
            $this->savePaymentStatus($order, true);
            $this->showError(_JSHOP_PAYNETEASY_PAYMENT_NOT_PASSED);
            $this->cancelOrder($order);

            return;
        }

        $this->savePaymentStatus($order, true);

        if ($response->isApproved())
        {
            $this->finishOrder($order);
        }
        elseif ($response->isDeclined())
        {
            $this->showError(_JSHOP_PAYNETEASY_PAYMENT_DECLINED);
            $this->cancelOrder($order);
        }
        elseif ($response->isProcessing())
        {
            $this->showPaymentStatusUpdater($order);
        }
    }

    /**
     * Get aggregate for order processing
     *
     * @param       array       $paymentConfig      Payment plugin config
     *
     * @return      PaynetEasy\PaynetEasyApi\AbstractPaymentAggregate
     */
    protected function getPaymentAggregate(array $paymentConfig)
    {
        static $paynetProcessorAggregate = null;

        if (!$paynetProcessorAggregate)
        {
            $paynetProcessorAggregate = new static::$aggregateClass($paymentConfig);
        }

        return $paynetProcessorAggregate;
    }

    /**
     * Save payment status to DB
     *
     * @param       jshopOrder      $order      Joomla order
     */
    protected function savePaymentStatus(jshopOrder $order, $alreadyExists = false)
    {
        $paymentStatus = JTable::getInstance('paymentState', 'jshop');

        $paymentStatus->client_id             = $order->order_number;
        $paymentStatus->paynet_id             = $order->paynet_order_id;
        $paymentStatus->payment_status        = $order->payment_status;
        $paymentStatus->transaction_status    = $order->transaction_status;

        unset
        (
            $order->order_number,
            $order->paynet_order_id,
            $order->payment_status,
            $order->transaction_status
        );

        if ($alreadyExists)
        {
            $paymentStatus->store();
        }
        else
        {
            $paymentStatus->create();
        }
    }

    /**
     * Load payment status from DB
     *
     * @param       jshopOrder      $order      Joomla order
     */
    protected function loadPaymentStatus(jshopOrder $order)
    {
        $paymentStatus = JTable::getInstance('paymentState', 'jshop');
        $paymentStatus->load($order->order_number);

        $order->paynet_order_id     = $paymentStatus->paynet_id;
        $order->payment_status      = $paymentStatus->payment_status;
        $order->transaction_status  = $paymentStatus->transaction_status;
    }

    /**
     * Get url for final payment processing
     *
     * @param       jshopOrder      $order      Joomla order
     *
     * @return      string
     */
    protected function getReturnUrl(jshopOrder $order)
    {
        return $this->getActionUrl('return', $order);
    }

    /**
     * Display message as error
     *
     * @param       string      $message        Message text
     */
    protected function showError($message)
    {
        JFactory::getApplication()->enqueueMessage($message, 'error');
    }

    /**
     * Cancel order if error occured
     *
     * @param       jshopOrder      $order      Order
     */
    protected function cancelOrder(jshopOrder $order)
    {
        $this->redirect($this->getActionUrl('cancel', $order));
    }

    /**
     * Finish order
     *
     * @param       jshopOrder      $order      Order
     */
    protected function finishOrder(jshopOrder $order)
    {
        $this->redirect($this->getActionUrl('return', $order));
    }

    /**
     * Redirect to update payment status form
     *
     * @param       jshopOrder      $order      Joomla order
     */
    protected function showPaymentStatusUpdater(jshopOrder $order)
    {
        JFactory::getSession()->set('jshop_send_end_form', 0);
        $formAction = $this->getActionUrl('', $order, 6) . '&payment_stage=status';

        require_once __DIR__ . '/status_form.php';
    }

    protected function getActionUrl($action, $order, $step = 7)
    {
        $orderId = (int) $order->order_id;
        $hash    = urlencode((string) $order->order_hash);

        $host = JURI::getInstance()->toString(array("scheme",'host', 'port'));

        return $host . SEFLink('index.php?option=com_jshopping&' .
                                         'controller=checkout&' .
                                         "task=step{$step}&" .
                                         "act={$action}&" .
                                         "js_paymentclass={$this->getPaymentPluginClass()}&" .
                                         "order_id={$orderId}&" .
                                         "hash={$hash}");
    }

    /**
     * Redirect to given URL
     *
     * @param       string      $url        Url to redirect
     */
    protected function redirect($url)
    {
        JFactory::getApplication()->redirect($url);
    }

    /**
     * Log exception
     *
     * @param       Exception       $exception          Exception to log
     */
    protected function logException(Exception $exception)
    {
        // Validation messages quote the rejected value, which may be a card
        // number or a CVV: the values and long digit runs are masked, no trace
        $message = get_class($exception) . ': ' . $exception->getMessage();
        $message = preg_replace("/Value '.*?' (does not|is not)/s", "Value '***' $1", $message);
        $message = preg_replace('/\d{7,}/', '***', $message);

        JLog::add($message, JLog::ERROR);
    }

    /**
     * Load language file
     */
    protected function loadLanguageFile()
    {
        $lang_dir  = __DIR__ . '/lang/';
        $lang_file = $lang_dir . JFactory::getLanguage()->getTag() . '.php';

        if (file_exists($lang_file))
        {
            require_once $lang_file;
        }
        else
        {
            require_once $lang_dir . 'en-GB.php';
        }
    }

    /**
     * Get current payment plugin name
     *
     * @return      string
     */
    protected function getPaymentPluginClass()
    {
        return get_called_class();
    }
}
