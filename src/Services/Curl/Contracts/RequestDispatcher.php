<?php namespace CurlWrapper\Services\Curl\Contracts;

use Closure;
use CurlWrapper\Services\Curl\Contracts\Request as RequestContract;

interface RequestDispatcher
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function add(RequestContract $request);

    /**
     * @return mixed
     */
    public function clear();

    /**
     * @param $key
     */
    public function remove($key);

    /**
     * @return mixed
     */
    public function getStackSize();

    /**
     * @param $size
     */
    public function setStackSize($size);

    /**
     * @param Closure $callback
     */
    public function execute(Closure $callback = null);

    /**
     * @return mixed
     */
    public function all();
}
