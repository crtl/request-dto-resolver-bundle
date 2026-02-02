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

namespace Crtl\RequestDtoResolverBundle\Test\Unit\Attribute;

use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class QueryParamTest extends TestCase
{
    public function testHasTransformTypeReturnsTrueIfTransformTypeIsProvided(): void
    {
        $queryParam = new QueryParam('test', 'int');
        self::assertTrue($queryParam->hasTransformType());

        $queryParam = new QueryParam('test');
        self::assertFalse($queryParam->hasTransformType());
    }

    #[DataProvider('provideTransformCases')]
    public function testGetValueFromRequestCorrectlyTransformsValue(mixed $value, string|callable $transformType, mixed $expected): void
    {
        $request = new Request(['test' => $value]);
        $queryParam = new QueryParam('test', $transformType);

        $this->assertSame($expected, $queryParam->getValueFromRequest($request));
    }

    /**
     * @return iterable<string, array{0: mixed, 1: string|callable, 2: mixed}>
     */
    public static function provideTransformCases(): iterable
    {
        yield 'int valid' => ['123', 'int', 123];
        yield 'int zero' => ['0', 'int', 0];
        yield 'int invalid' => ['abc', 'int', null];

        yield 'float valid' => ['1.23', 'float', 1.23];
        yield 'float zero' => ['0.0', 'float', 0.0];
        yield 'float invalid' => ['abc', 'float', null];

        yield 'bool true' => ['1', 'bool', true];
        yield 'bool true string' => ['true', 'bool', true];
        yield 'bool false' => ['0', 'bool', false];
        yield 'bool false string' => ['false', 'bool', false];
        yield 'bool invalid' => ['abc', 'bool', null];

        yield 'string' => ['abc', 'string', 'abc'];

        yield 'callable' => ['abc', fn ($v) => strtoupper($v), 'ABC'];
    }

    public function testGetValueFromRequestReturnsNestedQueryParameter(): void
    {
        $request = new Request(['filter' => ['id' => '123']]);
        $queryParam = new QueryParam('id', 'int');

        $parent = new QueryParam('filter');
        $queryParam->setParent($parent);

        $this->assertSame(123, $queryParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestUsesPropertyNameIfNoExplicitNameIsProvided(): void
    {
        $request = new Request(['testProperty' => 'foo']);
        $queryParam = new QueryParam();

        $property = new \ReflectionProperty(new class {
            public string $testProperty;
        }, 'testProperty');

        $queryParam->setProperty($property);

        $this->assertSame('foo', $queryParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestReturnsNullIfParameterIsMissing(): void
    {
        $request = new Request();
        $queryParam = new QueryParam('missing');
        $this->assertNull($queryParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestReturnsArrayWithoutTransformationIfValueIsArray(): void
    {
        $request = new Request(['ids' => ['1', '2']]);
        $queryParam = new QueryParam('ids', 'int');
        $this->assertSame(['1', '2'], $queryParam->getValueFromRequest($request));
    }
}
