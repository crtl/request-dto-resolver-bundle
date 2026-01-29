<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute to resolve value for property of {@link RequestDTO} from {@link Request::$files}.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FileParam extends AbstractParam
{
    /**
     * @throws \UnexpectedValueException When FileBag::get() does not return null or `UploadedFile` instance
     */
    public function getValueFromRequest(Request $request): ?UploadedFile
    {
        $file = $request->files->get($this->getName());
        if (!is_null($file) && !$file instanceof UploadedFile) {
            throw new \UnexpectedValueException(sprintf('Expected %s to return an instance of %s but got %s instead.', get_class($request->files), UploadedFile::class, get_debug_type($file)));
        }

        return $file;
    }
}
