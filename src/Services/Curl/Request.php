<?php namespace CurlWrapper\Services\Curl;

use CurlWrapper\Services\Curl\Contracts\Request as RequestContract;

class Request implements RequestContract
{
    public $headers;
    protected $content;
    protected $defaults = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
    );
    protected $executed = false;
    protected $handle;
    protected $response;

    /**
     * @param $url
     */
    public function __construct($url)
    {
        $this->handle = curl_init($url);
        $this->headers = new HeaderBag(array(), $this);

        foreach ($this->defaults as $option => $value) {
            curl_setopt($this->handle, $option, $value);
        }
    }

    public function __destruct()
    {
        curl_close($this->handle);
    }

    /**
     * @return resource
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @param null $key
     *
     * @return mixed
     */
    public function getInfo($key = null)
    {
        if ($key === null) {
            return curl_getinfo($this->handle);
        }

        return curl_getinfo($this->handle, $key);
    }

    /**
     * @return null|string
     */
    public function getErrorMessage()
    {
        $error = curl_error($this->handle);

        return ($error === '') ? null : $error;
    }

    /**
     * @return mixed
     */
    public function getRawResponse()
    {
        return $this->content;
    }

    /**
     * @return null|static
     */
    public function getResponse()
    {
        if ($this->response === null && $this->isExecuted()) {
            $this->response = Response::forge($this);
        }

        return $this->response;
    }

    /**
     * @param $option
     * @param null $value
     *
     * @throws \Exception
     */
    public function setOption($option, $value = null)
    {
        if (is_array($option)) {
            foreach ($option as $opt => $val) {
                $this->setOption($opt, $val);
            }
            return;
        }

        if (array_key_exists($option, $this->defaults)) {
            throw new \Exception(sprintf('Unable to set protected option #%u', $option));
        }

        if (curl_setopt($this->handle, $option, $value) === false) {
            throw new \Exception(sprintf('Couldn\'t set option #%u', $option));
        }
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $this->content = curl_exec($this->handle);

        if ($this->isSuccessful() === false) {
            throw new \Exception($this->getErrorMessage());
        }

        $this->executed = true;
    }

    /**
     * @param $content
     */
    public function setRawResponse($content)
    {
        $this->executed = true;
        $this->content = $content;
    }

    /**
     * @return bool
     */
    public function isExecuted()
    {
        return ($this->executed) ? true : false;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return ($this->getErrorMessage() === null) ? true : false;
    }
}