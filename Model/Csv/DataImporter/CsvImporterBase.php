<?php

namespace Guentur\MagentoImport\Model\Csv\DataImporter;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\DataImporter\ImporterBaseInterface;
use Guentur\MagentoImport\Model\Csv\Validator\CsvFileValidator;
use Guentur\MagentoImport\Api\Extensions\ApplyObserverInterface;
use Guentur\MagentoImport\Model\Mapper\DefaultMapping;

class CsvImporterBase implements ImporterBaseInterface
{
    const TYPE = 'csv';

    private $dataImportInfo;

    private $validator;

    private $applyObserver;

    private $mapping;

    public function __construct(
        CsvFileValidator $validator,
        ApplyObserverInterface $applyObserver,
        DefaultMapping $mapping
    ) {
        $this->validator = $validator;
        $this->applyObserver = $applyObserver;
        $this->mapping = $mapping;
    }

    /**
     * @param array $dataToInsert
     * @param string $mode
     * @return bool
     *
     * @todo Implement Mapping functionality
     */
    public function importData(array $dataToInsert, string $mode = self::MODE_ALL): bool
    {
        $pathToRecipient = $this->getDataImportInfo()->getPathToRecipient();
        $this->validator->validatePath($pathToRecipient);

        //@todo refactor for the reason to pass associative arrays with different keys and save them all to the csv file
        $resource = fopen($pathToRecipient, 'w');
        $header = $this->mapping->applyMappingForItem(array_values($dataToInsert)[0]);
        fputcsv($resource, array_keys($header));

        foreach ($dataToInsert as $row) {
            $this->mapping->applyMappingForItem($row);
            $row = $this->applyObserver->callObserver($row, $this->getDataImportInfo());
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
