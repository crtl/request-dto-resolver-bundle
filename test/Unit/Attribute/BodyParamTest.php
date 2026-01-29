<?php

namespace Crtl\RequestDTOResolverBundle\Test\Unit\Attribute;

use Crtl\RequestDTOResolverBundle\Attribute\BodyParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class BodyParamTest extends TestCase
{
    public function testGetValueFromRequest(): void
    {
        $paramName = 'test_param';
        $paramValue = 'test_value';

        $request = new Request([], [$paramName => $paramValue]);

        $bodyParam = new BodyParam($paramName);

        $this->assertEquals($paramValue, $bodyParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestWithMissingParam(): void
    {
        $request = new Request();
        $bodyParam = new BodyParam('missing_param');

        $this->assertNull($bodyParam->getValueFromRequest($request));
    }
}
