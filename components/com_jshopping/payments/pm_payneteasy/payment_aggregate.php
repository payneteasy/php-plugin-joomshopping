<?php

namespace PaynetEasy\PaynetEasyApi;

use PaynetEasy\PaynetEasyApi\PaymentData\PaymentTransaction;
use PaynetEasy\PaynetEasyApi\PaymentProcessor;
use PaynetEasy\PaynetEasyApi\Transport\CallbackResponse;
use PaynetEasy\PaynetEasyApi\Util\Validator;
use PaynetEasy\PaynetEasyApi\Util\RegionFinder;

use jshopOrder;
use JTable;

class PaymentAggregate
{
    /**
     * Order processor instance.
     * For lazy loading use PaynetProcessorAggregate::getPaymentProcessor()
     *
     * @see PaynetProcessorAggregate::getPaymentProcessor
     *
     * @var \PaynetEasy\PaynetEasyApi\PaymentProcessor
     */
    protected $paymentProcessor;

    /**
     * @var array
     */
    protected $paymentConfig;

    /**
     * @param       array       $paymentConfig          Config for payment method
     */
    public function __construct(array $paymentConfig)
    {
        $this->paymentConfig    = $paymentConfig;
        $this->paymentProcessor = new PaymentProcessor;
    }

    /**
     * Starts order processing.
     * Method executes query to PaynetEasy gateway and returns response from gateway.
     * After that user must be redirected to the Response::getRedirectUrl()
     *
     * @param       jshopOrder      $joomlaOrder        Joomla order
     * @param       string          $returnUrl          Url for final payment processing
     *
     * @return      \PaynetEasy\PaynetEasyApi\Transport\Response        Gateway response object
     */
    public function startSale(jshopOrder $joomlaOrder, $returnUrl)
    {
        $paynetTransaction = $this->getPaynetTransaction($joomlaOrder, $returnUrl);

        try
        {
            $response = $this->paymentProcessor
                ->executeQuery('sale-form', $paynetTransaction);
        } catch (Exception $e) {}
        // finally
        {
            $this->updateOrder($joomlaOrder, $paynetTransaction);
            if (isset($e)) {throw $e;}
        }

        return $response;
    }

    /**
     * Finish order processing.
     * Method checks callnack data and returns object with them.
     * After that order must be updated and saved.
     *
     * @param       jshopOrder      $joomlaOrder        Joomla address
     * @param       array           $callbackData       Callback data
     *
     * @return      \PaynetEasy\PaynetEasyApi\Transport\CallbackResponse       Callback data object
     */
    public function finishSale(jshopOrder $joomlaOrder, array $callbackData)
    {
        $paynetTransaction = $this->getPaynetTransaction($joomlaOrder);

        try
        {
            $callbackResponse = $this->paymentProcessor
                ->processCustomerReturn(new CallbackResponse($callbackData), $paynetTransaction);
        } catch (Exception $e) {}
        // finally
        {
            $this->updateOrder($joomlaOrder, $paynetTransaction);
            if (isset($e)) {throw $e;}
        }

        return $callbackResponse;
    }

    /**
     * Get PaynetEasy payment transaction object by Joomshopping order object
     *
     * @param       jshopOrder      $joomlaOrder        Joomshopping order
     * @param       string          $redirectUrl        Url for final payment processing
     *
     * @return      PaymentTransaction                  PaynetEasy payment transaction
     */
    protected function getPaynetTransaction(jshopOrder $joomlaOrder,$redirectUrl = null)
    {
        $paynetTransaction  = new PaymentTransaction;

        $this->addPaymentData($paynetTransaction, $joomlaOrder);
        $this->addCustomerData($paynetTransaction, $joomlaOrder);
        $this->addBillingAdressData($paynetTransaction, $joomlaOrder);
        $this->addQueryConfigData($paynetTransaction, $redirectUrl);

        if (isset($joomlaOrder->transaction_status))
        {
            $paynetTransaction->setStatus($joomlaOrder->transaction_status);
        }

        return $paynetTransaction;
    }

    /**
     * Add payment data to PaynetEasy payment transaction
     *
     * @param       PaymentTransaction      $paynetTransaction      PaynetEasy payment transaction
     * @param       jshopOrder              $joomlaOrder            Joomshopping order
     */
    protected function addPaymentData(PaymentTransaction $paynetTransaction, jshopOrder $joomlaOrder)
    {
        $paynetPayment = $paynetTransaction->getPayment();

        $paynetPayment
            ->setClientId($joomlaOrder->order_number)
            ->setDescription($this->getPaynetPaymentDescription($joomlaOrder))
            ->setAmount($joomlaOrder->order_total)
            ->setCurrency($joomlaOrder->currency_code_iso)
        ;

        if (isset($joomlaOrder->paynet_order_id))
        {
            $paynetPayment->setPaynetId($joomlaOrder->paynet_order_id);
        }

        if (isset($joomlaOrder->payment_status))
        {
            $paynetPayment->setStatus($joomlaOrder->payment_status);
        }
    }

    /**
     * Add customer data to PaynetEasy payment
     *
     * @param       PaymentTransaction      $paynetTransaction      PaynetEasy payment transaction
     * @param       jshopOrder              $joomlaOrder            Joomshopping order
     */
    protected function addCustomerData(PaymentTransaction $paynetTransaction, jshopOrder $joomlaOrder)
    {
        $paynetTransaction
            ->getPayment()
            ->getCustomer()
            ->setEmail($joomlaOrder->email)
            ->setFirstName($joomlaOrder->f_name)
            ->setLastName($joomlaOrder->l_name)
            ->setIpAddress($joomlaOrder->ip_address)
        ;
    }

    /**
     * Add customer data to PaynetEasy payment
     *
     * @param       PaymentTransaction      $paynetTransaction      PaynetEasy payment transaction
     * @param       jshopOrder              $joomlaOrder            Joomshopping order
     */
    protected function addBillingAdressData(PaymentTransaction $paynetTransaction, jshopOrder $joomlaOrder)
    {
        $country = JTable::getInstance('country', 'jshop');
        $country->load($joomlaOrder->d_country);

        $paynetTransaction
            ->getPayment()
            ->getBillingAddress()
            ->setCountry($country->country_code_2)
            ->setCity($joomlaOrder->city)
            ->setFirstLine($joomlaOrder->street)
            ->setZipCode($joomlaOrder->zip)
            ->setPhone($joomlaOrder->phone)
        ;

        if (RegionFinder::hasStateByName($joomlaOrder->state))
        {
            $paynetTransaction
                ->getPayment()
                ->getBillingAddress()
                ->setState(RegionFinder::getStateCode($joomlaOrder->state))
            ;
        }
    }

    /**
     * Add query config data to PaynetEasy payment transaction
     *
     * @param       PaymentTransaction      $paynetTransaction      PaynetEasy payment transaction
     * @param       string                  $redirectUrl            Url for final payment processing
     */
    protected function addQueryConfigData(PaymentTransaction $paynetTransaction, $redirectUrl = null)
    {
        $paynetTransaction
            ->getQueryConfig()
            ->setEndPoint($this->paymentConfig['end_point'])
            ->setLogin($this->paymentConfig['login'])
            ->setSigningKey($this->paymentConfig['signing_key'])
            ->setGatewayMode($this->paymentConfig['gateway_mode'])
            ->setGatewayUrlSandbox($this->paymentConfig['sandbox_gateway'])
            ->setGatewayUrlProduction($this->paymentConfig['production_gateway'])
        ;

        if (Validator::validateByRule($redirectUrl, Validator::URL, false))
        {
            $paynetTransaction
                ->getQueryConfig()
                ->setRedirectUrl($redirectUrl)
                ->setCallbackUrl($redirectUrl)
            ;
        }
    }

    /**
     * Get PaynetEasy order description
     *
     * @param       jshopOrder      $joomlaOrder        Joomshopping order
     *
     * @return      string
     */
    protected function getPaynetPaymentDescription(jshopOrder $joomlaOrder)
    {
        $store = JTable::getInstance('vendor', 'jshop');
        $store->load($joomlaOrder->vendor_id);

        return  _JSHOP_PAYNETEASY_SHOPPING_IN   . ': ' . $store->shop_name . '; ' .
                _JSHOP_PAYNETEASY_ORDER_ID      . ': ' . $joomlaOrder->order_number;
    }

    /**
     * Updates joomla address by PaynetEasy order data
     *
     * @param       jshopOrder              $joomlaOrder            Joomla order
     * @param       PaymentTransaction      $paynetTransaction      PaynetEasy payment transaction
     */
    protected function updateOrder(jshopOrder $joomlaOrder, PaymentTransaction $paynetTransaction)
    {
        $payment = $paynetTransaction->getPayment();

        $joomlaOrder->paynet_order_id     = $payment->getPaynetId();
        $joomlaOrder->transaction_status  = $paynetTransaction->getStatus();
        $joomlaOrder->payment_status      = $payment->getStatus();
    }
}
