<?php

namespace Guentur\MagentoImport\Model\Database\DataImporter;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\DataImporter\ImporterRememberInterface;
use Guentur\MagentoImport\Api\Extensions\ApplyObserverInterfaceFactory;
use Guentur\MagentoImport\Api\Extensions\ImportWithProgressBarInterface;
use Guentur\MagentoImport\Api\Extensions\RememberProcessor\RememberedEntitiesProviderInterface;
use Guentur\MagentoImport\Model\Extensions\ProgressBarWrapper;
use Guentur\MagentoImport\Model\Mapper\DefaultMapping;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Guentur\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorInterface;

class DbImporterRemember implements ImportWithProgressBarInterface, ImporterRememberInterface
{
    const TYPE = 'database_remember';

    private $moduleDataSetup;

    private $rememberedEntitiesProvider;

    /**
     * @var RememberProcessorInterface
     */
    private $rememberProcessor;

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
     * @param RememberedEntitiesProviderInterface $rememberedEntitiesProvider
     * @param ApplyObserverInterfaceFactory $importObserverFactory
     * @param DefaultMapping $mapping
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        RememberedEntitiesProviderInterface $rememberedEntitiesProvider,
        ApplyObserverInterfaceFactory $importObserverFactory,
        DefaultMapping $mapping
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->rememberedEntitiesProvider = $rememberedEntitiesProvider;
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
        $dataToInsert = $this->getArraySinceRememberedEntity($dataToInsert, $this->getDataImportInfo());

        if ($this->getProgressBarWrapper() instanceof ProgressBarWrapper) {
            $this->runImportWithProgressBar($dataToInsert);
        } else {
            $this->runDefaultImport($dataToInsert);
        }
    }

    /**
     * @param array $array
     * @param DataImportInfoInterface $dataImportInfo
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getArraySinceRememberedEntity(array $array, DataImportInfoInterface $dataImportInfo): array
    {
        $rememberedEntity = $this->rememberedEntitiesProvider->getRememberedEntitiesByScope(
            $dataImportInfo,
            $this->getRememberProcessor()->getStoragePath(),
            $this->getRememberProcessor()->getStorageType(),
        );
        if (isset($rememberedEntity) && array_key_exists($rememberedEntity, $array)) {
            $array = array_slice($array, $rememberedEntity, null, true);
        }
        return $array;
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
                $importObserver->callObserver($dataItem, $this->getDataImportInfo());
                $this->importItem($dataItem);
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
                if ($dataItemKey % 2) {
                    throw new \RuntimeException('$dataItemKey % 2');
                }
                $importObserver->callObserver($dataItem, $this->getDataImportInfo());
                $this->importItem($dataItem);
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
