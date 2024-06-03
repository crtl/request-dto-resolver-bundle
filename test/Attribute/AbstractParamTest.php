<?php

namespace Crtl\RequestDTOResolverBundle\Test\Attribute;

use Crtl\RequestDTOResolverBundle\Attribute\AbstractParam;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;

class TestClass
{
    public string $testProperty;
}

class AbstractParamTest extends TestCase
{
    public function testGetNameWithExplicitName()
    {
        $param = new class("testName") extends AbstractParam {
            public function getValueFromRequest(Request $request): mixed
            {
                return $request->request->get($this->getName());
            }
        };

        $this->assertEquals("testName", $param->getName());
    }

    public function testGetNameWithPropertyName()
    {
        $param = new class() extends AbstractParam {
            public function getValueFromRequest(Request $request): mixed
            {
                return $request->request->get($this->getName());
            }
        };

        $property = new ReflectionProperty(TestClass::class, "testProperty");
        $param->setProperty($property);

        $this->assertEquals("testProperty", $param->getName());
    }

    public function testGetNameThrowsLogicException()
    {
        $this->expectException(LogicException::class);

        $param = new class() extends AbstractParam {
            public function getValueFromRequest(Request $request): mixed
            {
                return $request->request->get($this->getName());
            }
        };

        $param->getName();
    }

    public function testSetProperty()
    {
        $param = new class() extends AbstractParam {
            public function getValueFromRequest(Request $request): mixed
            {
                return $request->request->get($this->getName());
            }
        };

        $property = new ReflectionProperty(TestClass::class, "testProperty");
        $param->setProperty($property);

        $this->assertSame($property, $param->getProperty());
    }

    public function testGetValueFromRequest()
    {
        $request = new Request([], ["param" => "value"]);

        $param = new class() extends AbstractParam {
            public function getValueFromRequest(Request $request): mixed
            {
                return $request->request->get("param");
            }
        };

        $this->assertEquals("value", $param->getValueFromRequest($request));
    }
}
