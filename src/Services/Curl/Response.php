<?php namespace CurlWrapper\Services\Curl;

use CurlWrapper\Services\Curl\Contracts\Request as RequestContract;

class Response extends \Symfony\Component\HttpFoundation\Response
{
    /**
     * @param RequestContract $request
     *
     * @return static
     */
    public static function forge(RequestContract $request)
    {
        $headerSize = $request->getInfo(CURLINFO_HEADER_SIZE);
        $response = $request->getRawResponse();
        $content = (strlen($response) === $headerSize) ? '' : substr($response, $headerSize);
        $rawHeaders = rtrim(substr($response, 0, $headerSize));
        $headers = array();

        foreach (preg_split('/(\\r?\\n)/', $rawHeaders) as $header) {
            if ($header) {
                $headers[] = $header;
            } else {
                $headers = array();
            }
        }

        $headerBag = array();
        $info = $request->getInfo();
        $status = explode(' ', $headers[0]);
        $status = explode('/', $status[0]);

        unset($headers[0]);

        foreach ($headers as $header) {
            list($key, $value) = explode(': ', $header);
            $headerBag[trim($key)] = trim($value);
        }

        $response = new static($content, $info['http_code'], $headerBag);
        $response->setProtocolVersion($status[1]);
        $response->setCharset(substr(strstr($response->headers->get('Content-Type'), '='), 1));

        return $response;
    }
}