DELETE FROM `#__jshopping_payment_method` WHERE `payment_class`         = 'pm_payneteasy_saleform';

DELETE FROM `#__jshopping_payment_method` WHERE `payment_class`         = 'pm_payneteasy_sale';

DROP TABLE `#__jshopping_payment_method_payneteasy`;
