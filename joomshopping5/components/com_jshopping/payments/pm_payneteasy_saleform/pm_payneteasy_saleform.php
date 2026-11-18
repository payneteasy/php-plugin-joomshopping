<?php

defined('_JEXEC') or die();

require_once __DIR__ . '/../pm_payneteasy_abstract/pm_payneteasy_abstract.php';
require_once __DIR__ . '/payment_aggregate.php';

/**
 * Class pm_pay2pay
 */
class pm_payneteasy_saleform extends pm_payneteasy_abstract
{
    /**
     * {@inheritdoc}
     */
    static protected $aggregateClass = 'PaynetEasy\PaynetEasyApi\SaleformPaymentAggregate';
}
