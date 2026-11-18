<?php

defined('_JEXEC') or die();

require_once __DIR__ . '/../pm_payneteasy_abstract/pm_payneteasy_abstract.php';
require_once __DIR__ . '/payment_aggregate.php';

use Joomla\Component\Jshopping\Site\Table\OrderTable;

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
        // payment has been checked in updatePaymentStatus() method
        return array(1, '');
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
            'credit_card_owner'         => \JText::_('JSHOP_PAYNETEASY_CREDIT_CARD_OWNER'),
            'credit_card_number'        => \JText::_('JSHOP_PAYNETEASY_CREDIT_CARD_NUMBER'),
            'credit_card_expire_month'  => \JText::_('JSHOP_PAYNETEASY_CREDIT_CARD_EXPIRE_MONTH'),
            'credit_card_expire_year'   => \JText::_('JSHOP_PAYNETEASY_CREDIT_CARD_EXPIRE_YEAR'),
            'credit_card_cvv2'          => \JText::_('JSHOP_PAYNETEASY_CREDIT_CARD_CVV2')
        );
    }
    
    function getSavePaymentParams(){
        return true;
    }
}
