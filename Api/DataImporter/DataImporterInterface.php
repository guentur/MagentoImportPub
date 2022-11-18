<?php

declare(strict_types=1);

namespace Elogic\MagentoImport\Api\DataImporter;

use Elogic\MagentoImport\Api\Data\DataImportInfoInterface;

interface DataImporterInterface
{
    /**
     * @param array $dataToInsert
     * @return mixed @todo
     */
    public function importData(array $dataToInsert);

    /**
     * @param DataImportInfoInterface $dataImportInfo
     * @return void
     */
    public function setDataImportInfo(DataImportInfoInterface $dataImportInfo): void;

    /**
     * @return DataImportInfoInterface
     */
    public function getDataImportInfo(): DataImportInfoInterface;
}
