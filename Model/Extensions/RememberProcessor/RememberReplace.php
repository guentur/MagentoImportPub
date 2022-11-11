<?php

namespace Guentur\MagentoImport\Model\Extensions\RememberProcessor;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\Data\DataImportInfoInterfaceFactory;
use Guentur\MagentoImport\Api\Data\RememberedEntityInterface;
use Guentur\MagentoImport\Api\Data\RememberedEntityInterfaceFactory;
use Guentur\MagentoImport\Api\DataImporter\DataImporterPoolInterface;
use Guentur\MagentoImport\Api\DataProvider\DataProviderPoolInterface;
use Guentur\MagentoImport\Model\EntityManager;
use Guentur\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorInterface;
use Guentur\MagentoImport\Model\EntityScopeManager;
use Guentur\MagentoImport\Model\Solver\StorageSolverPool;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Guentur\MagentoImport\Api\RememberedEntityRepositoryInterface;
use Guentur\MagentoImport\Model\Extensions\ApplyObserverFactory;

class RememberReplace extends RememberProcessorAbstract implements RememberProcessorInterface
{
    // @todo setup only filename. Make absolute path by function like getMediaPath() in Magento
    const IMPORT_STATE_FILE_NAME = __DIR__ . '/../../../etc/import_state.csv';

    private $searchCriteriaBuilder;

    private $sortOrderBuilder;

    private $rememberedEntityRepository;

    private $rememberedEntityF;

    private $applyObserverFactory;

    public function __construct(
        DataImporterPoolInterface $dataImporterPool,
        DataImportInfoInterfaceFactory $dataImportInfoF,
        DataProviderPoolInterface $dataProviderPool,
        EntityManager $entityManager,
        StorageSolverPool $storageSolverPool,
        EntityScopeManager $entityScopeManager,
        RememberedEntityInterfaceFactory $rememberedEntityF,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ApplyObserverFactory $applyObserverFactory,
        SortOrderBuilder $sortOrderBuilder,
        RememberedEntityRepositoryInterface $rememberedEntityRepository,
        string $rememberedEntitiesStorageType,
        string $rememberedEntitiesStoragePath
    ) {
        $this->rememberedEntitiesStorageType = $rememberedEntitiesStorageType;
        $this->rememberedEntitiesStoragePath = $rememberedEntitiesStoragePath;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->rememberedEntityF = $rememberedEntityF;
        $this->applyObserverFactory = $applyObserverFactory;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->rememberedEntityRepository = $rememberedEntityRepository;
        parent::__construct(
            $dataImporterPool,
            $dataImportInfoF,
            $dataProviderPool,
            $entityManager,
            $storageSolverPool,
            $entityScopeManager
        );
    }

    /**
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @param $exception
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function rememberEntity(int $entityKey, DataImportInfoInterface $dataImportInfo, $exception)
    {
        $pathToRecipient = $dataImportInfo->getPathToRecipient();
        $pathToProvider = $dataImportInfo->getPathToDataProvider();
        //@todo Important. Refactor to use Guentur\MagentoImport\Model\Data\DataImportInfo
        $currentEntityInfo = [
            'path_to_provider' => $pathToProvider,
            'path_to_recipient' => $pathToRecipient,
            'entity_key' => (int) $entityKey,
        ];

        $rememberedEntities = $this->getRememberedEntities();
        $allRememberedEntities = $this->mergeWithAllRememberedEntities($rememberedEntities, $currentEntityInfo);
        $this->importRememberedEntities($allRememberedEntities);

        /** @var \Guentur\MagentoImport\Model\Extensions\ApplyObserver $applyObserverModel */
        $applyObserverModel = $this->applyObserverFactory->create();
        $scope = $applyObserverModel->getFullEventName($dataImportInfo);

        /** @var \Guentur\MagentoImport\Api\Data\RememberedEntityInterface $rememberedEntity */
        $rememberedEntity = $this->rememberedEntityF->create();
        $rememberedEntity->setScope($scope);
        $rememberedEntity->setRememberedEntityKey($scope);
        $this->rememberedEntityRepository->save($rememberedEntity);

        throw $exception;
    }

    public function mergeWithAllRememberedEntities(array $rememberedEntities, array $currentEntityInfo): array
    {
        $rememberedEntities[] = $currentEntityInfo;
        // Merge the entity that is processing with already saved entities by path_to_provider and path_to_recipient
        $scopeFormatEntityList = $this->entityManager->getScopeFormatEntityList($rememberedEntities);
        $rememberedEntities = $this->entityManager->getImportFormatEntityList($scopeFormatEntityList);

        return $rememberedEntities;
    }

    /**
     * @param array $array
     * @param DataImportInfoInterface $dataImportInfo
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getArraySinceRememberedEntity(array $array, DataImportInfoInterface $dataImportInfo): array
    {
        $entityScope = $this->entityScopeManager->getEntityScope($dataImportInfo);
        $this->searchCriteriaBuilder->addFilter('scope', $entityScope);
        $sortOrder = $this->sortOrderBuilder
            ->setField('created_at')
            ->setDescendingDirection()
            ->create();
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        $this->searchCriteriaBuilder->setPageSize(1)->setCurrentPage(1);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $rememberedEntitiesResult = $this->rememberedEntityRepository->getList($searchCriteria);
//        $rememberedEntity = $this->getRememberedEntitiesByScope($entityScope);

        foreach ($rememberedEntitiesResult->getItems() as $rememberedEntity) {
            $rememberedEntityKey = $rememberedEntity->getRememberedEntityKey();
        }

        if (isset($rememberedEntityKey) && array_key_exists($rememberedEntityKey, $array)) {
            $array = array_slice($array, $rememberedEntityKey, null, true);
        }
        return $array;
    }

    /**
     * @param array $array
     * @param DataImportInfoInterface $dataImportInfo
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRememberedStateDataForImport(array $array, DataImportInfoInterface $dataImportInfo): array
    {
        return $this->getArraySinceRememberedEntity($array, $dataImportInfo);
    }

    public function forgetEntity(int $entityKey, DataImportInfoInterface $dataImportInfo)
    {
        //@todo
    }
}
