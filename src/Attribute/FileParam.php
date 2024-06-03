<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute to resolve value for property of {@link RequestDTO} from {@link Request::$files}
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class FileParam extends AbstractParam
{

    /**
     * @inheritDoc
     */
    public function getValueFromRequest(Request $request): ?UploadedFile
    {
        return $request->files->get($this->getName());
    }

}
