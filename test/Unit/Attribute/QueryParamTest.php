<?php

namespace Crtl\RequestDTOResolverBundle\Test\Unit\Attribute;

use Crtl\RequestDTOResolverBundle\Attribute\QueryParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class QueryParamTest extends TestCase
{
    public function testGetValueFromRequest(): void
    {
        $paramName = 'test_query';
        $paramValue = 'test_value';

        $request = new Request([$paramName => $paramValue]);

        $queryParam = new QueryParam($paramName);

        $this->assertEquals($paramValue, $queryParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestWithMissingQueryParam(): void
    {
        $paramName = 'missing_query';

        $request = new Request();

        $queryParam = new QueryParam($paramName);

        $this->assertNull($queryParam->getValueFromRequest($request));
    }
}
