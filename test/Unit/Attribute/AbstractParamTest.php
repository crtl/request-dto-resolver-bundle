<?php

namespace Crtl\RequestDTOResolverBundle\Test\Unit\Attribute;

use Crtl\RequestDTOResolverBundle\Attribute\AbstractParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class TestClass
{
    public string $testProperty;
}

class TestParam extends AbstractParam
{
    public function getValueFromRequest(Request $request): mixed
    {
        return $request->request->get($this->getName());
    }
}

class AbstractParamTest extends TestCase
{
    public function testGetNameWithExplicitName(): void
    {
        $param = new TestParam('testName');

        $this->assertEquals('testName', $param->getName());
    }

    public function testGetNameWithPropertyName(): void
    {
        $param = new TestParam();

        $property = new \ReflectionProperty(TestClass::class, 'testProperty');
        $param->setProperty($property);

        $this->assertEquals('testProperty', $param->getName());
    }

    public function testGetNameThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);

        $param = new TestParam();

        $param->getName();
    }

    public function testSetProperty(): void
    {
        $param = new TestParam();

        $property = new \ReflectionProperty(TestClass::class, 'testProperty');
        $param->setProperty($property);

        $this->assertSame($property, $param->getProperty());
    }

    public function testGetValueFromRequest(): void
    {
        $request = new Request([], ['param' => 'value']);

        $param = new TestParam('param');

        $this->assertEquals('value', $param->getValueFromRequest($request));
    }
}
