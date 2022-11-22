<?php

namespace ElogicCo\MagentoImport\Model\Csv\Validator;

use ElogicCo\MagentoImport\Model\Exception\InvalidFileExtensionException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class CsvFileValidator
{
    public function validatePath(string $dataProviderPath)
    {
        $allowedExtensions = array('csv');
        $ext = pathinfo($dataProviderPath, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowedExtensions)) {
            throw new InvalidFileExtensionException(null, 0, null, $dataProviderPath, $ext, $allowedExtensions);
        }

        if (!file_exists($dataProviderPath)) {
//            throw new \InvalidArgumentException('File ' . $dataProviderPath . ' does not exist');
            throw new FileNotFoundException(__('File "%1" could not be found.', $dataProviderPath));
        }
    }
}
