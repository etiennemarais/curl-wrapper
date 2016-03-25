<?php namespace CurlWrapper\Services\Curl;

use CurlWrapper\Services\Curl\Contracts\Request as RequestContract;
use CurlWrapper\Services\Curl\Contracts\RequestDispatcher as RequestDispatcherContract;

class RequestDispatcher implements RequestDispatcherContract
{
    protected $handle;
    protected $requests = array();
    protected $stackSize = 42;

    public function __construct()
    {
        $this->handle = curl_multi_init();
    }

    /**
     * @param RequestContract $request
     *
     * @return int
     */
    public function add(RequestContract $request)
    {
        $this->requests[] = $request;

        return (count($this->requests) - 1);
    }

    public function clear()
    {
        $this->requests = array();
    }

    /**
     * @param $key
     */
    public function remove($key)
    {
        if (array_key_exists($key, $this->requests) === true) {
            $this->requests[$key]->removeMultiHandle($this->handle);
            unset($this->requests[$key]);
        }
    }

    /**
     * @return int
     */
    public function getStackSize()
    {
        return $this->stackSize;
    }

    /**
     * @param $size
     * @throws \InvalidArgumentException
     */
    public function setStackSize($size)
    {
        if (gettype($size) !== 'integer') {
            throw new \InvalidArgumentException(
                'setStackSize() expected an integer, '.gettype($size).' received.'
            );
        }

        $this->stackSize = $size;
    }

    /**
     * @param callable $callback
     *
     * @throws \Exception
     */
    public function execute(\Closure $callback = null)
    {
        $stacks = $this->buildStacks();

        foreach ($stacks as $requests) {
            foreach ($requests as $request) {
                $status = curl_multi_add_handle($this->handle, $request->getHandle());
                if ($status !== CURLM_OK) {
                    throw new \Exception(sprintf(
                        'Unable to add request to cURL multi handle (code #%u)',
                        $status
                    ));
                }
            }

            $this->dispatch();

            foreach ($requests as $request) {
                $request->setRawResponse(curl_multi_getcontent($request->getHandle()));
                curl_multi_remove_handle($this->handle, $request->getHandle());

                if ($callback !== null) {
                    $callback($request->getResponse());
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function all()
    {
        return $this->requests;
    }

    /**
     * @return array
     */
    protected function buildStacks()
    {
        $stacks   = array();
        $stackNo  = 0;
        $currSize = 0;

        foreach ($this->requests as $request) {
            if ($currSize === $this->stackSize) {
                $currSize = 0;
                $stackNo++;
            }

            $stacks[$stackNo][] = $request;
            $currSize++;
        }

        return $stacks;
    }

    /**
     * @throws \Exception
     */
    protected function dispatch()
    {
        list($mrc, $active) = $this->process();

        while ($active and $mrc === CURLM_OK) {
            list($mrc, $active) = $this->process();
        }

        if ($mrc !== CURLM_OK) {
            throw new \Exception('cURL read error #' . $mrc);
        }
    }

    /**
     * @return array
     */
    protected function process()
    {
        // Workaround for PHP Bug #61141.
        if (curl_multi_select($this->handle) === -1) {
            usleep(100);
        }

        do {
            $mrc = curl_multi_exec($this->handle, $active);
        } while ($mrc === CURLM_CALL_MULTI_PERFORM);

        return array($mrc, $active);
    }
}