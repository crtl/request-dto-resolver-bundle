<?php

namespace Crtl\RequestDTOResolverBundle\Test\Unit\Attribute;

use Crtl\RequestDTOResolverBundle\Attribute\HeaderParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class HeaderParamTest extends TestCase
{
    public function testGetValueFromRequest(): void
    {
        $paramName = 'test_header';
        $paramValue = 'test_value';

        $request = new Request([], [], [], [], [], ['HTTP_'.strtoupper($paramName) => $paramValue]);

        $headerParam = new HeaderParam($paramName);

        $this->assertEquals($paramValue, $headerParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestWithMissingHeader(): void
    {
        $paramName = 'missing_header';

        $request = new Request();

        $headerParam = new HeaderParam($paramName);

        $this->assertNull($headerParam->getValueFromRequest($request));
    }
}
