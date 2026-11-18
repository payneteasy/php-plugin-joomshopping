<?php

namespace PaynetEasy\PaynetEasyApi\Query;

use PaynetEasy\PaynetEasyApi\PaymentData\PaymentTransaction;
use PaynetEasy\PaynetEasyApi\Transport\Response;
use PaynetEasy\PaynetEasyApi\Transport\Request;

interface QueryInterface
{
    /**
     * Create API gateway request from payment transaction data
     *
     * @param       PaymentTransaction      $paymentTransaction     Payment transaction for query
     *
     * @return      Request                                         Request object
     */
    public function createRequest(PaymentTransaction $paymentTransaction): Request;

    /**
     * Process API gateway response and update payment transaction
     *
     * @param       PaymentTransaction      $paymentTransaction     Payment transaction for update
     * @param       Response                $response               API gateway response
     */
    public function processResponse(PaymentTransaction $paymentTransaction, Response $response): Response;
}