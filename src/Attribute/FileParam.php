<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FileParam extends AbstractParam
{

    public function getValueFromRequest(Request $request): ?UploadedFile
    {
        return $request->files->get($this->name);
    }

}