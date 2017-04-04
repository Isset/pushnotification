<?php

namespace IssetBV\PushNotification\Tests\Type\Windows;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use IssetBV\PushNotification\Core\Connection\ConnectionResponseImpl;
use IssetBV\PushNotification\Type\Windows\Message\WindowsMessage;
use IssetBV\PushNotification\Type\Windows\WindowsConnection;
use PHPUnit\Framework\TestCase;

class WindowsConnectionTest extends TestCase
{
    /** @test */
    public function it_should_build_correctly()
    {
        $sut = new WindowsConnection('windows', true);

        TestCase::assertSame('windows', $sut->getType());
        TestCase::assertTrue($sut->isDefault());
    }

    /** @test */
    public function it_should_send_a_message_successfully()
    {
        $mock = new MockHandler([
            new Response(200, [], '<xml>Message Sent</xml>'),
        ]);

        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $message = new WindowsMessage('identifier');
        $message->addToPayload('wp:Text1', 'some text');

        $sut = new WindowsConnection('windows', true, $stack);
        $response = $sut->sendAndReceive($message);

        TestCase::assertInstanceOf(ConnectionResponseImpl::class, $response);
        TestCase::assertTrue($response->isSuccess());
        TestCase::assertSame('<xml>Message Sent</xml>', $response->getResponse());

        TestCase::assertCount(1, $container);

        /** @var Request $lastRequest */
        $lastRequest = current($container)['request'];

        TestCase::assertSame('POST', $lastRequest->getMethod());
        TestCase::assertSame('identifier', $lastRequest->getUri()->__toString());
        TestCase::assertSame('text/xml', $lastRequest->getHeaderLine('Content-Type'));
        TestCase::assertSame('toast', $lastRequest->getHeaderLine('X-WindowsPhone-Target'));
        TestCase::assertSame('2', $lastRequest->getHeaderLine('X-NotificationClass'));
        TestCase::assertSame(
            '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
            '<wp:Notification xmlns:wp="WPNotification"><wp:Toast><wp:Text1>some text</wp:Text1></wp:Toast></wp:Notification>' . "\n",
            $lastRequest->getBody()->getContents()
        );
    }

    /** @test */
    public function it_should_send_a_message_unsuccessfully()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_should_handle_failed_connection_gracefully()
    {
        $mock = new MockHandler([
            new Response(500, [], 'An error occurred'),
        ]);

        $stack = HandlerStack::create($mock);

        $message = new WindowsMessage('identifier');
        $message->addToPayload('wp:Text1', 'some text');

        $sut = new WindowsConnection('windows', true, $stack);
        $response = $sut->sendAndReceive($message);

        TestCase::assertInstanceOf(ConnectionResponseImpl::class, $response);
        TestCase::assertFalse($response->isSuccess());
        TestCase::assertSame(
            "Server error: `POST identifier` resulted in a `500 Internal Server Error` response:\nAn error occurred\n",
            $response->getResponse());
    }

    /** @test */
    public function it_should_send_a_message_without_waiting_for_response()
    {
        $mock = new MockHandler([
            new Response(200, [], '<xml>Message Sent</xml>'),
        ]);

        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $message = new WindowsMessage('identifier');
        $message->addToPayload('wp:Text1', 'some text');

        $sut = new WindowsConnection('windows', true, $stack);
        $sut->send($message);

        TestCase::assertCount(1, $container);

        /** @var Request $lastRequest */
        $lastRequest = current($container)['request'];

        TestCase::assertSame('POST', $lastRequest->getMethod());
        TestCase::assertSame('identifier', $lastRequest->getUri()->__toString());
        TestCase::assertSame('text/xml', $lastRequest->getHeaderLine('Content-Type'));
        TestCase::assertSame('toast', $lastRequest->getHeaderLine('X-WindowsPhone-Target'));
        TestCase::assertSame('2', $lastRequest->getHeaderLine('X-NotificationClass'));
        TestCase::assertSame(
            '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
            '<wp:Notification xmlns:wp="WPNotification"><wp:Toast><wp:Text1>some text</wp:Text1></wp:Toast></wp:Notification>' . "\n",
            $lastRequest->getBody()->getContents()
        );
    }
}
