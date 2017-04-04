# IssetBV/PushNotification

[![Build Status](https://travis-ci.org/Isset/pushnotification.svg?branch=master)](https://travis-ci.org/Isset/pushnotification)
[![Coverage Status](https://coveralls.io/repos/github/Isset/pushnotification/badge.svg?branch=master)](https://coveralls.io/github/Isset/pushnotification?branch=master)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)


PushNotification is a push message abstraction which allows you to easily send push notifications to mobile devices. Currently we support Apple, Android and Windows (experimental)
 
## Why Yet Another Notification Package

* There are many packages out there that support mobile push notification, but most do not support an unified API.
* Some implementations were lacking flexible logging capabilities
* Most implementations did not support batches/queues
* Most implementations did not take into account that when sending batches to Apple, if one of the messages fails, the entire batch is dropped. This means that if you have a queue of 50 messages and the 3rd message fails, 47 messages will be dropped. This frustrated us to no end, so we build a simple yet effective fallback mechanism that will restart the batch from the first message after the failed one.

## Goals

* Have a generic API for sending push notifications regardless of device.
* Have consistent output.
* Integrate well with other packages/frameworks.
* Have a flexible logging mechanism.
* Have build in queue mechanism.
* Have build in queue resume when dealing with batches (specifically sent to Apple).

## Prerequisites

* PHP 7.0+
* [cURL](https://secure.php.net/manual/en/book.curl.php)


## Installation

Through Composer:

```bash
  composer require issetbv/push-notification
```

## Integrations

At the moment we only support Symfony, since that's what we use, but feel free to create your own, and send us a PR with a link to your integration

* Symfony integration: https://github.com/Isset/pushnotification-bundle

## Supported Devices

* Android
* Apple
* Windows (experimental)

## Documentation

The bulk of the documentation is stored in the docs/index.md file in this package:

[Read the Documentation](docs/index.md)

If you just want to send a message without using the `NotificationCenter` here are some simple `TL;DR` examples

### Simple Android Example

To send a push notification to an Android device we first need to setup a connection. A connection needs a `name`, `api url` and your `api key`. Lastly we need the device token of the device we want to send the message to.

```php
    use IssetBV\PushNotification\Type\Android\AndroidConnection;
    use IssetBV\PushNotification\Type\Android\Message\AndroidMessage;
    
    $connection = new AndroidConnection(
        $name,    // 'android'
        $api_url, // 'https://fcm.googleapis.com/fcm/send'
        $api_key, // 'super-secret-api-key
    );
    
    $message = new AndroidMessage('my-device-token');
    $message->addToPayload('notification', ['title' => 'Test android']);
    
    $response = $connection->sendAndReceive($message);
    
    echo $response->isSuccess(); // should be true
```

### Simple Apple Example

To send a push notification to an Apple device we first need to setup a connection. A connection needs a `name`, `api url`, location of the `pem file` and the passphrase of the `pem` file (if the `pem` file has one). Lastly we need the device identifier of the device we want to send the message to.  

```php
    use IssetBV\PushNotification\Type\Apple\AppleConnection;
    use IssetBV\PushNotification\Type\Apple\Message\AppleMessageAps;
    
    $connection = new AppleConnection(
        $name,      // 'apple'
        $api_url,   // 'ssl://gateway.push.apple.com:2195'  
        $pemFile,   // __DIR__ '/pemfile.pem'
        $passPhrase // 'super-secret-passphrase'
    );
    
    $appleMessage = new AppleMessageAps('my-device-identifier');
    // see notes below as to why we don't use ->addToPayload()
    $appleMessage->getAps()->setAlert('Test apple');    
    
    $response = $connection->sendAndReceive($appleMessage);
    
    echo $response->isSuccess(); // should be true
```

When sending messages to Apple, the payload can have a specific key named `aps` where we can specify that it should show a notification on the screen. For more information, visit the [official Apple documentation](https://developer.apple.com/library/content/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/CreatingtheNotificationPayload.html) regarding the message format.

### Simple Windows Example

To send a push notification to a Windows device we first need to setup a connection. A connection only needs a `name`. Differently than with Android or Apple, the Windows message needs the uri of your specific device to deliver the message.

```php
    use IssetBV\PushNotification\Type\Windows\Message\WindowsMessage;
    use IssetBV\PushNotification\Type\Windows\WindowsConnection;
    
    $connection = new WindowsConnection('windows');
    
    $windowsMessage = new WindowsMessage('https://cloud.notify.windows.com/?token=AQE%bU%2fSjZOCvRjjpILow%3d%3d');
    $windowsMessage->addToPayload('wp:Text1', 'Test Windows');
    
    $response = $connection->sendAndReceive($windowsMessage);
    
    echo $response->isSuccess(); // should be true
```
