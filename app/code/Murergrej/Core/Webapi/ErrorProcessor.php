<?php

namespace Murergrej\Core\Webapi;

use Magento\Framework\App\State;
use Magento\Framework\Exception\AggregateExceptionInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;

class ErrorProcessor extends \Magento\Framework\Webapi\ErrorProcessor
{
    const INTERNAL_SERVER_ERROR_MSG = 'Internal Error. Details are available in Magento log file. Report ID: %1';

    /**
     * Fix translation
     *
     * @param \Exception $exception Exception to convert to a WebAPI exception
     * @return WebapiException
     */
    public function maskException(\Exception $exception)
    {
        $isDevMode = $this->_appState->getMode() === State::MODE_DEVELOPER;
        $stackTrace = $isDevMode ? $exception->getTraceAsString() : null;

        if ($exception instanceof WebapiException) {
            $maskedException = $exception;
        } elseif ($exception instanceof LocalizedException) {
            // Map HTTP codes for LocalizedExceptions according to exception type
            if ($exception instanceof NoSuchEntityException) {
                $httpCode = WebapiException::HTTP_NOT_FOUND;
            } elseif (($exception instanceof AuthorizationException)
                || ($exception instanceof AuthenticationException)
            ) {
                $httpCode = WebapiException::HTTP_UNAUTHORIZED;
            } else {
                // Input, Expired, InvalidState exceptions will fall to here
                $httpCode = WebapiException::HTTP_BAD_REQUEST;
            }

            if ($exception instanceof AggregateExceptionInterface) {
                $errors = $exception->getErrors();
            } else {
                $errors = null;
            }

            $maskedException = new WebapiException(
                new Phrase($exception->getRawMessage()),
                $exception->getCode(),
                $httpCode,
                $exception->getParameters(),
                get_class($exception),
                $errors,
                $stackTrace
            );
        } else {
            $message = $exception->getMessage();
            $code = $exception->getCode();
            //if not in Dev mode, make sure the message and code is masked for unanticipated exceptions
            if (!$isDevMode) {
                /** Log information about actual exception */
                $reportId = $this->_critical($exception);
                $phrase = __(self::INTERNAL_SERVER_ERROR_MSG, $reportId);
                $code = 0;
            } else {
                $phrase = new Phrase($message);
            }
            $maskedException = new WebapiException(
                $phrase,
                $code,
                WebapiException::HTTP_INTERNAL_ERROR,
                [],
                '',
                null,
                $stackTrace
            );
        }
        return $maskedException;
    }
}
