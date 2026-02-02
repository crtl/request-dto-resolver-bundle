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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Symfony\Component\HttpFoundation\Request;

#[RequestDto]
final class RequestConstructorDTO
{
    public bool $constructorCalled = false;

    public function __construct(public readonly Request $request)
    {
        $this->constructorCalled = true;
    }

    #[BodyParam]
    public ?string $param;
}
