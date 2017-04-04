<?php

namespace IssetBV\PushNotification\Tests\Type\Android;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use IssetBV\PushNotification\Core\Connection\ConnectionResponseImpl;
use IssetBV\PushNotification\Type\Android\AndroidConnection;
use IssetBV\PushNotification\Type\Android\Message\AndroidMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class AndroidConnectionTest extends TestCase
{
    /** @test */
    public function it_should_build_correctly()
    {
        $sut = new AndroidConnection('android', 'https://isset.nl', 'apikey', 30, false, false);

        TestCase::assertSame('android', $sut->getType());
        TestCase::assertFalse($sut->isDefault());
        TestCase::assertInstanceOf(NullLogger::class, $sut->getLogger());
    }

    /** @test */
    public function it_should_send_a_message_successfully()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode($this->getSuccessFullResponseData())),
        ]);

        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $sut = new AndroidConnection('android', 'https://isset.nl', 'apikey', 30, false, false, $stack);
        $response = $sut->sendAndReceive(new AndroidMessage('identifier'));

        TestCase::assertInstanceOf(ConnectionResponseImpl::class, $response);
        TestCase::assertTrue($response->isSuccess());
        TestCase::assertSame($this->getSuccessFullResponseData(), $response->getResponse());

        TestCase::assertCount(1, $container);

            /** @var Request $lastRequest */
            $lastRequest = current($container)['request'];

        TestCase::assertSame('POST', $lastRequest->getMethod());
        TestCase::assertSame('https://isset.nl', $lastRequest->getUri()->__toString());
        TestCase::assertSame('application/json', $lastRequest->getHeaderLine('Content-Type'));
        TestCase::assertSame('key=apikey', $lastRequest->getHeaderLine('Authorization'));
        TestCase::assertArraySubset(
                ['to' => 'identifier'],
                json_decode($lastRequest->getBody()->getContents(), true)
            );
    }

    /** @test */
    public function it_should_mark_a_message_as_dry_run()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode($this->getSuccessFullResponseData())),
        ]);

        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $sut = new AndroidConnection('android', 'https://isset.nl', 'apikey', 30, true, false, $stack);
        $response = $sut->sendAndReceive(new AndroidMessage('identifier'));

        TestCase::assertInstanceOf(ConnectionResponseImpl::class, $response);
        TestCase::assertTrue($response->isSuccess());
        TestCase::assertSame($this->getSuccessFullResponseData(), $response->getResponse());

        TestCase::assertCount(1, $container);

            /** @var Request $lastRequest */
            $lastRequest = $container[0]['request'];

        TestCase::assertSame('POST', $lastRequest->getMethod());
        TestCase::assertSame('https://isset.nl', $lastRequest->getUri()->__toString());
        TestCase::assertSame('application/json', $lastRequest->getHeaderLine('Content-Type'));
        TestCase::assertSame('key=apikey', $lastRequest->getHeaderLine('Authorization'));
        TestCase::assertArraySubset(
                ['to' => 'identifier', 'dry_run' => true],
                json_decode($lastRequest->getBody()->getContents(), true)
            );
    }

    /** @test */
    public function it_should_trim_extraneous_slashes_from_the_api_url()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode($this->getSuccessFullResponseData())),
        ]);

        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $sut = new AndroidConnection('android', 'https://isset.nl///////', 'apikey', 30, false, false, $stack);
        $response = $sut->sendAndReceive(new AndroidMessage('identifier'));

        TestCase::assertInstanceOf(ConnectionResponseImpl::class, $response);
        TestCase::assertTrue($response->isSuccess());
        TestCase::assertSame($this->getSuccessFullResponseData(), $response->getResponse());

        TestCase::assertCount(1, $container);

            /** @var Request $lastRequest */
            $lastRequest = $container[0]['request'];

        TestCase::assertSame('POST', $lastRequest->getMethod());
        TestCase::assertSame('https://isset.nl', $lastRequest->getUri()->__toString());
        TestCase::assertSame('application/json', $lastRequest->getHeaderLine('Content-Type'));
        TestCase::assertSame('key=apikey', $lastRequest->getHeaderLine('Authorization'));
        TestCase::assertArraySubset(
                ['to' => 'identifier'],
                json_decode($lastRequest->getBody()->getContents(), true)
            );
    }

    /** @test */
    public function it_should_send_a_message_unsuccessfully()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode($this->getUnSuccessFullResponseData())),
        ]);

        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $sut = new AndroidConnection('android', 'https://isset.nl', 'apikey', 30, false, false, $stack);
        $response = $sut->sendAndReceive(new AndroidMessage('identifier'));

        TestCase::assertInstanceOf(ConnectionResponseImpl::class, $response);
        TestCase::assertFalse($response->isSuccess());
        TestCase::assertSame($this->getUnSuccessFullResponseData(), $response->getResponse());

        TestCase::assertCount(1, $container);

            /** @var Request $lastRequest */
            $lastRequest = $container[0]['request'];

        TestCase::assertSame('POST', $lastRequest->getMethod());
        TestCase::assertSame('https://isset.nl', $lastRequest->getUri()->__toString());
        TestCase::assertSame('application/json', $lastRequest->getHeaderLine('Content-Type'));
        TestCase::assertSame('key=apikey', $lastRequest->getHeaderLine('Authorization'));
        TestCase::assertArraySubset(
                ['to' => 'identifier'],
                json_decode($lastRequest->getBody()->getContents(), true)
            );
    }

    /** @test */
    public function it_should_handle_failed_connection_gracefully()
    {
        $mock = new MockHandler([
            new Response(500, [], 'An error occurred'),
        ]);

        $stack = HandlerStack::create($mock);

        $sut = new AndroidConnection('android', 'https://isset.nl', 'apikey', 30, false, false, $stack);
        $response = $sut->sendAndReceive(new AndroidMessage('identifier'));

        TestCase::assertInstanceOf(ConnectionResponseImpl::class, $response);
        TestCase::assertFalse($response->isSuccess());
        TestCase::assertSame("Server error: `POST https://isset.nl` resulted in a `500 Internal Server Error` response:
An error occurred\n", $response->getResponse());
    }

    /** @test */
    public function it_should_send_a_message_without_waiting_for_response()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode($this->getSuccessFullResponseData())),
        ]);

        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $sut = new AndroidConnection('android', 'https://isset.nl', 'apikey', 30, false, false, $stack);
        $sut->send(new AndroidMessage('identifier'));

        TestCase::assertCount(1, $container);

        /** @var Request $lastRequest */
        $lastRequest = current($container)['request'];

        TestCase::assertSame('POST', $lastRequest->getMethod());
        TestCase::assertSame('https://isset.nl', $lastRequest->getUri()->__toString());
        TestCase::assertSame('application/json', $lastRequest->getHeaderLine('Content-Type'));
        TestCase::assertSame('key=apikey', $lastRequest->getHeaderLine('Authorization'));
        TestCase::assertArraySubset(
            ['to' => 'identifier'],
            json_decode($lastRequest->getBody()->getContents(), true)
        );
    }

    public function getSuccessFullResponseData()
    {
        return [
      'multicast_id' => 1283745393084616026,
      'success' => 1,
      'failure' => 0,
      'canonical_ids' => 0,
      'results' => [
        [
          'message_id' => '0:1490627981758525%c9a34f42c9a34f42',
        ],
      ],
    ];
    }

    public function getUnSuccessFullResponseData()
    {
        return [
            'multicast_id' => 1283745393084616027,
            'success' => 0,
            'failure' => 1,
            'canonical_ids' => 0,
            'results' => [
                [
                    'message_id' => '0:1490627981758525%c9a34f42c9a34f42',
                ],
            ],
        ];
    }
}
