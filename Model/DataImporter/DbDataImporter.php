<?php

namespace Guentur\MagentoImport\Model\DataImporter;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\DataImporterInterface;
use Guentur\MagentoImport\Model\EntityScopeManager;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObject;
use Guentur\MagentoImport\Api\ImportWithProgressBarInterface;

use Guentur\MagentoImport\Model\ImportState;
use Guentur\MagentoImport\Model\Mapper\DefaultMapping;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Guentur\MagentoImport\Model\ProgressBarWrapper;

class DbDataImporter implements DataImporterInterface, ImportWithProgressBarInterface
{
    const TYPE = 'database';

    private $moduleDataSetup;

    private $importState;

    private $entityScopeManager;

    private $mapping;

    private $eventManager;

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
     * @param EntityScopeManager $entityScopeManager
     * @param DefaultMapping $mapping
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ImportState $importState,
        EntityScopeManager $entityScopeManager,
        DefaultMapping $mapping,
        ManagerInterface $eventManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->importState = $importState;
        $this->entityScopeManager = $entityScopeManager;
        $this->mapping = $mapping;
        $this->eventManager = $eventManager;
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

        $dataItem = $this->applyObserver($dataItem);
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

    //@todo Observer Interface
    //----------------- Observer Interface
    /**
     * @param array $dataItem
     * @return array
     */
    public function applyObserver(array $dataItem): array
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
     * @return string
     */
    public function getEventName(): string
    {
        //@todo add importer name part to event name
        return 'guentur_import_'
            . $this->entityScopeManager->getEntityScopeEventFormat(
                $this->getDataImportInfo()
            );
    }
    //----------------- //Observer Interface

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
