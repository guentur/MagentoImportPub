<?php

namespace ElogicCo\MagentoImport\Api\Extensions;

use ElogicCo\MagentoImport\Api\Data\DataImportInfoInterface;

interface ApplyObserverInterface
{
    /**
     * @param array $dataItem
     * @param DataImportInfoInterface $dataImportInfo
     * @return array
     */
    public function callObserver(array $dataItem, DataImportInfoInterface $dataImportInfo): array;

    /**
     * @param DataImportInfoInterface $dataImportInfo
     * @return string
     */
    public function getFullEventName(DataImportInfoInterface $dataImportInfo): string;
}
