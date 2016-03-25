<?php namespace CurlWrapper\Services\Curl;

use CurlWrapper\Exceptions\CurlWrapperException;
use CurlWrapper\Exceptions\RequestValidationException;
use CurlWrapper\Exceptions\UserNotAuthorizedException;

class CurlClient
{
    private $headers = array();

    /**
     * @return CurlClient
     */
    public static function make()
    {
        return new self();
    }

    /**
     * @param $resource
     *
     * @return mixed
     * @throws UserNotAuthorizedException
     */
    public function get($resource)
    {
        return $this->getResponse('GET', $resource);
    }

    /**
     * @param $resource
     *
     * @return mixed
     * @throws UserNotAuthorizedException
     */
    public function delete($resource)
    {
        return $this->getResponse('DELETE', $resource);
    }

    /**
     * @param $resource
     * @param $data
     *
     * @return mixed
     * @throws UserNotAuthorizedException
     */
    public function put($resource, $data)
    {
        return $this->getResponse('PUT', $resource, $data);
    }

    /**
     * @param $resource
     * @param $data
     *
     * @return mixed
     * @throws UserNotAuthorizedException
     */
    public function post($resource, $data)
    {
        return $this->getResponse('POST', $resource, $data);
    }

    /**
     * @param array $headers
     *
     * @return $this
     */
    public function withHeaders(array $headers = array())
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param $verb
     * @param $resource
     * @param array $data
     *
     * @return mixed
     * @throws RequestValidationException
     * @throws UserNotAuthorizedException
     */
    protected function getResponse($verb, $resource, array $data = array())
    {
        $dispatcher = new RequestDispatcher();
        $request = new Request($resource);

        $this->setRequestHeaders($request);

        $this->prepareRequest($verb, $data, $request);

        $dispatcher->add($request);
        $dispatcher->execute();

        $responses = $this->getRequestContent($dispatcher);

        $this->checkResponseForErrors($responses);

        return json_decode($responses[0]->getContent());
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    protected function prepareDeleteRequest(Request $request)
    {
        $request->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    protected function prepareGetRequest(Request $request)
    {
        $request->setOption(CURLOPT_HTTPGET, true);
    }

    /**
     * @param Request $request
     * @param $data
     * @throws \Exception
     */
    protected function preparePostRequest(Request $request, $data)
    {
        if ($data !== null) {
            $request->setOption(CURLOPT_POST, true);
            $request->setOption(CURLOPT_POSTFIELDS, $data);
        } else {
            $request->setOption(CURLOPT_CUSTOMREQUEST, 'POST');
        }
    }

    /**
     * @param Request $request
     * @param $data
     * @throws \Exception
     */
    protected function preparePutRequest(Request $request, $data)
    {
        $request->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data !== null) {
            $request->setOption(CURLOPT_POSTFIELDS, $data);
        }
    }

    /**
     * @param $request
     */
    protected function setRequestHeaders($request)
    {
        foreach ($this->headers as $header => $value) {
            $request->headers->set($header, $value);
        }

        $request->setOption(CURLOPT_FOLLOWLOCATION, true);
    }

    /**
     * @param $verb
     * @param array $data
     * @param $request
     */
    protected function prepareRequest($verb, array $data, $request)
    {
        switch ($verb) {
            case 'DELETE':
                $this->prepareDeleteRequest($request);
                break;
            case 'GET':
                $this->prepareGetRequest($request);
                break;
            case 'POST':
                $this->preparePostRequest($request, json_encode($data));
                break;
            case 'PUT':
                $this->preparePutRequest($request, $data);
                break;
        }
    }

    /**
     * @param $dispatcher
     *
     * @return array
     */
    protected function getRequestContent($dispatcher)
    {
        $requests = $dispatcher->all();
        $responses = array();

        foreach ($requests as $request) {
            $responses[] = $request->getResponse();
        }
        return $responses;
    }

    /**
     * @param $responses
     *
     * @throws CurlWrapperException
     * @throws RequestValidationException
     * @throws UserNotAuthorizedException
     */
    protected function checkResponseForErrors($responses)
    {
        switch($responses[0]->getStatusCode()) {
            case 400:
                throw new RequestValidationException();
                break;
            case 401:
                throw new UserNotAuthorizedException();
                break;
            case 200:
                // Do nothing, no errors
                break;
            default:
                throw new CurlWrapperException($responses[0]->getStatusCode());
        }
    }
}
