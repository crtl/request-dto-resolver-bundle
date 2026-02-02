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

namespace Crtl\RequestDtoResolverBundle\Attribute;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract attribute class for attributes that support nested values.
 */
abstract class AbstractNestedParam extends AbstractParam
{
    protected ?int $index = null;

    abstract protected function getInputBag(Request $request): ParameterBag;

    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    public function getIndex(): ?int
    {
        return $this->index;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDataFromRequest(Request $request): array
    {
        return $this->getInputBag($request)->all();
    }

    public function getValueFromRequest(Request $request): mixed
    {
        $name = $this->getName();

        if ($this->parent) {
            $data = $this->parent->getValueFromRequest($request);
        } else {
            $data = $this->getDataFromRequest($request);
        }

        $defaultValue = null; // $this->property?->getDefaultValue();

        $value = $data[$name] ?? $defaultValue;

        $index = $this->getIndex();

        if (null !== $index) {
            $value = is_array($value) && isset($value[$index]) ? $value[$index] : null;
        }

        return $value;
    }
}
