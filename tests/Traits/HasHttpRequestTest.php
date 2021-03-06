<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 7/27/2018
 * Time: 3:56 PM
 */

namespace JimChen\MobSms\Tests\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use JimChen\MobSms\Tests\TestCase;
use JimChen\MobSms\Traits\HasHttpRequest;
use Psr\Http\Message\ResponseInterface;

class HasHttpRequestTest extends TestCase
{
    public function testRequest()
    {
        $object = \Mockery::mock(DummyClassForHasHttpRequestTrait::class)
            ->shouldAllowMockingProtectedMethods();
        $mockBaseOptions = ['base_uri' => 'https://mock-base-options'];
        $mockResponse = \Mockery::mock(ResponseInterface::class);
        $mockHttpClient = \Mockery::mock(Client::class);
        $object->shouldReceive('getHttpClient')
            ->with($mockBaseOptions)
            ->andReturn($mockHttpClient)
            ->once();
        $object->shouldReceive('getBaseOptions')->andReturn($mockBaseOptions);
        $object->shouldReceive('unwrapResponse')->with($mockResponse)->andReturn('unwrapped-api-result');
        $options = ['form_params' => ['foo' => 'bar']];
        $mockHttpClient->shouldReceive('get')->with('mock-endpoint', $options)->andReturn($mockResponse)->once();
        $object->shouldReceive('request')->withAnyArgs()->passthru();
        $this->assertSame('unwrapped-api-result', $object->request('get', 'mock-endpoint', $options));
    }

    public function testGet()
    {
        $object = \Mockery::mock(DummyClassForHasHttpRequestTrait::class)
            ->shouldAllowMockingProtectedMethods();
        $object->shouldReceive('request')->with('get', 'mock-endpoint', [
            'headers' => ['Content-Type' => 'Mock-Content-Type'],
            'query' => ['foo' => 'bar'],
        ])->andReturn('mock-result')->once();
        $object->shouldReceive('get')->withAnyArgs()->passthru();
        $response = $object->get('mock-endpoint', ['foo' => 'bar'], ['Content-Type' => 'Mock-Content-Type']);
        $this->assertSame('mock-result', $response);
    }

    public function testPost()
    {
        $object = \Mockery::mock(DummyClassForHasHttpRequestTrait::class)
            ->shouldAllowMockingProtectedMethods();
        $object->shouldReceive('request')->with('post', 'mock-endpoint', [
            'headers' => ['Content-Type' => 'Mock-Content-Type'],
            'form_params' => ['foo' => 'bar'],
        ])->andReturn('mock-result')->once();
        $object->shouldReceive('post')->withAnyArgs()->passthru();
        $response = $object->post('mock-endpoint', ['foo' => 'bar'], ['Content-Type' => 'Mock-Content-Type']);
        $this->assertSame('mock-result', $response);
    }

    public function testGetBaseOptions()
    {
        $object = \Mockery::mock(DummyClassForHasHttpRequestTrait::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $object->shouldReceive('getBaseOptions')->withAnyArgs()->passthru();
        $this->assertSame('http://mock-uri', $object->getBaseOptions()['base_uri']);
        $this->assertSame(30.0, $object->getBaseOptions()['timeout']);
    }

    public function testUnwrapResponseWithJsonResponse()
    {
        $object = \Mockery::mock(DummyClassForHasHttpRequestTrait::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $object->shouldReceive('unwrapResponse')->withAnyArgs()->passthru();
        $body = ['foo' => 'bar'];
        $response = new Response(200, ['content-type' => 'application/json'], json_encode($body));
        $this->assertSame($body, $object->unwrapResponse($response));
    }

    public function testUnwrapResponseWithXMLResponse()
    {
        $object = \Mockery::mock(DummyClassForHasHttpRequestTrait::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $object->shouldReceive('unwrapResponse')->withAnyArgs()->passthru();
        $body = '<xml>
                    <foo>hello</foo>
                    <bar>world</bar>
                </xml>';
        $response = new Response(200, ['content-type' => 'application/xml'], $body);
        $this->assertSame(['foo' => 'hello', 'bar' => 'world'], $object->unwrapResponse($response));
    }

    public function testUnwrapResponseWithUnsupportedResponse()
    {
        $object = \Mockery::mock(DummyClassForHasHttpRequestTrait::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $object->shouldReceive('unwrapResponse')->withAnyArgs()->passthru();
        $body = 'something here.';
        $response = new Response(200, ['content-type' => 'text/plain'], $body);
        $this->assertSame('something here.', $object->unwrapResponse($response));
    }
}

class DummyClassForHasHttpRequestTrait
{
    use HasHttpRequest;
    public function getBaseUri()
    {
        return 'http://mock-uri';
    }
}
