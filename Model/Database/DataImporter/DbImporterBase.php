<?php

namespace Guentur\MagentoImport\Model\Database\DataImporter;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\DataImporter\ImporterBaseInterface;
use Guentur\MagentoImport\Api\Extensions\ApplyObserverInterfaceFactory;
use Guentur\MagentoImport\Api\Extensions\ImportWithProgressBarInterface;
use Guentur\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorInterface;
use Guentur\MagentoImport\Model\Exception\ImportException;
use Guentur\MagentoImport\Model\Extensions\ProgressBarWrapper;
use Guentur\MagentoImport\Model\Mapper\DefaultMapping;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class DbImporterBase implements ImportWithProgressBarInterface, ImporterBaseInterface
{
    const TYPE = 'database';

    private $moduleDataSetup;

    /**
     * @var ApplyObserverInterfaceFactory
     */
    private $importObserverFactory;

    private $mapping;

    /**
     * @var
     */
    private $dataImportInfo;

    /**
     * AdapterInterface $dbAdapter
     */
    private $dbAdapter;

    /**
     * string $tableName
     */
    private $tableName;

    private $progressBarWrapper;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ApplyObserverInterfaceFactory $importObserverFactory
     * @param DefaultMapping $mapping
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ApplyObserverInterfaceFactory $importObserverFactory,
        DefaultMapping $mapping
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->importObserverFactory = $importObserverFactory;
        $this->mapping = $mapping;
    }

    ////--------------- @todo Change with runImport
//    /**
//     * @param array $dataForImport
//     * @return mixed|void
//     * @throws \Magento\Framework\Exception\LocalizedException
//     */
//    public function importData(array $dataForImport)
//    {
//        if ($this->getProgressBarWrapper() instanceof ProgressBarWrapper) {
//            $this->runImportWithProgressBar($dataForImport);
//        } else {
//            $this->runDefaultImport($dataForImport);
//        }
//    }
    ///-----------------------

    /**
     * Realize this function as a generator
     *
     * @param array $dataForImport
     * @return iterable $dataItemKey
     * @throw \Guentur\MagentoImport\Model\Exception\ImportException
     */
    public function runImport(array $dataForImport): iterable
    {
        $progressBar = $this->getProgressBarWrapper()->getProgressBarInstance(count($dataForImport));
        $importObserver = $this->importObserverFactory->create();
        $progressBar->start();
        foreach ($dataForImport as $dataItemKey => $dataItem) {
            $progressBar->display();
            try {
                $importObserver->callObserver($dataItem, $this->getDataImportInfo());
                $this->importItem($dataItem);
                yield $dataItemKey;
            } catch (\Throwable $exception) {
                throw new ImportException($dataItemKey, $exception->getMessage(), $exception->getCode(), $exception);
            }
            $progressBar->advance();
        }
        $progressBar->finish();
    }

//    /**
//     * @param array $dataForImport
//     * @return void
//     */
//    public function runDefaultImport(array $dataForImport)
//    {
//        $importObserver = $this->importObserverFactory->create();
//        foreach ($dataForImport as $dataItemKey => $dataItem) {
//            try {
//                $importObserver->callObserver($dataItem, $this->getDataImportInfo());
//                $this->importItem($dataItem);
//                yield $dataItemKey;
//            } catch (\Throwable $exception) {
//                throw new ImportException($dataItemKey, $exception->getMessage(), $exception->getCode(), $exception);
//            }
//        }
//    }

    /**
     * @param $dataItem
     * @return void
     */
    private function importItem($dataItem)
    {
        $tableName = $this->getTableName();
        $dbAdapter = $this->getConnection();

        $this->mapping->applyMappingForItem($dataItem);
        $dbAdapter->insertOnDuplicate($tableName, $dataItem);
    }

    /**
     * @return AdapterInterface
     */
    protected function getConnection(): AdapterInterface
    {
        if ($this->dbAdapter === null) {
            $this->dbAdapter = $this->moduleDataSetup->getConnection();
        }
        return $this->dbAdapter;
    }

    /**
     * @return string
     */
    protected function getTableName(): string
    {
        if ($this->tableName === null) {
            $this->tableName = $this->moduleDataSetup->getTable($this->getDataImportInfo()->getPathToRecipient());
        }
        return $this->tableName;
    }

    // ---------------- ImportWithProgressBarInterface
//    /**
//     * @param array $dataForImport
//     * @return void
//     */
//    public function runImportWithProgressBar(array $dataForImport)
//    {
//        $progressBar = $this->getProgressBarWrapper()->getProgressBarInstance(count($dataForImport));
//        $importObserver = $this->importObserverFactory->create();
//        $progressBar->start();
//        foreach ($dataForImport as $dataItemKey => $dataItem) {
//            $progressBar->display();
//            try {
//                if ($dataItemKey % 2) {
//                    throw new \RuntimeException('$dataItemKey % 2');
//                }
//                $importObserver->callObserver($dataItem, $this->getDataImportInfo());
//                $this->importItem($dataItem);
//                // if entity was imported successfully we should delete it from list of broken entities
//                $this->getRememberProcessor()->forgetEntity($dataItemKey, $this->getDataImportInfo());
//            } catch (\RuntimeException|\Exception $e) {
//                $this->getRememberProcessor()->rememberEntity($dataItemKey, $this->getDataImportInfo(), $e);
//            }
//            $progressBar->advance();
//        }
//        $progressBar->finish();
//    }

    /**
     * @param ProgressBarWrapper $progressBarWrapper
     * @return void
     */
    public function setProgressBarWrapper(ProgressBarWrapper $progressBarWrapper)
    {
        $this->progressBarWrapper = $progressBarWrapper;
    }

    /**
     * @return ProgressBarWrapper
     */
    public function getProgressBarWrapper(): ProgressBarWrapper
    {
        return $this->progressBarWrapper;
    }
    // ---------------- //ImportWithProgressBarInterface

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
