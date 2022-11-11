<?php

declare(strict_types=1);

namespace Guentur\MagentoImport\Api\DataImporter;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Model\Exception\ImportException;

interface DataImporterInterface
{
//    /**
// @todo change this method with runImport() method
//     * @param array $dataForImport
//     * @return mixed @todo
//     */
//    public function importData(array $dataForImport);

    /**
     * @param DataImportInfoInterface $dataImportInfo
     * @return void
     */
    public function setDataImportInfo(DataImportInfoInterface $dataImportInfo): void;

    /**
     * @return DataImportInfoInterface
     */
    public function getDataImportInfo(): DataImportInfoInterface;

    /**
     * Realize this function as a generator
     *
     * @param array $dataForImport
     * @return iterable $dataItemKey
     * @throw \Guentur\MagentoImport\Model\Exception\ImportException
     */
    public function runImport(array $dataForImport): iterable;
}
