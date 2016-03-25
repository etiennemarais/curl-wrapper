<?php namespace CurlWrapper\Exceptions;

class RequestValidationException extends \Exception
{
    public function __construct()
    {
        $message = 'Request data validation failed';
        parent::__construct($message, 400);
    }
}