<?php


namespace App\Tests\Services;

use App\Exception\InvalidJsonFormatException;
use App\Services\Utils;
use PHPUnit\Framework\TestCase;

/**
 * Class UtilsTest
 * @package App\Tests\Services
 *
 * @author Wings <Eternity.mr8@gmail.com>
 */
class UtilsTest extends TestCase
{

    /**
     * @throws InvalidJsonFormatException
     */
    public function testJsonParse()
    {
        $data = ['first_name' => 'John', 'last_name' => 'Doe'];
        $this->assertEquals($data, Utils::parseJson(json_encode($data)));
        $this->expectException(InvalidJsonFormatException::class);
        $invalidJsonString = 'John Doe';
        Utils::parseJson($invalidJsonString);
    }
}
