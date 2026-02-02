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
use Symfony\Component\Validator\Constraints as Assert;

trait AllTypesBodyParamsTrait
{
    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $name;

    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('int')]
    #[Assert\Positive]
    public int $age;

    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('bool')]
    public bool $isMale;

    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    #[Assert\Positive]
    public float $height;
}
