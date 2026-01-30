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

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract attribute class for attributes that support nested values.
 */
abstract class AbstractNestedParam extends AbstractParam
{
    abstract protected function getInputBag(Request $request): ParameterBag;

    /**
     * @return array<string, mixed>
     */
    protected function getDataFromRequest(Request $request): array
    {
        return $this->getInputBag($request)->all();
    }

    public function getValueFromRequest(Request $request): mixed
    {
        $data = $this->getDataFromRequest($request);

        $name = $this->getName();
        $parentName = $this->parent?->getName();

        $value = $data[$parentName ?? $name] ?? null;

        if ($parentName) {
            $value = is_array($value) && isset($value[$name]) ? $value[$name] : null;
        }

        return $value;
    }
}
