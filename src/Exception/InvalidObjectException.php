<?php


namespace App\Exception;

use Throwable;

/**
 * Class InvalidObjectException
 * @package App\Exception
 *
 * @author Wings <Eternity.mr8@gmail.com>
 */
class InvalidObjectException extends \Exception
{
    /**
     * InvalidJsonFormat constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "Invalid object", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
