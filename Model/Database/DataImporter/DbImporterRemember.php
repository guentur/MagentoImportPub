<?php

namespace Guentur\MagentoImport\Model\Database\DataImporter;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\DataImporter\ImporterRememberInterface;
use Guentur\MagentoImport\Api\Extensions\ImportWithProgressBarInterface;
use Guentur\MagentoImport\Model\EntityScopeManager;
use Guentur\MagentoImport\Model\ImportState;
use Guentur\MagentoImport\Model\Mapper\DefaultMapping;
use Guentur\MagentoImport\Model\ProgressBarWrapper;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Guentur\MagentoImport\Api\Extensions\ApplyObserverInterface;

class DbImporterRemember implements ImportWithProgressBarInterface, ImporterRememberInterface
{
    const TYPE = 'database_remember';

    private $moduleDataSetup;

    private $importState;

    private $applyObserver;

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
     * @param ImportState $importState
     * @param ApplyObserverInterface $applyObserver
     * @param DefaultMapping $mapping
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ImportState $importState,
        ApplyObserverInterface $applyObserver,
        DefaultMapping $mapping
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->importState = $importState;
        $this->applyObserver = $applyObserver;
        $this->mapping = $mapping;
    }

    /**
     * @param array $dataToInsert
     * @param string $mode
     * @return mixed|void
     */
    public function importData(
        array $dataToInsert,
        string $mode = self::MODE_ALL
    ) {
        $dataToInsert = $this->importState->getArraySinceRememberedEntity($dataToInsert, $this->getDataImportInfo());

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
        foreach ($dataToInsert as $dataItemKey => $dataItem) {
            try {
                $this->importItem($dataItem);
            } catch (\RuntimeException|\Exception $e) {
                $this->importState->rememberEntity($dataItemKey, $this->getDataImportInfo());
                throw $e;
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

        $dataItem = $this->applyObserver->callObserver($dataItem, $this->getDataImportInfo());
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
    /**
     * @param array $dataToInsert
     * @return void
     */
    public function runImportWithProgressBar(array $dataToInsert)
    {
        $progressBar = $this->getProgressBarWrapper()->getProgressBarInstance(count($dataToInsert));
        $progressBar->start();
        foreach ($dataToInsert as $dataItemKey => $dataItem) {
            $progressBar->display();
            try {
                $this->importItem($dataItem);
            } catch (\RuntimeException|\Exception $e) {
                $this->importState->rememberEntity($dataItemKey, $this->getDataImportInfo());
                throw $e;
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
