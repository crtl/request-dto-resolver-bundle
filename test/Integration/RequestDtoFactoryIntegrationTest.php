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

namespace Crtl\RequestDtoResolverBundle\Test\Integration;

use Crtl\RequestDtoResolverBundle\Factory\Exception\CircularReferenceException;
use Crtl\RequestDtoResolverBundle\Factory\Exception\RequestDtoHydrationException;
use Crtl\RequestDtoResolverBundle\Factory\RequestDtoFactory;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\AllParamTypesDTO;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\CircularReferencingDto;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\DtoWithNestedDtoArray;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\NestedChildDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

final class RequestDtoFactoryIntegrationTest extends KernelTestCase
{
    private RequestDtoFactory $factory;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var RequestDtoFactory $factory */
        $factory = self::getContainer()->get(RequestDtoFactory::class);
        $this->factory = $factory;
    }

    public function testFromRequestThrowsReflectionExceptionWhenClassDoesNotExist(): void
    {
        $this->expectException(\ReflectionException::class);
        // @phpstan-ignore argument.type
        $this->factory->fromRequest('NonExistingClass', new Request());
    }

    public function testFromRequestHydratesAllParamTypes(): void
    {
        $request = new Request(
            query: ['query' => 'query_val'],
            request: ['body' => 'body_val'],
            attributes: ['_route_params' => ['_route' => 'test_route']],
            server: ['HTTP_USER_AGENT' => 'test_ua'],
        );

        /** @var AllParamTypesDTO $dto */
        $dto = $this->factory->fromRequest(AllParamTypesDTO::class, $request);

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(AllParamTypesDTO::class, $dto);
        $this->assertEquals('query_val', $dto->query);
        $this->assertEquals('body_val', $dto->body);
        $this->assertEquals('test_route', $dto->routeName);
        $this->assertEquals('test_ua', $dto->userAgent);
    }

    public function testFromRequestHydratesNestedDtos(): void
    {
        $request = new Request(
            request: [
                'children' => [
                    ['childName' => 'child1'],
                    ['childName' => 'child2'],
                ]
            ],
        );

        /** @var DtoWithNestedDtoArray $dto */
        $dto = $this->factory->fromRequest(DtoWithNestedDtoArray::class, $request);

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(DtoWithNestedDtoArray::class, $dto);
        $this->assertCount(2, $dto->children);

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(NestedChildDTO::class, $dto->children[0]);
        $this->assertEquals('child1', $dto->children[0]->childName);

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(NestedChildDTO::class, $dto->children[1]);
        $this->assertEquals('child2', $dto->children[1]->childName);
    }

    public function testFromArrayHydratesDto(): void
    {
        $data = [
            'query' => 'q',
            'body' => 'b',
            'routeName' => 'r',
            'userAgent' => 'ua'
        ];

        /** @var AllParamTypesDTO $dto */
        $dto = $this->factory->fromArray(AllParamTypesDTO::class, $data);

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(AllParamTypesDTO::class, $dto);
        $this->assertEquals('q', $dto->query);
        $this->assertEquals('b', $dto->body);
        $this->assertEquals('r', $dto->routeName);
        $this->assertEquals('ua', $dto->userAgent);
    }

    public function testFromArrayThrowsExceptionOnTypeError(): void
    {
        $data = [
            'childName' => ['invalid'], // Expected string, got array
        ];

        $this->expectException(RequestDtoHydrationException::class);

        try {
            $this->factory->fromArray(NestedChildDTO::class, $data);
        } catch (RequestDtoHydrationException $e) {
            $this->assertCount(1, $e->violations);
            // The violation path is prefixed with the property name in fromArrayRecursive
            $this->assertEquals('childName', $e->violations[0]->getPropertyPath());
            throw $e;
        }
    }

    public function testFromArrayThrowsExceptionWithNestedArrayRequestDtoTypeMismatch(): void
    {
        self::expectException(RequestDtoHydrationException::class);
        try {
            $this->factory->fromArray(
                DtoWithNestedDtoArray::class,
                ['children' => [
                    [], // No name at all an property is not nullable
                    ['childName' => 1], // Int despite string is expected
                    ['childName' => 1.0], // float despite string is expected
                    ['childName' => false], // bool despite string is expected
                    ['childName' => new \stdClass()], // object despite string is expected
                ]],
            );
        } catch (RequestDtoHydrationException $e) {
            $this->assertCount(5, $e->violations);
            self::assertSame('children[0].childName', $e->violations[0]->getPropertyPath());
            self::assertSame('children[1].childName', $e->violations[1]->getPropertyPath());
            self::assertSame('children[2].childName', $e->violations[2]->getPropertyPath());
            self::assertSame('children[3].childName', $e->violations[3]->getPropertyPath());
            self::assertSame('children[4].childName', $e->violations[4]->getPropertyPath());

            throw $e;
        }
    }

    public function testFromArrayThrowsCircularReferenceExceptionWhenCircularReferenceIsDetected(): void
    {
        self::expectException(CircularReferenceException::class);
        $this->factory->fromArray(CircularReferencingDto::class, ['prop' => []]);
    }

    public function testFromArrayThrowsCircularReferenceExceptionWhenNestedCircularReferenceIsDetected(): void
    {
        self::expectException(CircularReferenceException::class);
        $this->factory->fromArray(CircularReferencingDto::class, ['array' => [[]]]);
    }

    public function testFromRequestThrowsCircularReferenceExceptionWhenCircularReferenceIsDetected(): void
    {
        self::expectException(CircularReferenceException::class);
        $request = new Request(request: ['prop' => []]);
        $this->factory->fromRequest(CircularReferencingDto::class, $request);
    }

    public function testFromRequestThrowsCircularReferenceExceptionWhenNestedCircularReferenceIsDetected(): void
    {
        self::expectException(CircularReferenceException::class);
        $request = new Request(request: ['array' => [[]]]);
        $this->factory->fromRequest(CircularReferencingDto::class, $request);
    }
}
