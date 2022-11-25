<?php

namespace ElogicCo\MagentoImport\Model\Database\DataImporter;

use ElogicCo\MagentoImport\Api\Data\DataImportInfoInterface;
use ElogicCo\MagentoImport\Api\DataImporter\ImporterRememberInterface;
use ElogicCo\MagentoImport\Api\Extensions\ApplyObserverInterfaceFactory;
use ElogicCo\MagentoImport\Api\Extensions\ImportWithProgressBarInterface;
use ElogicCo\MagentoImport\Model\Extensions\ProgressBarWrapper;
use ElogicCo\MagentoImport\Model\Mapper\DefaultMapping;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use ElogicCo\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorInterface;

class DbImporterRemember implements ImportWithProgressBarInterface, ImporterRememberInterface
{
    const TYPE = 'database_remember';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var RememberProcessorInterface
     */
    private $rememberProcessor;

    /**
     * @var ApplyObserverInterfaceFactory
     */
    private $importObserverFactory;

    /**
     * @var DefaultMapping
     */
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

    /**
     * @param array $dataToInsert
     * @return mixed|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importData(
        array $dataToInsert
    ) {
        if ($this->getProgressBarWrapper() instanceof ProgressBarWrapper) {
            $this->runImportWithProgressBar($dataToInsert);
        } else {
            $this->runDefaultImport($dataToInsert);
        }
    }

    /**
     * @param array $dataToInsert
     * @return void
     */
    public function runDefaultImport(array $dataToInsert)
    {
        $importObserver = $this->importObserverFactory->create();
        foreach ($dataToInsert as $dataItemKey => $dataItem) {
            try {
                $dataItem = $importObserver->callObserver($dataItem, $this->getDataImportInfo());
                $this->importItem($dataItem);
                // if entity was imported successfully we should delete it from list of broken entities
                $this->getRememberProcessor()->forgetEntity($dataItemKey, $this->getDataImportInfo());
            } catch (\RuntimeException|\Exception $e) {
                $this->getRememberProcessor()->rememberEntity($dataItemKey, $this->getDataImportInfo(), $e);
            }
        }
    }

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

    // ------------------- RememberLogic interface

    public function getRememberProcessor(): RememberProcessorInterface
    {
        return $this->rememberProcessor;
    }

    public function setRememberProcessor(RememberProcessorInterface $rememberProcessor)
    {
        $this->rememberProcessor = $rememberProcessor;
    }
    // ------------------- RememberLogic interface

    // ---------------- ImportWithProgressBarInterface
    /**
     * @param array $dataToInsert
     * @return void
     */
    public function runImportWithProgressBar(array $dataToInsert)
    {
        $progressBar = $this->getProgressBarWrapper()->getProgressBarInstance(count($dataToInsert));
        $importObserver = $this->importObserverFactory->create();
        $progressBar->start();
        foreach ($dataToInsert as $dataItemKey => $dataItem) {
            $progressBar->display();
            try {
                $dataItem = $importObserver->callObserver($dataItem, $this->getDataImportInfo());
                $this->importItem($dataItem);
                // if entity was imported successfully we should delete it from list of broken entities
                $this->getRememberProcessor()->forgetEntity($dataItemKey, $this->getDataImportInfo());
            } catch (\RuntimeException|\Exception $e) {
                $this->getRememberProcessor()->rememberEntity($dataItemKey, $this->getDataImportInfo(), $e);
            }
            $progressBar->advance();
        }
        $progressBar->finish();
    }

    /**
     * @param ProgressBarWrapper $progressBarWrapper
     * @return void
     */
    public function setProgressBarWrapper(ProgressBarWrapper $progressBarWrapper)
    {
        $this->progressBarWrapper = $progressBarWrapper;
    }

    /**
     * @todo test if it returns NULL
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
