<?php


namespace App\Exception;

use Throwable;

/**
 * Class InvalidFormDataException
 * @package App\Exception
 *
 * @author Wings <Eternity.mr8@gmail.com>
 */
class InvalidFormDataException extends \Exception
{
    /**
     * InvalidFormDataException constructor.
     * @param string $message
     * @param int $code
     * @param Throwa|null $previous
     */
    public function __construct($message = "Invalid form data exception", $code = 0, Throwa $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}