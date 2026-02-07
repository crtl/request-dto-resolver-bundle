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
use Symfony\Component\Validator\Constraints as Assert;

#[RequestDto]
final class StrictTypesDto
{
    #[BodyParam]
    #[Assert\NotBlank]
    public string $string;

    #[BodyParam]
    public ?string $nullableString;

    #[BodyParam]
    #[Assert\GreaterThan(0)]
    public int $int;

    #[BodyParam]
    public ?int $nullableInt;

    #[BodyParam]
    #[Assert\GreaterThan(0.0)]
    public float $float;

    #[BodyParam]
    public ?float $nullableFloat;

    #[BodyParam]
    #[Assert\NotBlank]
    public bool $bool;

    #[BodyParam]
    public ?bool $nullableBool;

    /** @var mixed[] */
    #[BodyParam]
    #[Assert\NotBlank]
    public array $array;

    /** @var mixed[]|null */
    #[BodyParam]
    public ?array $nullableArray;
}
