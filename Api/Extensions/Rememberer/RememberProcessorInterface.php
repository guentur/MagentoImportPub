<?php

namespace Guentur\MagentoImport\Api\Extensions\Rememberer;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;

interface RememberProcessorInterface
{
    /**
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @return void
     */
    public function rememberEntity(int $entityKey, DataImportInfoInterface $dataImportInfo);
}
