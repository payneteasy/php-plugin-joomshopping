# Plugin for Joomla 3.x and JoomShopping 4.x for pay by PaynetEasy

## Доступная функциональность

Данный  плагин позволяет производить оплату с помощью [merchant PaynetEasy API](http://wiki.payneteasy.com/index.php/PnE:Merchant_API). На текущий момент реализованы следующие платежные методы:
- [x] [Sale Transactions](http://wiki.payneteasy.com/index.php/PnE:Sale_Transactions)
- [ ] [Preauth/Capture Transactions](http://wiki.payneteasy.com/index.php/PnE:Preauth/Capture_Transactions)
- [ ] [Transfer Transactions](http://wiki.payneteasy.com/index.php/PnE:Transfer_Transactions)
- [ ] [Return Transactions](http://wiki.payneteasy.com/index.php/PnE:Return_Transactions)
- [ ] [Recurrent Transactions](http://wiki.payneteasy.com/index.php/PnE:Recurrent_Transactions)
- [x] [Payment Form Integration](http://wiki.payneteasy.com/index.php/PnE:Payment_Form_integration)
- [ ] [Buy Now Button integration](http://wiki.payneteasy.com/index.php/PnE:Buy_Now_Button_integration)
- [ ] [eCheck integration](http://wiki.payneteasy.com/index.php/PnE:eCheck_integration)
- [ ] [Western Union Integration](http://wiki.payneteasy.com/index.php/PnE:Western_Union_Integration)
- [ ] [Bitcoin Integration](http://wiki.payneteasy.com/index.php/PnE:Bitcoin_integration)
- [ ] [Loan Integration](http://wiki.payneteasy.com/index.php/PnE:Loan_integration)
- [ ] [Qiwi Integration](http://wiki.payneteasy.com/index.php/PnE:Qiwi_integration)
- [ ] [Merchant Callbacks](http://wiki.payneteasy.com/index.php/PnE:Merchant_Callbacks)

## Системные требования

* PHP 5.3 - 5.5
* [Расширение curl](http://php.net/manual/en/book.curl.php)
* [Joomla](http://www.joomla.org/download.html) 3.x (плагин тестировался с версией 3.1)
* [JoomShopping](http://joomshopping.pro/download/component.html) 4.x (плагин тестировался с версией 4.3)

## <a name="get_package"></a> Получение пакета с плагином

### Самостоятельная сборка пакета
1. [Установите composer](http://getcomposer.org/doc/00-intro.md), если его еще нет
2. Клонируйте репозиторий с плагином: `composer create-project payneteasy/php-plugin-joomshopping --stability=dev --prefer-dist`
3. Перейдите в папку плагина: `cd php-plugin-joomshopping`
4. Упакуйте плагин в архив: `composer archive --format=zip`

## Установка, настройка, удаление плагина

* [Установка плагина](doc/00-installation.md)
* [Настройка плагина](doc/01-configuration.md)
* [Удаление плагина](doc/02-uninstalling.md)
## Joomla 5 / JoomShopping 5

The `joomshopping5` folder contains the version for Joomla 5 and JoomShopping 5
(file extension `jshopping_payneteasy`). Install the `jshopping_payneteasy-*.zip`
archive from the release in Joomla: System → Install → Extensions. Version 2.0.0
installs over 1.0.2 as an update; payment method settings are kept.
