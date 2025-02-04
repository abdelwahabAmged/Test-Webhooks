<?php

namespace PWA\Import\Model;

use Throwable;

class HttpException extends \Exception
{
    /**
     * @var array|null
     */
    protected $response;

    public function __construct($response, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
