<?php

/**
 * @package redaxo\core
 */
class rex_exception extends Exception
{
    /**
     * @param string    $message
     * @param Exception $previous
     */
    public function __construct($message, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}

/**
 * Exception class for user-friendly error messages.
 *
 * @package redaxo\core
 */
class pz_functional_exception extends rex_exception
{
}

/**
 * Exception class for http-status code handling.
 *
 * @package redaxo\core
 */
class pz_http_exception extends rex_exception
{
    private $httpCode;

    /**
     * @param Exception $cause
     * @param int       $httpCode
     */
    public function __construct(Exception $cause, $httpCode)
    {
        parent::__construct(null, $cause);
        $this->httpCode = $httpCode;
    }

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }
}
