<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract attribute class for attributes that support nested values.
 */
abstract class AbstractNestedParam extends AbstractParam
{
    abstract protected function getInputBag(Request $request): ParameterBag;

    public function getValueFromRequest(Request $request): mixed
    {
        $data = $this->getInputBag($request)->all();

        $name = $this->getName();
        $parentName = $this->parent?->getName();

        $value = $data[$parentName ?? $name] ?? null;

        if ($parentName) {
            return is_array($value) && isset($value[$name]) ? $value[$name] : null;
        }

        return $value;
    }
}
