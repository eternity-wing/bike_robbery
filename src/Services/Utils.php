<?php


namespace App\Services;

use App\Exception\InvalidJsonFormatException;

/**
 * Class Utils
 * @package App\Services
 *
 * @author Wings <Eternity.mr8@gmail.com>
 */
class Utils
{
    /**
     * @param string $json
     * @return array
     * @throws InvalidJsonFormatException
     */
    public static function parseJson(string $json): array
    {
        $data = json_decode($json, true);
        if ($data === null) {
            throw new InvalidJsonFormatException();
        }
        return $data;
    }
}
