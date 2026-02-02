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

namespace Crtl\RequestDtoResolverBundle\Trait;

use Crtl\RequestDtoResolverBundle\Attribute\AbstractParam;
use Symfony\Component\HttpFoundation\Request;

trait RequestDtoTrait
{
    public function __construct(
        private readonly Request $request
    ) {
    }

    /**
     * Helper to get values from request before hydration.
     *
     * !!!WARNING!!!: This does not for nested dtos.
     *
     * @throws \ReflectionException
     */
    public function getValue(string $property): mixed
    {
        $reflectionClass = new \ReflectionClass($this);
        $reflectionProperty = $reflectionClass->getProperty($property);

        $attrs = $reflectionProperty->getAttributes(AbstractParam::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (empty($attrs)) {
            return $this->request->request->get($property);
        }

        /** @var AbstractParam $attr */
        $attr = $attrs[0]->newInstance();
        $attr->setProperty($reflectionProperty);

        return $attr->getValueFromRequest($this->request);
    }
}
