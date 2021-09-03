# Pff2 Facebook conversion Api

## Request common parameter

The basic request sent to the facebook api:
```json
{
   "data": [
      {
         "event_name": "", // based on the event called
         "event_time": , // current timestamp
         "event_source_url": "", // $event_source_url parameter, by default is the current request url
         "action_source": "website",
         "user_data": {
            "client_ip_address": "", // $_SERVER['REMOTE_ADDR']
            "client_user_agent": "" // $_SERVER['HTTP_USER_AGENT']
         },
         "custom_data": {
            // depend on the specific event called
         }
      }
   ],
   "test_event_code": "" //present only if the debug config param is not false
}
```

## Events

The facebook pixel events managed by the module are:
 
####PageView

To send a PageView event create an action in a controller in which you call `sendPageViewEvent()`:
```php 
public function pageViewTrigger() {
    $this->resetViews();
    /** @var Pff2FbConversionApi $fb_conv_api */
    $fb_conv_api = ModuleManager::loadModule('pff2-fb-conversion-api');
    $fb_conv_api->sendPageViewEvent();
}
``` 
Insert a render action in the main layout which refers to the action created (for ex. in the Layout_Controller)

`<?php $this->renderAction('Layout', 'pageViewTrigger', array())?>`

This method does not create a custom data request field.

####CompleteRegistration
`sendCompleteRegistrationEvent($event_source_url, $user_email, $status)`
* `string|null $event_source_url` The browser URL where the event happened. The URL must begin with http:// or https:// and should match the verified domain.
If `null` the parameter value is replaced with the absolute url of the request.
* `string|null $user_email` User email, if specified it has added to the `user_data` request field.
* `string $status` The status of the registration event, as a string. Example: 'registered'.

example of custom data created:

```json
"custom_data": {
    "status": "registered"
}
```
####InitiateCheckout

`sendInitiateCheckoutEvent($event_source_url, $user_email, $value, $currency = "EUR")`
 * `string|null $event_source_url` The browser URL where the event happened. The URL must begin with http:// or https:// and should match the verified domain. If `null` the parameter value is replaced with the absolute url of the request.
 * `string|null` User email, if specified it has added to the `user_data` request field.
 * `float $value` The total of order when the checkout process begins
 * `string` $currency The currency for the $value specified. Currency must be a valid ISO 4217 three digit currency code. Example: 'EUR'.

example of custom data created:

```json
"custom_data": {
    "currency": "EUR",
    "value": 100
}
```

####ViewContent

`sendViewContentEvent($event_source_url, $user_email, $content_name, $content_ids, $value, $currency = "EUR")`

 * `string|null $event_source_url` The browser URL where the event happened. The URL must begin with http:// or https:// and should match the verified domain. If `null` the parameter value is replaced with the absolute url of the request.
 * `string|null` User email, if specified it has added to the `user_data` request field.
 * `string $content_name` The name of the page or product associated with the event.
 * `[string] $content_ids` The content IDs associated with the event in the form ['ABC','123']
 * `float $value` A numeric value associated with this event.
 * `string $currency` The currency for the $value specified. Currency must be a valid ISO 4217 three digit currency code. Example: 'EUR'.

example of custom data created:

```json
"custom_data": {
    "currency": "EUR",
    "value": 60.00,
    "content_type": "product",
    "content_name": "Name of the product",
    "content_ids": ["123"]
}
```

####AddToCart

`sendAddToCartEvent($event_source_url, $user_email, $contents, $value, $currency = "EUR")`

* `string|null $event_source_url` The browser URL where the event happened. The URL must begin with http:// or https:// and should match the verified domain. If `null` the parameter value is replaced with the absolute url of the request.
* `string|null` User email, if specified it has added to the `user_data` request field.
* `[array] $contents` array of associative arrays `[array("id" => <product_id>, "quantity" => <product_qnt>)]`
* `float $value` Total cost of the item added (price * quantity).
* `string $currency` The currency for the $value specified. Currency must be a valid ISO 4217 three digit currency code. Example: 'EUR'.

example of custom data created: 
```json
"custom_data": {
    "value": 100.2,
    "currency": "EUR",
    "contents": [
       {id: "123", quantity: 1},
       {id: "234", quantity: 3}
    ],
    "content_type": "product"
}
```

####Purchase

`sendPurchaseEvent($event_source_url, $user_email, $content_ids, $order_value, $currency = "EUR")`

* `string|null $event_source_url` The browser URL where the event happened. The URL must begin with http:// or https:// and should match the verified domain. If `null` the parameter value is replaced with the absolute url of the request.
* `string|null` User email, if specified it has added to the `user_data` request field.
* `[string] $content_ids` The content IDs associated with the event in the form `['ABC','123']`.
* `float $order_value` Total order amount
* `string $currency` The currency for the $order_value specified. Currency must be a valid ISO 4217 three digit currency code. Example: 'EUR'.

example of custom data created:

```json
"custom_data": {
    "currency": "EUR",
    "value": 123.45,
    "content_type": "product",
    "content_ids": ["123","234"]
}
```

