<?php

namespace Guentur\MagentoImport\Model;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\DataImporterInterface;
use Guentur\MagentoImport\Model\DataImporter\CsvDataImporter;
use Guentur\MagentoImport\Model\DataProvider\CsvDataProvider;
use Guentur\MagentoImport\Api\DataImporterPoolInterface;
use Guentur\MagentoImport\Api\DataProviderPoolInterface;
use Guentur\MagentoImport\Model\EntityManager;
use Guentur\MagentoImport\Model\Solver\StorageSolverProvider;
use Guentur\MagentoImport\Model\EntityScopeManager;
use Guentur\MagentoImport\Api\Data\DataImportInfoInterfaceFactory;

class ImportState
{
    // @todo setup only filename. Make absolute path by function like getMediaPath() in Magento
    const IMPORT_STATE_FILE_NAME = __DIR__ . '/../etc/import_state.csv';

    private $dataImporterPool;

    private $dataProviderPool;

    private $entityManager;

    private $storageSolverProvider;

    private $entityScopeManager;

    private $rememberedEntitiesStorageType;

    private $rememberedEntitiesStoragePath;

    /**
     * @var DataImportInfoInterfaceFactory
     */
    private $dataImportInfoF;

    public function __construct(
        DataImporterPoolInterface $dataImporterPool,
        DataProviderPoolInterface $dataProviderPool,
        EntityManager $entityManager,
        StorageSolverProvider $storageSolverProvider,
        DataImportInfoInterfaceFactory $dataImportInfoF,
        EntityScopeManager $entityScopeManager,
        string $rememberedEntitiesStorageType,
        string $rememberedEntitiesStoragePath
    ) {
        $this->dataImporterPool = $dataImporterPool;
        $this->dataProviderPool = $dataProviderPool;
        $this->entityManager = $entityManager;
        $this->storageSolverProvider = $storageSolverProvider;
        $this->dataImportInfoF = $dataImportInfoF;
        $this->entityScopeManager = $entityScopeManager;
        $this->rememberedEntitiesStorageType = $rememberedEntitiesStorageType;
        $this->rememberedEntitiesStoragePath = $rememberedEntitiesStoragePath;
    }

    /**
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @return void
     */
    public function rememberEntity(int $entityKey, DataImportInfoInterface $dataImportInfo)
    {
        $pathToRecipient = $dataImportInfo->getPathToRecipient();
        $pathToProvider = $dataImportInfo->getPathToDataProvider();
        $currentEntityInfo = [
                'path_to_provider' => $pathToProvider,
                'path_to_recipient' => $pathToRecipient,
                'entity_key' => (int) $entityKey,
            ];
        $rememberedEntities = $this->getRememberedEntities();
        $rememberedEntities[] = $currentEntityInfo;

        $scopeFormatEntityList = $this->entityManager->getScopeFormatEntityList($rememberedEntities);
        $dataForImport = $this->entityManager->getImportFormatEntityList($scopeFormatEntityList);

        /** DataImportInfoInterface $dataImportInfo */
        $dataImportInfo = $this->dataImportInfoF->create();
        $dataImportInfo->setPathToDataProvider($pathToProvider);
        $dataImportInfo->setPathToRecipient($pathToRecipient);

        /** @var \Guentur\MagentoImport\Api\DataImporterInterface $dataImporter */
        $dataImporter = $this->dataImporterPool->getDataImporter($this->rememberedEntitiesStorageType);
        $dataImporter->setDataImportInfo($dataImportInfo);
        $dataImporter->importData($dataForImport);
    }

    public function getRememberedEntities(): array
    {
        $rememberedEntities = [];
        //@todo
        try {
            $dataProvider = $this->dataProviderPool->getDataProvider($this->rememberedEntitiesStorageType);
            $rememberedEntities = $dataProvider->getData($this->rememberedEntitiesStoragePath);
        } catch(\InvalidArgumentException $e) {
            //@todo create the file for remembering entities if it is not exist
            $solver = $this->storageSolverProvider->getSolver($this->rememberedEntitiesStorageType);
            $solver->execute($this->rememberedEntitiesStoragePath);
            $message = __(' We cannot access to storage for remembered entities.'
                          . ' The storage provider returned this message: ' . $e->getMessage()
                          . ' The solver script have been run.'
                          . ' You can configure your class with solver script in the di.xml config.'
                          . ' See node type for class Guentur\MagentoImport\Model\Solver\StorageSolverProvider');
            echo $message;
        }
        return $rememberedEntities;
    }

    /**
     * @param array $array
     * @param DataImportInfoInterface $dataImportInfo
     * @return array
     */
    public function getArraySinceRememberedEntity(array $array, DataImportInfoInterface $dataImportInfo): array
    {
        $rememberedEntity = $this->getRememberedEntity($dataImportInfo);
        if (isset($rememberedEntity)) {
            $array = array_slice($array, $rememberedEntity);
        }
        return $array;
    }

    public function getRememberedEntity(DataImportInfoInterface $dataImportInfo)
    {
        //@todo optimize
        $rememberedEntities = $this->getRememberedEntities();

        $formattedEntityList = $this->entityManager->getScopeFormatEntityList($rememberedEntities);
        $entityScope = $this->entityScopeManager->getEntityScope($dataImportInfo);

        return array_key_exists($entityScope, $formattedEntityList) ? $formattedEntityList[$entityScope] : null;
    }
}
