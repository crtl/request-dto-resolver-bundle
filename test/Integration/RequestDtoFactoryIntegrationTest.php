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
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\New\CircularReferencingRequestDto;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\New\MixedType\MixedRequestDto;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\New\StrictTypes\NonStrictRequestDto;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\New\StrictTypes\RequestDtoWithDeepNesting;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\New\StrictTypes\RequestDtoWithShallowNesting;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\New\StrictTypes\StrictRequestDto;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\New\TransformQueryParamRequestDto;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @phpstan-type HydrationTestCaseArgs array{
 *      method: string,
 *      context: array<string, mixed>|Request,
 *      expected: array<string, mixed>,
 *      className: class-string,
 * }
 * @phpstan-type ViolationTestCaseArgs array{
 *      method: string,
 *      context: array<string, mixed>|Request,
 *      expected: string[],
 *      className: class-string,
 * }
 */
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

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>|Request}>
     */
    public static function throwsReflectionExceptionWhenClassDoesNotExist(): iterable
    {
        yield 'fromRequest' => ['fromRequest', new Request()];
        yield 'fromArray' => ['fromArray', []];
    }

    /**
     * @param array<string, mixed>|Request $context
     */
    #[DataProvider('throwsReflectionExceptionWhenClassDoesNotExist')]
    public function testThrowsReflectionExceptionWhenClassDoesNotExist(string $method, array|Request $context): void
    {
        $this->expectException(\ReflectionException::class);
        $this->factory->$method('NonExistingClass', $context);
    }

    /**
     * @return iterable<string, array{method: string, context: array<string, mixed>|Request}>
     */
    public static function throwsCircularReferenceExceptionWhenNestedCircularReferenceIsDetectedProvider(): iterable
    {
        yield 'fromArray with single child' => [
            'method' => 'fromArray',
            'context' => ['prop' => []],
        ];
        yield 'fromArray with array child' => [
            'method' => 'fromArray',
            'context' => ['array' => [[]]],
        ];
        yield 'fromRequest with single child' => [
            'method' => 'fromRequest',
            'context' => new Request(request: ['prop' => []]),
        ];
        yield 'fromRequest with array child' => [
            'method' => 'fromRequest',
            'context' => new Request(request: ['array' => [[]]]),
        ];
    }

    /**
     * @param array<string, mixed>|Request $context
     */
    #[DataProvider('throwsCircularReferenceExceptionWhenNestedCircularReferenceIsDetectedProvider')]
    public function testThrowsCircularReferenceExceptionWhenNestedCircularReferenceIsDetected(string $method, array|Request $context): void
    {
        self::expectException(CircularReferenceException::class);
        $this->factory->$method(CircularReferencingRequestDto::class, $context);
    }

    /**
     * @return iterable<string, HydrationTestCaseArgs>
     */
    public static function correctlyHydratesRequestDtosProvider(): iterable
    {
        // TODO: implement data providers, optionally add more entries.
        yield 'mixed fromRequest' => [
            'method' => 'fromRequest',
            'context' => new Request(request: ['prop' => []]),
            'expected' => [],
            'className' => MixedRequestDto::class,
        ];
        yield 'mixed fromArray' => [
            'method' => 'fromArray',
            'context' => [],
            'expected' => [],
            'className' => MixedRequestDto::class,
        ];

        // TODO: implement data providers, optionally add more entries.
        yield 'strict fromRequest' => [
            'method' => 'fromRequest',
            'context' => new Request(request: ['prop' => []]),
            'expected' => [],
            'className' => StrictRequestDto::class,
        ];
        yield 'strict fromArray' => [
            'method' => 'fromArray',
            'context' => [],
            'expected' => [],
            'className' => StrictRequestDto::class,
        ];

        // TODO: implement data provider
        yield 'non-strict fromRequest' => [
            'method' => 'fromRequest',
            'context' => new Request(request: ['prop' => []]),
            'expected' => [],
            'className' => NonStrictRequestDto::class,
        ];
        yield 'non-strict fromArray' => [
            'method' => 'fromArray',
            'context' => [],
            'expected' => [],
            'className' => NonStrictRequestDto::class,
        ];

        yield 'deep nested fromRequest with single child' => [
            'method' => 'fromRequest',
            'context' => new Request(request: [
                'child' => [
                    'child' => [
                        'childName' => 'name',
                    ]
                ]
            ]),
            'expected' => [
                'child' => [
                    'child' => [
                        'childName' => 'name',
                    ]
                ]
            ],
            'className' => RequestDtoWithDeepNesting::class,
        ];
        yield 'deep nested fromRequest with multiple children' => [
            'method' => 'fromRequest',
            'context' => new Request(request: [
                'children' => [
                    [
                        'children' => [[
                            'childName' => 'name',
                        ]]
                    ]
                ]
            ]),
            'expected' => [
                'children' => [
                    [
                        'children' => [[
                            'childName' => 'name',
                        ]]
                    ]
                ]
            ],
            'className' => RequestDtoWithDeepNesting::class,
        ];
        yield 'deep nested fromArray with single child' => [
            'method' => 'fromArray',
            'context' => [
                'child' => [
                    'child' => [
                        'childName' => 'name',
                    ]
                ]
            ],
            'expected' => [
                'child' => [
                    'child' => [
                        'childName' => 'name',
                    ]
                ]
            ],
            'className' => RequestDtoWithDeepNesting::class,
        ];
        yield 'deep nested fromArray with multiple children' => [
            'method' => 'fromArray',
            'context' => [
                'children' => [
                    [
                        'children' => [[
                            'childName' => 'name',
                        ]]
                    ]
                ]
            ],
            'expected' => [
                'children' => [
                    [
                        'children' => [[
                            'childName' => 'name',
                        ]]
                    ]
                ]
            ],
            'className' => RequestDtoWithDeepNesting::class,
        ];

        yield 'shallow nested fromRequest with single child' => [
            'method' => 'fromRequest',
            'context' => new Request(request: [
                'child' => [
                    'childName' => 'name',
                ]
            ]),
            'expected' => [
                'child' => [
                    'childName' => 'name',
                ]
            ],
            'className' => RequestDtoWithShallowNesting::class,
        ];
        yield 'shallow nested fromRequest with multiple children' => [
            'method' => 'fromRequest',
            'context' => new Request(request: [
                'children' => [[
                    'childName' => 'name',
                ]]
            ]),
            'expected' => [
                'children' => [[
                    'childName' => 'name',
                ]]
            ],
            'className' => RequestDtoWithShallowNesting::class,
        ];
        yield 'shallow nested fromArray with single child' => [
            'method' => 'fromArray',
            'context' => [
                'child' => [
                    'childName' => 'name',
                ]
            ],
            'expected' => [
                'child' => [
                    'childName' => 'name',
                ]
            ],
            'className' => RequestDtoWithShallowNesting::class,
        ];
        yield 'shallow nested fromArray with multiple children' => [
            'method' => 'fromArray',
            'context' => [
                'children' => [[
                    'childName' => 'name',
                ]]
            ],
            'expected' => [
                'children' => [[
                    'childName' => 'name',
                ]]
            ],
            'className' => RequestDtoWithShallowNesting::class,
        ];

        yield 'transform query param fromRequest' => [
            'method' => 'fromRequest',
            'context' => new Request([
                'queryInt' => '123',
                'queryNullableInt' => '321',
                'queryFloat' => '1.2',
                'queryNullableFloat' => '3.14',
                'queryBool' => 'yes',
                'queryNullableBool' => 'false',
            ]),
            'expected' => [
                'queryInt' => 123,
                'queryNullableInt' => 321,
                'queryFloat' => 1.2,
                'queryNullableFloat' => 3.14,
                'queryBool' => true,
                'queryNullableBool' => false,
            ],
            'className' => TransformQueryParamRequestDto::class,
        ];
        yield 'transform query param fromArray' => [
            'method' => 'fromArray',
            'context' => [
                'queryInt' => '123',
                'queryNullableInt' => '321',
                'queryFloat' => '1.2',
                'queryNullableFloat' => '3.14',
                'queryBool' => 'yes',
                'queryNullableBool' => 'false',
            ],
            'expected' => [
                'queryInt' => 123,
                'queryNullableInt' => 321,
                'queryFloat' => 1.2,
                'queryNullableFloat' => 3.14,
                'queryBool' => true,
                'queryNullableBool' => false,
            ],
            'className' => TransformQueryParamRequestDto::class,
        ];
    }

    /**
     * @param array<string, mixed>|Request $context
     * @param array<string, mixed> $expected
     * @param class-string $className
     */
    #[DataProvider('correctlyHydratesRequestDtosProvider')]
    public function testCorrectlyHydratesRequestDtos(
        string        $method,
        array|Request $context,
        array         $expected,
        string        $className,
    ): void
    {
        $dto = $this->factory->$method($className, $context);
        self::assertInstanceOf($className, $dto);
        self::objectMatchesExpectedStructure($dto, $expected);
    }

    /**
     * @return iterable<string, ViolationTestCaseArgs>
     */
    public static function throwsRequestDtoHydrationExceptionWithPropertyTypeViolationsWhenHydratingTypedDtoWithInvalidTypesProvider(): iterable
    {
        yield 'fromArray with invalid body scalar types' => [
            'method' => 'fromArray',
            'context' => [
                'bodyString' => ['not', 'a', 'string'],
                'bodyInt' => 'not-a-number',
                'bodyFloat' => 'not-a-number',
                'bodyBool' => ['not-a-bool'],
                'bodyArray' => 'not-an-array',
            ],
            'expected' => ['bodyString', 'bodyInt', 'bodyFloat', 'bodyBool', 'bodyArray'],
            'className' => StrictRequestDto::class,
        ];

        yield 'fromRequest with invalid body scalar types' => [
            'method' => 'fromRequest',
            'context' => new Request(request: [
                'bodyString' => ['not', 'a', 'string'],
                'bodyInt' => 'not-a-number',
                'bodyFloat' => 'not-a-number',
                'bodyBool' => ['not-a-bool'],
                'bodyArray' => 'not-an-array',
            ]),
            'expected' => ['bodyString', 'bodyInt', 'bodyFloat', 'bodyBool', 'bodyArray'],
            'className' => StrictRequestDto::class,
        ];

        yield 'fromArray with single invalid property among valid ones' => [
            'method' => 'fromArray',
            'context' => [
                'bodyString' => 'valid-string',
                'bodyInt' => ['not-an-int'],
            ],
            'expected' => ['bodyInt'],
            'className' => StrictRequestDto::class,
        ];

        yield 'fromArray with invalid types across param sources' => [
            'method' => 'fromArray',
            'context' => [
                'bodyInt' => ['not-an-int'],
            ],
            'expected' => ['bodyInt'],
            'className' => StrictRequestDto::class,
        ];

        yield 'fromRequest with invalid types across param sources' => [
            'method' => 'fromRequest',
            'context' => new Request(
                query: ['queryString' => ['not-a-string']],
                request: ['bodyInt' => ['not-an-int']],
            ),
            'expected' => ['bodyInt', 'queryString'],
            'className' => StrictRequestDto::class,
        ];

        yield 'fromArray with null for non-nullable properties' => [
            'method' => 'fromArray',
            'context' => [
                'bodyString' => null,
                'bodyInt' => null,
            ],
            'expected' => ['bodyString', 'bodyInt'],
            'className' => StrictRequestDto::class,
        ];

        yield 'fromArray with deep nested dto' => [
            'method' => 'fromArray',
            'context' => [
                'child' => [
                    'child' => [
                        'childName' => 1,
                    ],
                    'children' => [
                        ['childName' => 1],
                    ]
                ],
                'children' => [
                    [
                        'child' => [
                            'childName' => 1,
                        ],
                        'children' => [
                            ['childName' => 1],
                            null,
                        ]
                    ],
                ],
            ],
            'expected' => [
                'child.child.childName',
                'child.children[0].childName',
                'children[0].child.childName',
                'children[0].children[0].childName',
            ],
            'className' => RequestDtoWithDeepNesting::class,
        ];
    }

    /**
     * @param array<string, mixed>|Request $context
     * @param string[] $expectedViolations
     * @param class-string $className
     */
    #[DataProvider('throwsRequestDtoHydrationExceptionWithPropertyTypeViolationsWhenHydratingTypedDtoWithInvalidTypesProvider')]
    public function testThrowsRequestDtoHydrationExceptionWithPropertyTypeViolationsWhenHydratingTypedDtoWithInvalidTypes(
        string        $method,
        array|Request $context,
        array         $expectedViolations,
        string        $className,
    ): void
    {
        self::expectException(RequestDtoHydrationException::class);
        try {
            $this->factory->$method($className, $context);
        } catch (RequestDtoHydrationException $e) {
            self::assertRequestDtoHydrationException($e, $expectedViolations);
            throw $e;
        }
    }

    /**
     * @return array<string, ConstraintViolationInterface[]>
     */
    private static function mapViolationsToPath(ConstraintViolationListInterface $violations): array
    {
        $result = [];
        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();
            $result[$path] ??= [];
            $result[$path][] = $violation;
        }

        return $result;
    }

    /**
     * @template TKey of array-key
     * @template TVal
     *
     * @param array<TKey> $keys
     * @param array<TKey, TVal> $array
     */
    private static function assertArrayHasKeys(array $keys, array $array): void
    {
        foreach ($keys as $key) {
            self::assertArrayHasKey($key, $array);
        }
    }

    /**
     * Helper to assert that error contains violations for given fields.
     *
     * @param string[]|array<string, mixed> $expected property paths of expected violations or a map of field names to expected violation count
     */
    private static function assertRequestDtoHydrationException(RequestDtoHydrationException $e, array $expected): void
    {
        $violations = self::mapViolationsToPath($e->violations);
        self::assertCount(count($expected), $violations);
        foreach ($expected as $key => $value) {
            if (is_int($key)) {
                $key = $value;
            }

            self::assertArrayHasKey($key, $violations);

            // Only assert count when array is assoc
            if (is_int($value)) { // @phpstan-ignore-line
                self::assertCount($value, $violations[$key]); // @phpstan-ignore-line
            }
        }
    }

    /**
     * @param array<string, mixed> $expected
     */
    private static function objectMatchesExpectedStructure(object $object, array $expected): void
    {
        self::assertSame(
            $expected,
            json_decode(json_encode($object) ?: '', true),
        );
    }
}
