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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures\Controller;

use Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\StrictTypesDto;
use Symfony\Component\HttpFoundation\JsonResponse;

class StrictTypesDtoController
{
    public function __invoke(StrictTypesDto $dto): JsonResponse
    {
        return new JsonResponse(get_object_vars($dto));
    }
}
