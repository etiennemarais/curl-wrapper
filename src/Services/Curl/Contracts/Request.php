<?php namespace CurlWrapper\Services\Curl\Contracts;

interface Request 
{
    public function getHandle();

    public function getInfo($key = null);

    public function getErrorMessage();

    public function getRawResponse();

    public function getResponse();

    public function setOption($option, $value = null);

    public function execute();

    public function setRawResponse($content);

    public function isExecuted();

    public function isSuccessful();
}