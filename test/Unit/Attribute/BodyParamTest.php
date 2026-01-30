<?php

/*
 * This file is part of a private project.
 *
 * Copyright 2026 Crtl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Crtl\RequestDTOResolverBundle\Test\Unit\Attribute;

use Crtl\RequestDTOResolverBundle\Attribute\BodyParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class BodyParamTest extends TestCase
{
    public function testGetValueFromRequestReturnsValueFromRequestRequestBag(): void
    {
        $paramName = 'test_param';
        $paramValue = 'test_value';

        $request = new Request([], [$paramName => $paramValue]);

        $bodyParam = new BodyParam($paramName);

        $this->assertEquals($paramValue, $bodyParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestReturnsNullIfParameterIsMissing(): void
    {
        $request = new Request();
        $bodyParam = new BodyParam('missing_param');

        $this->assertNull($bodyParam->getValueFromRequest($request));
    }
}
