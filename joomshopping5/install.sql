INSERT INTO `#__jshopping_payment_method`
SET
	`payment_code`          = 'PaynetEasy sale form',
	`payment_class`         = 'pm_payneteasy_saleform',
	`payment_publish`       = 0,
	`payment_ordering`      = 0,
	`payment_params`        = '',
	`payment_type`          = 2,
	`price`                 = 0.00,
	`price_type`            = 1,
	`tax_id`                = -1,
	`show_descr_in_email`   = 0,
	`name_en-GB`            = 'PaynetEasy sale form',
	`name_de-DE`            = 'PaynetEasy sale form',
	`order_description`     = '',
	`description_en-GB`     = '',
	`description_de-DE`     = ''
;

INSERT INTO `#__jshopping_payment_method`
SET
	`payment_code`          = 'PaynetEasy sale',
	`payment_class`         = 'pm_payneteasy_sale',
	`payment_publish`       = 0,
	`payment_ordering`      = 0,
	`payment_params`        = '',
	`payment_type`          = 2,
	`price`                 = 0.00,
	`price_type`            = 1,
	`tax_id`                = -1,
	`show_descr_in_email`   = 0,
	`name_en-GB`            = 'PaynetEasy sale',
	`name_de-DE`            = 'PaynetEasy sale',
	`order_description`     = '',
	`description_en-GB`     = '',
	`description_de-DE`     = ''
;

CREATE TABLE IF NOT EXISTS `#__jshopping_payment_method_payneteasy`
(
    `client_id`             VARCHAR(30) PRIMARY KEY,
    `paynet_id`             VARCHAR(30),
    `payment_status`        VARCHAR(15),
    `transaction_status`    VARCHAR(15),
    INDEX(`paynet_id`)
);