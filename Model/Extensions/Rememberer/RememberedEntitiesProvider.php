<?php

namespace Guentur\MagentoImport\Model\Extensions\Rememberer;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\DataProvider\DataProviderPoolInterface;
use Guentur\MagentoImport\Api\Extensions\Rememberer\RememberedEntitiesProviderInterface;
use Guentur\MagentoImport\Model\EntityManager;
use Guentur\MagentoImport\Model\EntityScopeManager;
use Guentur\MagentoImport\Model\Solver\StorageSolverProvider;

class RememberedEntitiesProvider implements RememberedEntitiesProviderInterface
{
    private $dataProviderPool;

    private $entityManager;

    private $storageSolverProvider;

    private $entityScopeManager;

    private $rememberedEntitiesStorageType;

    private $rememberedEntitiesStoragePath;

    public function __construct(
        DataProviderPoolInterface $dataProviderPool,
        EntityManager $entityManager,
        StorageSolverProvider $storageSolverProvider,
        EntityScopeManager $entityScopeManager,
        string $rememberedEntitiesStorageType,
        string $rememberedEntitiesStoragePath
    ) {
        $this->dataProviderPool = $dataProviderPool;
        $this->entityManager = $entityManager;
        $this->storageSolverProvider = $storageSolverProvider;
        $this->entityScopeManager = $entityScopeManager;
        $this->rememberedEntitiesStorageType = $rememberedEntitiesStorageType;
        $this->rememberedEntitiesStoragePath = $rememberedEntitiesStoragePath;
    }

    public function getRememberedEntities(): array
    {
        $rememberedEntities = [];
        //@todo
        try {
            $dataProvider = $this->dataProviderPool->getDataProvider($this->rememberedEntitiesStorageType);
            $rememberedEntities = $dataProvider->getData($this->rememberedEntitiesStoragePath);
        } catch(\InvalidArgumentException $e) {
            // create the file for remembering entities if it does not exist
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
        if (isset($rememberedEntity) && array_key_exists($rememberedEntity, $array)) {
            $array = array_slice($array, $rememberedEntity, null, true);
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
