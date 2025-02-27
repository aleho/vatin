VATIN
=====
[![Latest Stable Version](https://poser.pugx.org/ddeboer/vatin/v/stable.png)](https://packagist.org/packages/ddeboer/vatin)

A small PHP library for validating VAT identification numbers (VATINs).

Installation
------------

This library is available on [Packagist](https://packagist.org/packages/ddeboer/vatin):

```bash
$ composer require ddeboer/vatin
```

If you want to use this library in a Symfony application, you can use the
[VatinBundle](https://github.com/ddeboer/vatin-bundle) instead.

Usage
-----

Validate a VAT number’s format:

```php
use Ddeboer\Vatin\Validator;

$validator = new Validator();
$bool = $validator->isValid('NL123456789B01');
```

Additionally check whether the VAT number is in use, with a call to the [VAT
Information Exchange System (VIES)](https://ec.europa.eu/taxation_customs/vies/faq.html#item_16) SOAP web service:

```php
use Ddeboer\Vatin\Validator;

$validator = new Validator();
$bool = $validator->isValid('NL123456789B01', true);
```
