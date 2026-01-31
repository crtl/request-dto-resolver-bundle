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

namespace Crtl\RequestDTOResolverBundle\Test\Fixtures\Controller;

use Crtl\RequestDTOResolverBundle\Test\Fixtures\StrictTypesDTO;
use Symfony\Component\HttpFoundation\JsonResponse;

final class TestController
{
    public function __invoke(StrictTypesDTO $dto): JsonResponse
    {
        return new JsonResponse(get_object_vars($dto));
    }
}
