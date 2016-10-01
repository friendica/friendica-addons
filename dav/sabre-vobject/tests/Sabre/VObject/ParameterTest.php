<?php

namespace Sabre\VObject;

class ParameterTest extends \PHPUnit_Framework_TestCase
{
    public function testSetup()
    {
        $param = new Parameter('name', 'value');
        $this->assertEquals('NAME', $param->name);
        $this->assertEquals('value', $param->value);
    }

    public function testCastToString()
    {
        $param = new Parameter('name', 'value');
        $this->assertEquals('value', $param->__toString());
        $this->assertEquals('value', (string) $param);
    }
}
