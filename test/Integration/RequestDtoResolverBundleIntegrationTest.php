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

use Crtl\RequestDtoResolverBundle\Test\Fixtures\CollectionPathTestDto;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Controller\MixedDtoWithDefaultsController;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Controller\MultipleFilesTestController;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Controller\StrictTypesDtoController;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\DtoWithGroupSequenceProvider;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\DtoWithNestedDtoArray;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\GroupSequenceProviderDTO;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Legacy\ExampleDto;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\TypeConflictingDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestDtoResolverBundleIntegrationTest extends KernelTestCase
{
    public function testHydratesAndValidatesARequestDtoAndCallsTheController(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;
        $payload = [
            'string' => 'hello',
            'nullableString' => null,

            'int' => 123,
            'nullableInt' => null,

            'float' => 12.5,
            'nullableFloat' => null,

            'bool' => true,
            'nullableBool' => null,

            'array' => ['a' => 1, 'b' => 2],
            'nullableArray' => null,
        ];

        $request = Request::create(
            uri: '/_test',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );

        $controller = new StrictTypesDtoController();

        // no routing needed
        $request->attributes->set('_controller', $controller);

        $response = $kernel->handle($request);

        self::assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertJson($content);

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        // Values round-trip correctly
        self::assertSame('hello', $data['string']);
        self::assertNull($data['nullableString']);

        self::assertSame(123, $data['int']);
        self::assertNull($data['nullableInt']);

        // json_decode returns float as float, but equality checks are fine here
        self::assertSame(12.5, $data['float']);
        self::assertNull($data['nullableFloat']);

        self::assertTrue($data['bool']);
        self::assertNull($data['nullableBool']);

        self::assertSame(['a' => 1, 'b' => 2], $data['array']);
        self::assertNull($data['nullableArray']);
    }

    public function testReturns400WhenValidationFails(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        $payload = [
            'string' => '',      // NotBlank violation
            'int' => null,          // NotBlank considers 0 as blank -> violation (Symfony behavior)
            'float' => null,      // NotBlank considers 0.0 as blank -> violation
            'bool' => false,     // NotBlank considers false as blank -> violation
            'array' => [],       // NotBlank considers empty array as blank -> violation
        ];

        $request = Request::create(
            uri: '/_test',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );

        $controller = new StrictTypesDtoController();

        $request->attributes->set('_controller', $controller);

        // Replace with your concrete exception type:
        // e.g. \Crtl\RequestDtoResolverBundle\Exception\RequestDtoValidationException::class

        $response = $kernel->handle($request);
        var_dump($response->getContent());

        self::assertValidationErrorResponse($response, ['string', 'int', 'float', 'bool', 'array']);
    }

    public function testControllerIsCalledWithLegacyDto(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'file content');
        $file = new UploadedFile($tempFile, 'test.txt', 'text/plain', null, true);

        $payload = [
            'someParam' => 'someValue',
            'nested' => [
                'childName' => 'nestedValue',
            ],
        ];

        $request = Request::create(
            uri: '/_test?queryParamName=queryValue&nested[childName]=nestedQueryValue',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );
        $request->files->set('file', $file);
        $request->attributes->set('_route_params', ['id' => 123]);

        $controller = new class {
            public function __invoke(ExampleDto $dto): JsonResponse
            {
                return new JsonResponse([
                    'someParam' => $dto->someParam,
                    'file' => $dto->file instanceof UploadedFile ? 'uploaded' : 'not-uploaded',
                    'contentType' => $dto->contentType,
                    'query' => $dto->query,
                    'id' => $dto->id,
                    'nestedBodyDto' => $dto->nestedBodyDto?->childName,
                    'nestedQueryParamDto' => $dto->nestedQueryParamDto?->childName,
                    'requestInjected' => true,
                ]);
            }
        };

        $request->attributes->set('_controller', $controller);

        $response = $kernel->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertNotFalse($response->getContent());
        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('someValue', $data['someParam']);
        self::assertSame('uploaded', $data['file']);
        self::assertSame('application/json', $data['contentType']);
        self::assertSame('queryValue', $data['query']);
        self::assertSame(123, $data['id']);
        self::assertSame('nestedValue', $data['nestedBodyDto']);
        self::assertSame('nestedQueryValue', $data['nestedQueryParamDto']);
        self::assertTrue($data['requestInjected']);

        @unlink($tempFile);
    }

    public function testReturns400WithLegacyDtoAndInvalidData(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        // Missing someParam, file, contentType, query, id
        $payload = [];

        $request = Request::create(
            uri: '/_test',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );

        $controller = new class {
            public function __invoke(ExampleDto $dto): JsonResponse
            {
                return new JsonResponse([]);
            }
        };

        $request->attributes->set('_controller', $controller);

        $response = $kernel->handle($request);
        echo $response->getContent();

        self::assertSame(400, $response->getStatusCode());
    }

    public function testDtoWithNestedDtoArray(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        $payload = [
            'children' => [
                ['childName' => 'child1'],
                ['childName' => 'child2'],
                ['childName' => 'child3'],
            ]
        ];

        $request = Request::create(
            uri: '/_test',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );

        $controller = new class {
            public function __invoke(DtoWithNestedDtoArray $dto): JsonResponse
            {
                $children = [];
                foreach ($dto->children as $child) {
                    $children[] = ['childName' => $child->childName];
                }

                return new JsonResponse([
                    'children' => $children
                ]);
            }
        };

        $request->attributes->set('_controller', $controller);

        $response = $kernel->handle($request);
        $json = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame($payload, $json);
    }

    /**
     * @param array<string, string|null> $payload
     *
     * @dataProvider provideGroupSequenceTestData
     */
    public function testRequestDtoWithGroupSequenceProviderInterfaceHydratesAndValidatesCorrectly(array $payload, int $expectedStatus): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        $request = Request::create(
            uri: '/_test_group_sequence',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );

        $controller = new class {
            public function __invoke(GroupSequenceProviderDTO $dto): JsonResponse
            {
                return new JsonResponse(get_object_vars($dto));
            }
        };

        $request->attributes->set('_controller', $controller);

        $response = $kernel->handle($request);
        echo $response->getContent();

        self::assertSame($expectedStatus, $response->getStatusCode());

        if (200 === $expectedStatus) {
            $data = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
            foreach ($payload as $key => $value) {
                self::assertSame($value, $data[$key]);
            }
        } elseif (400 === $expectedStatus) {
            self::assertValidationErrorResponse($response);
        }
    }

    /**
     * @param array<string, string|null> $payload
     *
     * @dataProvider provideGroupSequenceTestData
     */
    public function testRequestDtoWithGroupSequenceProviderServiceHydratesAndValidatesCorrectly(array $payload, int $expectedStatus): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        $request = Request::create(
            uri: '/_test_group_sequence',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );

        $controller = new class {
            public function __invoke(DtoWithGroupSequenceProvider $dto): JsonResponse
            {
                return new JsonResponse(get_object_vars($dto));
            }
        };

        $request->attributes->set('_controller', $controller);

        $response = $kernel->handle($request);

        self::assertSame($expectedStatus, $response->getStatusCode());

        if (200 === $expectedStatus) {
            $data = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
            foreach ($payload as $key => $value) {
                self::assertSame($value, $data[$key]);
            }
        } elseif (400 === $expectedStatus) {
            self::assertValidationErrorResponse($response);
        }
    }

    /**
     * @return iterable<string, array{array<string, string|null>, int}>
     */
    public static function provideGroupSequenceTestData(): iterable
    {
        yield 'valid without triggering second group' => [
            ['first' => 'foo', 'second' => null],
            200
        ];

        yield 'valid triggering second group' => [
            ['first' => 'validate_second', 'second' => 'bar'],
            200
        ];

        yield 'invalid triggering second group' => [
            ['first' => 'validate_second', 'second' => null],
            400
        ];
    }

    public function testRequestDtoWithCollectionPathReturnsCorrectPropertyPathsInErrors(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        $request = Request::create(
            uri: '/_test_group_sequence',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'property' => [[]]
            ], JSON_THROW_ON_ERROR),
        );

        $controller = new class {
            public function __invoke(CollectionPathTestDto $dto): JsonResponse
            {
                return new JsonResponse(['fail']);
            }
        };

        $request->attributes->set('_controller', $controller);

        $response = $kernel->handle($request);
        var_dump($response->getContent());

        self::assertValidationErrorResponse($response, [
            'property[0][key]',
            'property[0][value]',
        ]);
    }

    public function testMultipleFilesDtoIsHydratedCorrectlyFromRequest(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        // Create temporary files
        $file1Path = tempnam(sys_get_temp_dir(), 'test1');
        $file2Path = tempnam(sys_get_temp_dir(), 'test2');
        $file3Path = tempnam(sys_get_temp_dir(), 'test3');

        file_put_contents($file1Path, 'content1');
        file_put_contents($file2Path, 'content2');
        file_put_contents($file3Path, 'content3');

        $file1 = new UploadedFile($file1Path, 'file1.txt', 'text/plain', null, true);
        $file2 = new UploadedFile($file2Path, 'file2.txt', 'text/plain', null, true);
        $file3 = new UploadedFile($file3Path, 'file3.txt', 'text/plain', null, true);

        $request = Request::create(
            uri: '/_test_files',
            method: 'POST',
        );

        // Populate Request::$files (FileBag)
        $request->files->set('file_one', $file1);
        $request->files->set('file_two', $file2);
        $request->files->set('file_3', $file3); // Maps to $file3 in DTO via #[FileParam("file_3")]

        $controller = new MultipleFilesTestController();
        $request->attributes->set('_controller', $controller);

        $response = $kernel->handle($request);

        self::assertSame(200, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('file1.txt', $data['file_one']);
        self::assertSame('file2.txt', $data['file_two']);
        self::assertSame('file3.txt', $data['file3']);

        // Cleanup
        @unlink($file1Path);
        @unlink($file2Path);
        @unlink($file3Path);
    }

    public function testMixedDtoWithDefaultsIsHydratedCorrectlyAndRetainsDefaults(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        $query = [
            'queryParamString' => 'custom-query-string',
            'queryParamInt' => 100,
            'queryParamFloat' => 1.23,
            'queryParamBool' => '0', // bool as string in query
            'queryParamArrayNueric' => ['q-custom'],
            'queryParamArrayAssoc' => ['key_1' => 'qv1', 'key_2' => 'qv2'],
        ];

        $body = [
            'bodyParamString' => 'custom-body-string',
            'bodyParamInt' => 77,
            'bodyParamFloat' => 1.23,
            'bodyParamBool' => false,
            'bodyParamArrayNueric' => ['custom', 'array'],
            'bodyParamArrayAssoc' => ['key_1' => 'v1', 'key_2' => 'v2'],
        ];

        $request = Request::create(
            uri: '/_test_mixed?'.http_build_query($query),
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_CUSTOM_HEADER' => 'custom-header-value',
            ],
            content: json_encode($body, JSON_THROW_ON_ERROR),
        );
        $request->headers->set('X-Custom-Header', 'custom-header-value');

        $controller = new MixedDtoWithDefaultsController();

        $request->attributes->set('_controller', $controller);

        $response = $kernel->handle($request);

        $data = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        print_r($data);
        self::assertSame(200, $response->getStatusCode());

        // Provided values
        self::assertSame('custom-query-string', $data['queryParamString']);
        self::assertSame(100, $data['queryParamInt']);
        self::assertSame(1.23, $data['queryParamFloat']);
        self::assertFalse($data['queryParamBool']);
        self::assertSame(['q-custom'], $data['queryParamArrayNueric']);
        self::assertSame(['key_1' => 'qv1', 'key_2' => 'qv2'], $data['queryParamArrayAssoc']);

        self::assertSame('custom-body-string', $data['bodyParamString']);
        self::assertSame(77, $data['bodyParamInt']);
        self::assertSame(1.23, $data['bodyParamFloat']);
        self::assertFalse($data['bodyParamBool']);
        self::assertSame(['custom', 'array'], $data['bodyParamArrayNueric']);
        self::assertSame(['key_1' => 'v1', 'key_2' => 'v2'], $data['bodyParamArrayAssoc']);

        self::assertSame('custom-header-value', $data['headerParamString']);
    }

    public function testMixedDtoWithDefaultsUsesDefaultsWhenMissingFromRequest(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        // Empty request
        $request = Request::create(
            uri: '/_test_mixed',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode([], JSON_THROW_ON_ERROR),
        );

        $controller = new MixedDtoWithDefaultsController();

        $request->attributes->set('_controller', $controller);

        $response = $kernel->handle($request);

        self::assertSame(200, $response->getStatusCode());
        $data = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('query-param-string-default', $data['queryParamString']);
        self::assertSame(42, $data['queryParamInt']);
        self::assertSame(3.14, $data['queryParamFloat']);
        self::assertTrue($data['queryParamBool']);
        self::assertSame(['default', 'array'], $data['queryParamArrayNueric']);
        self::assertSame(['key_1' => 'value_1', 'key_2' => 'value_2'], $data['queryParamArrayAssoc']);

        self::assertSame('body-param-string-default', $data['bodyParamString']);
        self::assertSame(42, $data['bodyParamInt']);
        self::assertSame(3.14, $data['bodyParamFloat']);
        self::assertTrue($data['bodyParamBool']);
        self::assertSame(['default', 'array'], $data['bodyParamArrayNueric']);
        self::assertSame(['key_1' => 'value_1', 'key_2' => 'value_2'], $data['bodyParamArrayAssoc']);

        self::assertSame('header-param-string-default', $data['headerParamString']);
    }

    public function testTypeConflictingDtoReturnsValidationErrorsWhenPropertyTypeMismatches(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        // Empty request
        $request = Request::create(
            uri: '/_test_mixed',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode([], JSON_THROW_ON_ERROR),
        );

        $controller = new class {
            public function __invoke(TypeConflictingDto $dto): JsonResponse
            {
                return new JsonResponse(get_object_vars($dto));
            }
        };

        $request->attributes->set('_controller', $controller);

        $response = $kernel->handle($request);

        self::assertSame(400, $response->getStatusCode());
        $data = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertValidationErrorResponse($response, ['arrayProperty', 'intProperty', 'floatProperty', 'boolProperty', 'stringProperty']);
    }

    /**
     * @param string[] $fields
     *
     * @throws \JsonException
     */
    private static function assertValidationErrorResponse(Response $response, array $fields = []): void
    {
        $content = $response->getContent();
        self::assertEquals(400, $response->getStatusCode());
        self::assertNotFalse($content);

        $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);

        foreach ($fields as $field) {
            self::assertArrayHasKey($field, $json['errors'], "Expected error for field $field not found");
        }
    }
}
