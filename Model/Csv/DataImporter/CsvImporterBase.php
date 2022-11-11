<?php

namespace Guentur\MagentoImport\Model\Csv\DataImporter;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\DataImporter\ImporterBaseInterface;
use Guentur\MagentoImport\Model\Csv\Validator\CsvFileValidator;
use Guentur\MagentoImport\Api\Extensions\ApplyObserverInterfaceFactory;
use Guentur\MagentoImport\Model\Mapper\DefaultMapping;

class CsvImporterBase implements ImporterBaseInterface
{
    const TYPE = 'csv';

    private $dataImportInfo;

    private $validator;

    private $importObserverFactory;

    private $mapping;

    public function __construct(
        CsvFileValidator $validator,
        ApplyObserverInterfaceFactory $importObserverFactory,
        DefaultMapping $mapping
    ) {
        $this->validator = $validator;
        $this->importObserverFactory = $importObserverFactory;
        $this->mapping = $mapping;
    }

    /**
     * @param array $dataForImport
     * @return bool
     *
     * @todo Ask Alexander: "Is it right to pass dataForImport into Guentur\MagentoImport\Model\Data\DataImportInfo
     * I think it is the right thing, because the data for importing should transfer with information where it is transfering
     */
    public function importData(array $dataForImport): bool
    {
        $pathToRecipient = $this->getDataImportInfo()->getPathToRecipient();
        $this->validator->validatePath($pathToRecipient);

        //@todo refactor for the reason to pass associative arrays with different keys and save them all to the csv file
        $resource = fopen($pathToRecipient, 'w');
        //@todo refactor. Separate DataModel and Buiseness Logic Model in mapping.
        // We should use MappingFactory for non-injectable DataModels
        $header = $this->mapping->applyMappingForItem(array_values($dataForImport)[0]);
        fputcsv($resource, array_keys($header));

        $importObserver = $this->importObserverFactory->create();

        foreach ($dataForImport as $row) {
            $this->mapping->applyMappingForItem($row);
            //@todo refactor. Separate DataModel and Buiseness Logic Model in mapping.
            // We should use ImportObserverFactory for non-injectable DataModels
            $row = $importObserver->callObserver($row, $this->getDataImportInfo());
            fputcsv($resource, $row);
        }
        $status = fclose($resource);

        return $status;
    }

    public function runImport(array $dataForImport): iterable
    {
        // TODO: Implement runImport() method.
        yield 1;
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
