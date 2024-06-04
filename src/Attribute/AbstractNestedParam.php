<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract attribute class for attributes that support nested values
 */
abstract class AbstractNestedParam extends AbstractParam
{

    abstract protected function getInputBag(Request $request): InputBag;

    /**
     * @param Request $request
     * @param AbstractParam|null $parent
     * @inheritDoc
     */
    public function getValueFromRequest(Request $request): mixed
    {
        $data = $this->getInputBag($request)->all();

        $name = $this->getName();
        $parentName = $this->parent?->getName();

        $value = $data[$parentName ?? $name];

        if ($parentName) {
            if (is_array($value)) {
                return $value[$name] ?? null;
            } elseif (is_object($value)) {
                return $value->{$name} ?? null;
            }
            return null;
        }

        return $value;
    }

}
