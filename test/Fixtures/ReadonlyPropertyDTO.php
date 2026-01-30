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

namespace Crtl\RequestDTOResolverBundle\Test\Fixtures;

use Crtl\RequestDTOResolverBundle\Attribute\BodyParam;
use Crtl\RequestDTOResolverBundle\Attribute\RequestDto;
use Symfony\Component\HttpFoundation\Request;

#[RequestDto]
class ReadonlyPropertyDTO
{
    #[BodyParam]
    public readonly ?string $readonlyParam;

    public function __construct(Request $request)
    {
        $this->readonlyParam = $request->query->getString('readonlyParam') ?: null;
    }
}
