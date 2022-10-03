<?php

namespace Guentur\MagentoImport\Model\Database\DataImporter;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\DataImporter\DataImporterInterface;
use Guentur\MagentoImport\Api\DataImporter\ImporterBaseInterface;
use Guentur\MagentoImport\Api\ImportWithProgressBarInterface;
use Guentur\MagentoImport\Model\EntityScopeManager;
use Guentur\MagentoImport\Model\Mapper\DefaultMapping;
use Guentur\MagentoImport\Model\ProgressBarWrapper;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class DbImporterBase implements DataImporterInterface, ImportWithProgressBarInterface, ImporterBaseInterface
{
    const TYPE = 'database';

    private $moduleDataSetup;

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
     * @param EntityScopeManager $entityScopeManager
     * @param DefaultMapping $mapping
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EntityScopeManager $entityScopeManager,
        DefaultMapping $mapping,
        ManagerInterface $eventManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
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
            $this->importItem($dataItem);
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
            $this->importItem($dataItem);
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
