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

/**
 * Test dto which has all types of properties with default values using mixed and php doc to declare type.
 */
#[RequestDto]
class MixedDtoWithDefaults
{
    /**
     * @var mixed|string
     */
    #[QueryParam]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public mixed $queryParamString = 'query-param-string-default';
    /**
     * @var mixed|int
     */
    #[QueryParam()]
    #[Assert\NotBlank]
    #[Assert\Type('int')]
    public mixed $queryParamInt = 42;
    /**
     * @var mixed|float
     */
    #[QueryParam]
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    public mixed $queryParamFloat = 3.14;
    /**
     * @var mixed|bool
     */
    #[QueryParam(transformType: 'bool')]
    #[Assert\Type('bool')]
    public mixed $queryParamBool = true;

    /**
     * @var mixed|string[]
     */
    #[QueryParam]
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string')
    ])]
    public mixed $queryParamArrayNueric = ['default', 'array'];

    /**
     * @var mixed|array<string, string>
     */
    #[QueryParam]
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\Collection([
        'key_1' => [
            new Assert\NotBlank(),
            new Assert\Type('string'),
        ],
        'key_2' => [
            new Assert\NotBlank(),
            new Assert\Type('string'),
        ],
    ])]
    #[Assert\All([
        new Assert\Type('string')
    ])]
    public mixed $queryParamArrayAssoc = ['key_1' => 'value_1', 'key_2' => 'value_2'];

    /**
     * @var mixed|string
     */
    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public mixed $bodyParamString = 'body-param-string-default';
    /**
     * @var mixed|int
     */
    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('int')]
    public mixed $bodyParamInt = 42;
    /**
     * @var mixed|float
     */
    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    public mixed $bodyParamFloat = 3.14;
    /**
     * @var mixed|bool
     */
    #[BodyParam]
    #[Assert\Type('bool')]
    public mixed $bodyParamBool = true;

    /**
     * @var mixed|string[]
     */
    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string')
    ])]
    public mixed $bodyParamArrayNueric = ['default', 'array'];

    /**
     * @var mixed|array<string, string>
     */
    #[BodyParam]
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\Collection([
        'key_1' => [
            new Assert\NotBlank(),
            new Assert\Type('string'),
        ],
        'key_2' => [
            new Assert\NotBlank(),
            new Assert\Type('string'),
        ],
    ])]
    #[Assert\All([
        new Assert\Type('string')
    ])]
    public mixed $bodyParamArrayAssoc = ['key_1' => 'value_1', 'key_2' => 'value_2'];

    /**
     * @var mixed|string
     */
    #[HeaderParam('X-Custom-Header')]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public mixed $headerParamString = 'header-param-string-default';
}
