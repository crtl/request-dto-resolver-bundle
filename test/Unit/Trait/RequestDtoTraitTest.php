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

namespace Crtl\RequestDtoResolverBundle\Tests\Unit\Trait;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use Crtl\RequestDtoResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RouteParam;
use Crtl\RequestDtoResolverBundle\Trait\RequestDtoTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class RequestDtoTraitTestDto
{
    use RequestDtoTrait;

    public function __construct(
        ?Request $request = null,
        public ?string $noAttr = null,
        #[QueryParam]
        public ?string $queryAttr = null,
        #[BodyParam]
        public ?string $bodyAttr = null,
        #[HeaderParam('X-Test-Header')]
        public ?string $headerAttr = null,
        #[RouteParam('id')]
        public ?string $routeAttr = null,
        #[FileParam]
        public ?UploadedFile $fileAttr = null,
    ) {
        $this->request = $request;
    }
}

final class RequestDtoTraitTest extends TestCase
{
    public function testGetValueReturnsRequestBodyValueWhenNoAttributeIsPresent(): void
    {
        $request = new Request(request: ['noAttr' => 'body-value']);
        $dto = new RequestDtoTraitTestDto($request);

        $this->assertEquals('body-value', $dto->getValue('noAttr'));
    }

    public function testGetValueReturnsQueryParamValue(): void
    {
        $request = new Request(query: ['queryAttr' => 'query-value']);
        $dto = new RequestDtoTraitTestDto($request);

        $this->assertEquals('query-value', $dto->getValue('queryAttr'));
    }

    public function testGetValueReturnsBodyParamValue(): void
    {
        $request = new Request(request: ['bodyAttr' => 'body-value']);
        $dto = new RequestDtoTraitTestDto($request);

        $this->assertEquals('body-value', $dto->getValue('bodyAttr'));
    }

    public function testGetValueReturnsHeaderParamValue(): void
    {
        $request = new Request();
        $request->headers->set('X-Test-Header', 'header-value');
        $dto = new RequestDtoTraitTestDto($request);

        $this->assertEquals('header-value', $dto->getValue('headerAttr'));
    }

    public function testGetValueReturnsRouteParamValue(): void
    {
        $request = new Request(attributes: ['_route_params' => ['id' => 'route-value']]);
        $dto = new RequestDtoTraitTestDto($request);

        $this->assertEquals('route-value', $dto->getValue('routeAttr'));
    }

    public function testGetValueReturnsFileParamValue(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $request = new Request(files: ['fileAttr' => $file]);
        $dto = new RequestDtoTraitTestDto($request);

        $this->assertSame($file, $dto->getValue('fileAttr'));
    }

    public function testGetValueThrowsLogicExceptionWhenRequestIsNull(): void
    {
        $dto = new RequestDtoTraitTestDto(null);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Request must be set before calling getValue().');

        $dto->getValue('noAttr');
    }

    public function testGetValueThrowsReflectionExceptionWhenPropertyDoesNotExist(): void
    {
        $request = new Request();
        $dto = new RequestDtoTraitTestDto($request);

        $this->expectException(\ReflectionException::class);

        $dto->getValue('nonExistentProperty');
    }
}
