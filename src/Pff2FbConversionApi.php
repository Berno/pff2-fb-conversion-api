<?php

namespace pff\modules;

use pff\Abs\AModule;
use pff\Exception\ModuleException;
use pff\Iface\IConfigurableModule;


class Pff2FbConversionApi extends AModule implements IConfigurableModule {

    private $access_token;
    private $api_version;
    private $pixel_id;
    private $debug;
    private $test_code;

    public function __construct($confFile = 'pff2-fb-conversion-api/module.conf.local.yaml') {
        try {
            $this->loadConfig($confFile);
        } catch (\Exception $e) {
            throw new ModuleException($e->getMessage() . ' ' . $e->getFile(), $e->getCode());
        }
    }

    /**
     * @param array $parsedConfig
     * @throws \pff\Exception\ModuleException
     */
    public function loadConfig($parsedConfig) {
        $conf = $this->readConfig($parsedConfig);
        if (
            empty($conf['moduleConf']['accessToken'])
            || empty($conf['moduleConf']['apiVersion'])
            || empty($conf['moduleConf']['pixelId']))
        {
            throw new ModuleException("Missing configuration parameters", 400);
        }
        $this->access_token = $conf['moduleConf']['accessToken'];
        $this->api_version  = $conf['moduleConf']['apiVersion'];
        $this->pixel_id     = $conf['moduleConf']['pixelId'];
        if (
            $conf['moduleConf']['debug'] === false
            || ($conf['moduleConf']['debug'] !== 'payload' && $conf['moduleConf']['debug'] !== 'request')
        ) {
            $this->debug = false;
        } else {
            $this->debug = $conf['moduleConf']['debug'];
        }
        if ($this->debug !== false) {
            $this->test_code = $conf['moduleConf']['testCode'];
        }
    }

    public function showConfigVars() {
        echo '<b>Access token:</b> ' . $this->access_token;
        echo '<br /><br />';
        echo '<b>Api version:</b> ' . $this->api_version;
        echo '<br /><br />';
        echo '<b>Pixel ID:</b> ' . $this->pixel_id;
        echo '<br /><br />';
        echo '<b>Debug:</b> ' . ($this->debug === false ? 'false' : $this->debug);
        echo '<br /><br />';
        echo '<b>Test code:</b> ' . $this->test_code;
        echo '<br /><br />';
    }

    public function sendPageViewEvent() {
        $payload = $this->_createDataPayload("PageView", null, array(), null);

        if ($this->debug === 'payload') {
            $this->_testPayload($payload);
        } else {
            $this->_sendRequest($payload);
        }
    }

    /**
     * @param string|null $event_source_url The browser URL where the event happened. The URL must begin with http:// or https:// and should match the verified domain.
     * @param string|null $user_email User email if logged.
     * @param [string] $content_ids The content IDs associated with the event in the form ['ABC','123']
     * @param float $order_value Total order amount
     * @param string $currency The currency for the $order_value specified. Currency must be a valid ISO 4217 three digit currency code. Example: 'EUR'.
     */
    public function sendPurchaseEvent(
        $event_source_url, $user_email, $content_ids, $order_value, $currency = "EUR")
    {
        $custom_data = array(
            "currency" => $currency,
            "value" => number_format($order_value, 2, '.', ''),
            "content_type" => "product",
            "content_ids" => $content_ids
        );
        $payload = $this->_createDataPayload("Purchase", $event_source_url, $custom_data, $user_email);

        if ($this->debug === 'payload') {
            $this->_testPayload($payload);
        } else {
            $this->_sendRequest($payload);
        }
    }

    /**
     * @param string|null $event_source_url The browser URL where the event happened. The URL must begin with http:// or https:// and should match the verified domain.
     * @param string|null $user_email User email if logged.
     * @param string $status The status of the registration event, as a string. Example: 'registered'.
     */
    public function sendCompleteRegistrationEvent($event_source_url, $user_email, $status) {
        $custom_data = array(
            "status" => $status
        );
        $payload = $this->_createDataPayload("CompleteRegistration", $event_source_url, $custom_data, $user_email);

        if ($this->debug === 'payload') {
            $this->_testPayload($payload);
        } else {
            $this->_sendRequest($payload);
        }
    }

    /**
     * @param string|null $event_source_url The browser URL where the event happened. The URL must begin with http:// or https:// and should match the verified domain.
     * @param string|null $user_email User email if logged.
     * @param float $value The total of order when the checkout process begins
     * @param string $currency The currency for the $value specified. Currency must be a valid ISO 4217 three digit currency code. Example: 'EUR'.
     */
    public function sendInitiateCheckoutEvent($event_source_url, $user_email, $value, $currency = "EUR") {
        $custom_data = array(
            "currency" => $currency,
            "value" => number_format($value, 2, '.', '')
        );
        $payload = $this->_createDataPayload("InitiateCheckout", $event_source_url, $custom_data, $user_email);

        if ($this->debug === 'payload') {
            $this->_testPayload($payload);
        } else {
            $this->_sendRequest($payload);
        }
    }

    /**
     * @param string|null $event_source_url The browser URL where the event happened. The URL must begin with http:// or https:// and should match the verified domain.
     * @param string|null $user_email User email if logged.
     * @param string $content_name The name of the page or product associated with the event.
     * @param [string] $content_ids The content IDs associated with the event in the form ['ABC','123']
     * @param float $value A numeric value associated with this event.
     * @param string $currency The currency for the $value specified. Currency must be a valid ISO 4217 three digit currency code. Example: 'EUR'.
     */
    public function sendViewContentEvent(
        $event_source_url, $user_email, $content_name, $content_ids, $value, $currency = "EUR")
    {
        $custom_data = array(
            "currency" => $currency,
            "value" => number_format($value, 2, '.', ''),
            "content_type" => "product",
            "content_name" => $content_name,
            "content_ids" => $content_ids
        );
        $payload = $this->_createDataPayload("ViewContent", $event_source_url, $custom_data, $user_email);

        if ($this->debug === 'payload') {
            $this->_testPayload($payload);
        } else {
            $this->_sendRequest($payload);
        }
    }

    /**
     * @param string|null $event_source_url The browser URL where the event happened. The URL must begin with http:// or https:// and should match the verified domain.
     * @param string|null $user_email User email if logged.
     * @param [array] $contents, array of associative arrays [array("id" => <product_id>, "quantity" => <product_qnt>)]
     * @param float $value Total cost of the item added (price * quantity).
     * @param string $currency The currency for the $value specified. Currency must be a valid ISO 4217 three digit currency code. Example: 'EUR'.
     */
    public function sendAddToCartEvent(
        $event_source_url, $user_email, $contents, $value, $currency = "EUR")
    {
        $custom_data = array(
            "currency" => $currency,
            "value" => number_format($value, 2, '.', ''),
            "content_type" => "product",
            "contents" => $contents
        );
        $payload = $this->_createDataPayload("AddToCart", $event_source_url, $custom_data, $user_email);

        if ($this->debug === 'payload') {
            $this->_testPayload($payload);
        } else {
            $this->_sendRequest($payload);
        }
    }

    private function _createDataPayload($event, $event_source_url, $custom_data, $user_email = null) {
        $source_url = $event_source_url
            ? $event_source_url
            : 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

        $data = array(
            "event_name" => $event,
            "event_time" => time(),
            "event_source_url" => $source_url ,
            "action_source" => "website"
        );
        $user_data = array(
            "client_user_agent" => $_SERVER['HTTP_USER_AGENT'],
            "client_ip_address" => $_SERVER['REMOTE_ADDR']
        );
        if (!empty($_COOKIE['_fbp'])) {
            $user_data["fbp"] = $_COOKIE['_fbp'];
        }
        if (!empty($_COOKIE['_fbc'])) {
            $user_data["fbc"] = $_COOKIE['_fbc'];
        }
        if ($user_email) {
            $user_data["em"] = hash('sha256', $user_email);
        }
        $data["user_data"] = $user_data;
        if (!empty($custom_data)) {
            $data["custom_data"] = $custom_data;
        }
        $data_array[] = $data;
        $payload = array("data" => $data_array);
        if ($this->debug) {
            $payload["test_event_code"] = $this->test_code;
        }

        return $payload;
    }

    private function _testPayload($payload) {
        echo '<pre>'.json_encode($payload, JSON_PRETTY_PRINT).'</pre>';
    }

    private function _sendRequest($payload) {
        $url = "https://graph.facebook.com/".$this->api_version."/".$this->pixel_id."/events?access_token=".$this->access_token;
        $ch = curl_init($url);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result);

        if ($this->debug === 'request') {
            echo '<pre>'.print_r($response, true).'</pre>';
        }
    }
}
