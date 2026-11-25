<?php

defined('_JEXEC') or die();

require_once __DIR__ . '/../pm_payneteasy_abstract/pm_payneteasy_abstract.php';
require_once __DIR__ . '/payment_aggregate.php';

/**
 * Class pm_pay2pay
 */
class pm_payneteasy_sale extends pm_payneteasy_abstract
{
    /**
     * {@inheritdoc}
     */
    static protected $aggregateClass = 'PaynetEasy\PaynetEasyApi\SalePaymentAggregate';

    /**
     * Default credit card parameters
     *
     * @var array
     */
    static protected $defaultCreditCardParams = array
    (
        'credit_card_owner'         => '',
        'credit_card_number'        => '',
        'credit_card_expire_month'  => '',
        'credit_card_expire_year'   => '',
        'credit_card_cvv2'          => ''
    );

    /**
     * Show additional fields in payment form
     *
     * @param       array       $paymentFormParams          Payment form data
     * @param       array       $paymentMethodConfig        Payment method config
     */
    public function showPaymentForm($paymentFormParams, $paymentMethodConfig)
    {
        if (empty($paymentFormParams))
        {
            $params = static::$defaultCreditCardParams;
        }
        else
        {
            $params = array_merge(static::$defaultCreditCardParams, $paymentFormParams);
        }

        // The card number and the CVV are never printed back to the page
        $params['credit_card_number'] = '';
        $params['credit_card_cvv2']   = '';

        $paymentPluginClass = $this->getPaymentPluginClass();
        $startYear = (int) date("Y");
        $endYear   = $startYear + 10;

        require_once __DIR__ . '/payment_form.php';
    }

    /**
     * Select and execute needed payment action
     *
     * @param       array           $paymentParams      Payment plugin config
     * @param       jshopOrder      $order              Joomla order
     */
    public function showEndForm($paymentConfig, $order)
    {
        switch(JFactory::$application->input->getString('payment_stage', 'start'))
        {
            case 'start':
            {
                $this->startSale($paymentConfig, $order);
                $this->showPaymentStatusUpdater($order);
                break;
            }
            case 'status':
            {
                $this->updatePaymentStatus($paymentConfig, $order);
                break;
            }
        }
    }

    public function checkTransaction($paymentParams, $order, $action)
    {
        // The return URL can be opened by anyone: never trust it, ask the gateway.
        $this->loadPaymentStatus($order);

        if (empty($order->paynet_order_id))
        {
            return array(3, _JSHOP_PAYNETEASY_PAYMENT_NOT_PASSED);
        }

        try
        {
            $response = $this
                ->getPaymentAggregate($paymentParams)
                ->updateStatus($order);
        }
        catch (Exception $e)
        {
            $this->logException($e);
            $this->savePaymentStatus($order, true);

            return array(0, _JSHOP_PAYNETEASY_PAYMENT_NOT_PASSED);
        }

        $this->savePaymentStatus($order, true);

        if ($response->isApproved())
        {
            return array(1, '');
        }
        elseif ($response->isProcessing())
        {
            return array(2, '');
        }

        return array(3, _JSHOP_PAYNETEASY_PAYMENT_DECLINED);
    }

    /**
     * Get labels for order saved data
     *
     * Card fields are not listed, so they are never shown in the order.
     *
     * @return      array       [<order_parameter_name>, <order_parameter_label>]
     */
    public function getDisplayNameParams()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     *
     * JoomShopping stores the payment form fields (card number, CVV) in the
     * order and in the session. The sale request reads them from the loaded
     * order object, so the saved copies are erased before the request:
     * on an error the request ends with a redirect and nothing runs after it.
     */
    protected function startSale($paymentConfig, jshopOrder $order)
    {
        $this->forgetCardData($order);

        return parent::startSale($paymentConfig, $order);
    }

    /**
     * Remove card data saved with the order
     *
     * @param       jshopOrder      $order      Joomla order
     */
    protected function forgetCardData(jshopOrder $order)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__jshopping_orders'))
            ->set($db->quoteName('payment_params') . ' = ' . $db->quote(''))
            ->set($db->quoteName('payment_params_data') . ' = ' . $db->quote(''))
            ->where($db->quoteName('order_id') . ' = ' . (int) $order->order_id);
        $db->setQuery($query)->execute();

        // The copy the checkout keeps to show the payment form again
        JFactory::getSession()->set('pm_params', null);
    }
}
