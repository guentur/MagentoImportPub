<?php

declare(strict_types=1);

namespace Guentur\MagentoImport\Api;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;

interface DataImporterInterface
{
    const MODE_ALL = 'all';

    // @todo
    const MODE_PAGINATION = 'pagination';

    /**
     * @param array $dataToInsert
     * @param string $mode
     * @return mixed @todo
     */
    public function importData(array $dataToInsert, string $mode = self::MODE_ALL);

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
