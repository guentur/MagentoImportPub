<?php

namespace Guentur\MagentoImport\Model\Csv\Validator;

use Guentur\MagentoImport\Model\Exception\InvalidFileExtensionException;
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

        // @todo why it find file that does not exist (command example in the saved notes in the telegram group)
        if (!file_exists($dataProviderPath)) {
//            throw new \InvalidArgumentException('File ' . $dataProviderPath . ' does not exist');
            throw new FileNotFoundException(__('File "%s" could not be found.', $dataProviderPath));
        }
    }
}
