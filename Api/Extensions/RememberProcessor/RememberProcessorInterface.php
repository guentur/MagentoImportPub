<?php

namespace Guentur\MagentoImport\Api\Extensions\RememberProcessor;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\DataImporter\DataImporterInterface;

interface RememberProcessorInterface
{
    /**
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @return mixed
     */
    public function rememberEntity(int $entityKey, DataImportInfoInterface $dataImportInfo);

    /**
     * @param array $array
     * @param DataImportInfoInterface $dataImportInfo
     * @return array
     */
    public function getArraySinceRememberedEntity(array $array, DataImportInfoInterface $dataImportInfo): array;

    /**
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @return mixed
     */
    public function forgetEntity(int $entityKey, DataImportInfoInterface $dataImportInfo);

    /**
     * @param array $dataForImport
     * @param DataImporterInterface $dataImporter
     * @return mixed
     */
    public function importData(array $dataForImport, DataImporterInterface $dataImporter);
}
