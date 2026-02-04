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
use Crtl\RequestDtoResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestDto]
class DtoWithDefaults
{
    #[QueryParam]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $queryParamString = 'query-param-string-default';
    #[QueryParam]
    #[Assert\NotBlank]
    #[Assert\Type('int')]
    public int $queryParamInt = 42;
    #[QueryParam]
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    public float $queryParamFloat = 3.14;
    #[QueryParam]
    #[Assert\NotBlank]
    #[Assert\Type('bool')]
    public bool $queryParamBool = true;

    /**
     * @var string[]
     */
    #[QueryParam]
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string')
    ])]
    public array $queryParamArrayNueric = ['default', 'array'];

    /**
     * @var array<string, string>
     */
    #[QueryParam]
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string')
    ])]
    public array $queryParamArrayAssoc = ['key_1' => 'value_1', 'key_2' => 'value_2'];

    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $bodyParamString = 'body-param-string-default';
    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('int')]
    public int $bodyParamInt = 42;
    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    public float $bodyParamFloat = 3.14;
    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('bool')]
    public bool $bodyParamBool = true;

    /**
     * @var string[]
     */
    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string')
    ])]
    public array $bodyParamArrayNueric = ['default', 'array'];

    /**
     * @var array<string, string>
     */
    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string')
    ])]
    public array $bodyParamArrayAssoc = ['key_1' => 'value_1', 'key_2' => 'value_2'];

    #[HeaderParam('X-Custom-Header')]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $headerParamString = 'header-param-string-default';
}
