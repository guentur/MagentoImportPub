<?php

namespace Guentur\MagentoImport\Model\Csv\DataImporter;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\DataImporter\ImporterBaseInterface;
use Guentur\MagentoImport\Model\Csv\Validator\CsvFileValidator;
use Guentur\MagentoImport\Api\Extensions\ApplyObserverInterface;
use Magento\Framework\DataObject;

class CsvImporterBase implements ImporterBaseInterface, ApplyObserverInterface
{
    const TYPE = 'csv';

    private $dataImportInfo;

    private $validator;

    public function __construct(
        CsvFileValidator $validator
    ) {
        $this->validator = $validator;
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
        fputcsv($resource, array_keys(array_values($dataToInsert)[0]));
        foreach ($dataToInsert as $row) {
            fputcsv($resource, $row);
        }
        $status = fclose($resource);

        return $status;
    }

    public function getEventName(): string
    {
        //@todo add importer name part to event name
        return 'guentur_import_'
            . $this->entityScopeManager->getEntityScopeEventFormat(
                $this->getDataImportInfo()
            );
    }

    public function callObserver(array $dataItem): array
    {
        $dataItemObject = new DataObject($dataItem);
        // if there is error throw \RuntimeException
        $this->eventManager->dispatch(
            $this->getEventName(),
            [
                'data_item' => $dataItemObject
            ]
        );
        return $dataItemObject->getData();
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
