<?php namespace CurlWrapper\Exceptions;

class CurlWrapperException extends \Exception
{
    public function __construct($code)
    {
        $message = 'Request to API failed';
        parent::__construct($message, $code);
    }
}