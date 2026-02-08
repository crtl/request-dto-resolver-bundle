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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestDto]
class CollectionPathTestDto
{
    /**
     * @var array<int, array{key: string, value: string}>
     */
    #[BodyParam, Assert\NotBlank, Assert\Type('array'), Assert\All([
        new Assert\Type('array'),
        new Assert\Collection(fields: [
            'key' => [
                new Assert\NotBlank(),
            ],
            'value' => [
                new Assert\NotBlank(),
            ],
        ]),
    ])]
    public array $property;
}
