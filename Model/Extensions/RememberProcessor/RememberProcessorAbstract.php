<?php

namespace ElogicCo\MagentoImport\Model\Extensions\RememberProcessor;

use ElogicCo\MagentoImport\Api\Data\DataImportInfoInterface;
use ElogicCo\MagentoImport\Api\Data\DataImportInfoInterfaceFactory;
use ElogicCo\MagentoImport\Api\DataImporter\DataImporterPoolInterface;
use ElogicCo\MagentoImport\Api\DataProvider\DataProviderPoolInterface;
use ElogicCo\MagentoImport\Model\EntityManager;
use ElogicCo\MagentoImport\Model\EntityScopeManager;
use ElogicCo\MagentoImport\Model\Solver\StorageSolverPool;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use ElogicCo\MagentoImport\Api\Extensions\RememberProcessor\RememberedEntitiesProviderInterface;

abstract class RememberProcessorAbstract implements RememberedEntitiesProviderInterface
{
    protected $rememberedEntitiesStorageType;

    protected $rememberedEntitiesStoragePath;

    /**
     * @var DataProviderPoolInterface
     */
    protected $dataProviderPool;

    /**
     * @var DataImportInfoInterfaceFactory
     */
    protected $dataImportInfoF;

    /**
     * @var DataImporterPoolInterface
     */
    protected $dataImporterPool;

    /**
     * @var EntityManager `
     */
    protected $entityManager;

    /**
     * @var StorageSolverPool
     */
    protected $storageSolverPool;

    /**
     * @var EntityScopeManager
     */
    protected $entityScopeManager;

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
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRememberedEntities(): array
    {
        $rememberedEntities = [];
        //@todo
        try {
            $dataProvider = $this->dataProviderPool->getDataProvider($this->rememberedEntitiesStorageType);
            $rememberedEntities = $dataProvider->getData($this->rememberedEntitiesStoragePath);
        } catch(FileNotFoundException $e) {
            // create the file for remembering entities if it does not exist
            $solver = $this->storageSolverPool->getSolver($this->rememberedEntitiesStorageType);
            $solver->execute($this->rememberedEntitiesStoragePath);
            $message = __(' We cannot access to storage for remembered entities.'
                          . ' The storage provider returned this message: ' . $e->getMessage()
                          . ' The solver script have been run.'
                          . ' You can configure your Solver class in the di.xml config.'
                          . ' See node type for class ' . StorageSolverPool::class);
            echo $message;
        }
        return $rememberedEntities;
    }

//    /**
//     * @param DataImportInfoInterface $dataImportInfo
//     * @return mixed|null
//     * @throws \Magento\Framework\Exception\LocalizedException
//     */
//    public function getRememberedEntitiesByScope(string $entityScope)
//    {
//        //@todo optimize
//        $rememberedEntities = $this->getRememberedEntities();
//        $formattedEntityList = $this->entityManager->getScopeFormatEntityList($rememberedEntities);
//
//        return array_key_exists($entityScope, $formattedEntityList) ? $formattedEntityList[$entityScope] : null;
//    }

//    /**
//     * @param array $allRememberedEntities
//     * @return void
//     */
//    public function importRememberedEntities(array $allRememberedEntities): void
//    {
//        /** DataImportInfoInterface $dataImportInfo */
//        $dataImportInfo = $this->dataImportInfoF->create();
//        // Hide not used cells from dataImportInfo. In this case pathToDataProvider
//        // if we don't remember failed entity while import remembered entities data there is not required path to data-provider
//        $dataImportInfo->setPathToRecipient($this->rememberedEntitiesStoragePath);
//
//        /** @var \ElogicCo\MagentoImport\Api\DataImporter\DataImporterInterface $dataImporter */
//        $dataImporter = $this->dataImporterPool->getDataImporter($this->rememberedEntitiesStorageType);
//        $dataImporter->setDataImportInfo($dataImportInfo);
//        $dataImporter->importData($allRememberedEntities);
//    }
}
