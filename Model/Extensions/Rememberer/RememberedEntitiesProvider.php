<?php

namespace Guentur\MagentoImport\Model\Extensions\Rememberer;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\Data\DataImportInfoInterfaceFactory;
use Guentur\MagentoImport\Api\DataImporter\DataImporterPoolInterface;
use Guentur\MagentoImport\Api\DataProvider\DataProviderPoolInterface;
use Guentur\MagentoImport\Api\Extensions\Rememberer\RememberedEntitiesProviderInterface;
use Guentur\MagentoImport\Model\EntityManager;
use Guentur\MagentoImport\Model\EntityScopeManager;
use Guentur\MagentoImport\Model\Solver\StorageSolverPool;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class RememberedEntitiesProvider implements RememberedEntitiesProviderInterface
{
    /**
     * @var DataProviderPoolInterface
     */
    private $dataProviderPool;

    /**
     * @var DataImportInfoInterfaceFactory
     */
    private $dataImportInfoF;

    /**
     * @var DataImporterPoolInterface
     */
    private $dataImporterPool;

    /**
     * @var EntityManager `
     */
    private $entityManager;

    /**
     * @var StorageSolverPool
     */
    private $storageSolverPool;

    /**
     * @var EntityScopeManager
     */
    private $entityScopeManager;

    /**
     * @param DataImporterPoolInterface $dataImporterPool
     * @param DataImportInfoInterfaceFactory $dataImportInfoF
     * @param DataProviderPoolInterface $dataProviderPool
     * @param EntityManager $entityManager
     * @param StorageSolverPool $storageSolverPool
     * @param EntityScopeManager $entityScopeManager
     */
    public function __construct(
        DataImporterPoolInterface $dataImporterPool,
        DataImportInfoInterfaceFactory $dataImportInfoF,
        DataProviderPoolInterface $dataProviderPool,
        EntityManager $entityManager,
        StorageSolverPool $storageSolverPool,
        EntityScopeManager $entityScopeManager
    ) {
        $this->dataImporterPool = $dataImporterPool;
        $this->dataImportInfoF = $dataImportInfoF;
        $this->dataProviderPool = $dataProviderPool;
        $this->entityManager = $entityManager;
        $this->storageSolverPool = $storageSolverPool;
        $this->entityScopeManager = $entityScopeManager;
    }

    /**
     * @param string $rememberedEntitiesStoragePath
     * @param string $rememberedEntitiesStorageType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRememberedEntities(string $rememberedEntitiesStoragePath, string $rememberedEntitiesStorageType): array
    {
        $rememberedEntities = [];
        //@todo
        try {
            $dataProvider = $this->dataProviderPool->getDataProvider($rememberedEntitiesStorageType);
            $rememberedEntities = $dataProvider->getData($rememberedEntitiesStoragePath);
        } catch(FileNotFoundException $e) {
            // create the file for remembering entities if it does not exist
            $solver = $this->storageSolverPool->getSolver($rememberedEntitiesStorageType);
            $solver->execute($rememberedEntitiesStoragePath);
            $message = __(' We cannot access to storage for remembered entities.'
                          . ' The storage provider returned this message: ' . $e->getMessage()
                          . ' The solver script have been run.'
                          . ' You can configure your Solver class in the di.xml config.'
                          . ' See node type for class ' . StorageSolverPool::class);
            echo $message;
        }
        return $rememberedEntities;
    }

    /**
     * @param DataImportInfoInterface $dataImportInfo
     * @param string $rememberedEntitiesStoragePath
     * @param string $rememberedEntitiesStorageType
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRememberedEntitiesByScope(DataImportInfoInterface $dataImportInfo,
                                                 string $rememberedEntitiesStoragePath,
                                                 string $rememberedEntitiesStorageType
    ) {
        //@todo optimize
        $rememberedEntities = $this->getRememberedEntities($rememberedEntitiesStoragePath,
                                                           $rememberedEntitiesStorageType);

        $formattedEntityList = $this->entityManager->getScopeFormatEntityList($rememberedEntities);
        $entityScope = $this->entityScopeManager->getEntityScope($dataImportInfo);

        return array_key_exists($entityScope, $formattedEntityList) ? $formattedEntityList[$entityScope] : null;
    }

    /**
     * @param array $allRememberedEntities
     * @param string $rememberedEntitiesStoragePath
     * @param string $rememberedEntitiesStorageType
     * @return void
     */
    public function importRememberedEntities(array $allRememberedEntities,
                                             string $rememberedEntitiesStoragePath,
                                             string $rememberedEntitiesStorageType
    ) {
        /** DataImportInfoInterface $dataImportInfo */
        $dataImportInfo = $this->dataImportInfoF->create();
        // Hide not used cells from dataImportInfo. In this case pathToDataProvider
        // if we don't remember failed entity while import remembered entities data there is not required path to data-provider
        $dataImportInfo->setPathToRecipient($rememberedEntitiesStoragePath);

        /** @var \Guentur\MagentoImport\Api\DataImporter\DataImporterInterface $dataImporter */
        $dataImporter = $this->dataImporterPool->getDataImporter($rememberedEntitiesStorageType);
        $dataImporter->setDataImportInfo($dataImportInfo);
        $dataImporter->importData($allRememberedEntities);
    }
}
