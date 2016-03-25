<?php namespace CurlWrapper\Services\Curl;

use CurlWrapper\Services\Curl\Contracts\Request as RequestContract;

class HeaderBag extends \Symfony\Component\HttpFoundation\HeaderBag
{
    protected $request;

    /**
     * @param array $headers
     * @param RequestContract $request
     */
    public function __construct(array $headers, RequestContract $request)
    {
        $this->request = $request;
        parent::__construct($headers);
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        parent::remove($key);
        $this->updateRequest();
    }

    /**
     * @param string $key
     * @param array|string $values
     * @param bool $replace
     */
    public function set($key, $values, $replace = true)
    {
        parent::set($key, $values, $replace);
        $this->updateRequest();
    }

    protected function updateRequest()
    {
        $headers = array();
        foreach ($this->all() as $key => $values) {
            foreach ($values as $value) {
                $headers[] = $key.': '.$value;
            }
        }

        $this->request->setOption(CURLOPT_HTTPHEADER, $headers);
    }
}