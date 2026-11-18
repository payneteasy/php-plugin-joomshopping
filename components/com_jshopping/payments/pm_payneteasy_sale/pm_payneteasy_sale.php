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
     * @return      array       [<order_parameter_name>, <order_parameter_label>]
     */
    public function getDisplayNameParams()
    {
        return array
        (
            'credit_card_owner'         => _JSHOP_PAYNETEASY_CREDIT_CARD_OWNER,
            'credit_card_number'        => _JSHOP_PAYNETEASY_CREDIT_CARD_NUMBER,
            'credit_card_expire_month'  => _JSHOP_PAYNETEASY_CREDIT_CARD_EXPIRE_MONTH,
            'credit_card_expire_year'   => _JSHOP_PAYNETEASY_CREDIT_CARD_EXPIRE_YEAR,
            'credit_card_cvv2'          => _JSHOP_PAYNETEASY_CREDIT_CARD_CVV2
        );
    }
}
