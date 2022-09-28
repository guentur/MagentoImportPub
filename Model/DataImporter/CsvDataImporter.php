<?php

namespace Guentur\MagentoImport\Model\DataImporter;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\DataImporterInterface;

class CsvDataImporter implements DataImporterInterface
{
    const TYPE = 'csv';

    private $dataImportInfo;

    /**
     * @param array $dataToInsert
     * @param string $mode
     * @return bool
     *
     * @todo Make validation
     * @todo Apply mapping
     */
    public function importData(array $dataToInsert, string $mode = self::MODE_ALL): bool {
        //@todo refactor for the reason to pass associative arrays with different keys and save them all to the csv file
        //@todo Implement Mapping functionality
        $resource = fopen($this->getDataImportInfo()->getPathToRecipient(), 'w');
        fputcsv($resource, array_keys(array_values($dataToInsert)[0]));
        foreach ($dataToInsert as $row) {
            fputcsv($resource, $row);
        }
        $status = fclose($resource);

        return $status;
    }

    /**
     * @param DataImportInfoInterface $dataImportInfo
     * @return void
     */
    public function setDataImportInfo(DataImportInfoInterface $dataImportInfo): void
    {
        $this->dataImportInfo = $dataImportInfo;
    }

    /**
     * @return DataImportInfoInterface
     */
    public function getDataImportInfo(): DataImportInfoInterface
    {
        return $this->dataImportInfo;
    }
}
