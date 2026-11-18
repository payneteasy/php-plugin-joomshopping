<?php

namespace PaynetEasy\PaynetEasyApi;

use Joomla\Component\Jshopping\Site\Table\OrderTable;


class SalePaymentAggregate extends AbstractPaymentAggregate
{
    /**
     * {@inheritdoc}
     */
    protected $initialApiMethod = 'sale';

    /**
     * {@inheritdoc}
     */
    protected function getPaymentTransaction(OrderTable $joomlaOrder, $redirectUrl = null)
    {
        $paynetTransaction = parent::getPaymentTransaction($joomlaOrder, $redirectUrl);

        $this->addCreditCardData($paynetTransaction, $joomlaOrder);

        return $paynetTransaction;
    }
}
