<?php namespace CurlWrapper\Tests;

use CurlWrapper\Services\Curl\CurlClient;

class CurlClientTest extends \PHPUnit_Framework_TestCase
{
    public function testGetResource()
    {
        $response = CurlClient::make()->get('http://httpbin.org/get');

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/get', $response->url);
    }

    public function testGetResourceWithUnauthorizedCode()
    {
        $this->setExpectedException(
            'CurlWrapper\Exceptions\UserNotAuthorizedException',
            'User not authorized'
        );
        $response = CurlClient::make()->get('http://httpbin.org/status/401');

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/status/401', $response->url);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testGetResourceWithHeaders()
    {
        $headers = array(
            'X-Auth-Token' => 'someAuthToken',
        );

        $response = CurlClient::make()
            ->withHeaders($headers)
            ->get('http://httpbin.org/get');

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/get', $response->url);

        $headers = (array)$response->headers;
        $this->assertSame('someAuthToken', $headers['X-Auth-Token']);
    }

    public function testDeleteResource()
    {
        $response = CurlClient::make()->delete('http://httpbin.org/delete');

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/delete', $response->url);
    }

    public function testDeleteResourceWithHeaders()
    {
        $headers = array(
            'X-Auth-Token' => 'someAuthTokenDelete',
        );

        $response = CurlClient::make()
            ->withHeaders($headers)
            ->delete('http://httpbin.org/delete');

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/delete', $response->url);

        $headers = (array)$response->headers;
        $this->assertSame('someAuthTokenDelete', $headers['X-Auth-Token']);
    }

    public function testPutResource()
    {
        $response = CurlClient::make()->put('http://httpbin.org/put', array());

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/put', $response->url);
    }

    public function testPutResourceWithHeaders()
    {
        $headers = array(
            'X-Auth-Token' => 'someAuthTokenPut',
        );

        $response = CurlClient::make()
            ->withHeaders($headers)
            ->put('http://httpbin.org/put', array());

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/put', $response->url);

        $headers = (array)$response->headers;
        $this->assertSame('someAuthTokenPut', $headers['X-Auth-Token']);
    }

    public function testPostResource()
    {
        $headers = array(
            'Content-Type' => 'application/json',
        );

        $data = array(
            'email' => 'test@test.com',
            'name' => 'Firstname',
        );
        $response = CurlClient::make()->withHeaders($headers)
            ->post('http://httpbin.org/post', $data);

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/post', $response->url);
        $this->assertSame(json_encode($data), $response->data);
    }
}
