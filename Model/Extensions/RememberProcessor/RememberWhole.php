<?php

namespace ElogicCo\MagentoImport\Model\Extensions\RememberProcessor;

use ElogicCo\MagentoImport\Api\Data\DataImportInfoInterface;
use ElogicCo\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorInterface;
use ElogicCo\MagentoImport\Api\Data\RememberedEntityInterfaceFactory;
use ElogicCo\MagentoImport\Api\Data\RememberedEntityInterface;
use ElogicCo\MagentoImport\Api\RememberedEntityRepositoryInterface;
use ElogicCo\MagentoImport\Model\ResourceModel\RememberedEntity as RememberedEntityResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use ElogicCo\MagentoImport\Model\Extensions\ApplyObserverFactory;
use ElogicCo\MagentoImport\Model\Extensions\RememberProcessor\RememberProcessorPool\Proxy as RememberProcessorPoolProxy;
use ElogicCo\MagentoImport\Api\Data\RememberedEntitySearchResultInterface;

class RememberWhole implements RememberProcessorInterface
{
// @todo setup only filename. Make absolute path by function like getMediaPath() in Magento
    public const IMPORT_STATE_FILE_NAME = __DIR__ . '/../../../etc/whole_broken_entities.csv';

    protected $rememberedEntitiesStorageType;

    protected $rememberedEntitiesStoragePath;

    protected $rememberedEntityF;

    protected $rememberedEntityResource;

    protected $searchCriteriaBuilder;

    protected $applyObserverFactory;

    protected $rememberProcessorPool;

    protected $sortOrderBuilder;

    protected $rememberedEntityRepository;

    /**
     * @param RememberedEntityInterfaceFactory $rememberedEntityF
     * @param RememberedEntityResource $rememberedEntityResource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ApplyObserverFactory $applyObserverFactory
     * @param RememberProcessorPoolProxy $rememberProcessorPool
     * @param SortOrderBuilder $sortOrderBuilder
     * @param RememberedEntityRepositoryInterface $rememberedEntityRepository
     * @param string $rememberedEntitiesStorageType
     * @param string $rememberedEntitiesStoragePath
     */
    public function __construct(
        RememberedEntityInterfaceFactory $rememberedEntityF,
        RememberedEntityResource $rememberedEntityResource,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ApplyObserverFactory $applyObserverFactory,
        RememberProcessorPoolProxy $rememberProcessorPool,
        SortOrderBuilder $sortOrderBuilder,
        RememberedEntityRepositoryInterface $rememberedEntityRepository,
        string $rememberedEntitiesStorageType,
        string $rememberedEntitiesStoragePath
    ) {
        $this->rememberedEntitiesStorageType = $rememberedEntitiesStorageType;
        $this->rememberedEntitiesStoragePath = $rememberedEntitiesStoragePath;
        $this->rememberedEntityF = $rememberedEntityF;
        $this->rememberedEntityResource = $rememberedEntityResource;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->applyObserverFactory = $applyObserverFactory;
        $this->rememberProcessorPool = $rememberProcessorPool;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->rememberedEntityRepository = $rememberedEntityRepository;
    }

    /**
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @param $exception
     * @return mixed|void
     */
    public function rememberEntity(int $entityKey, DataImportInfoInterface $dataImportInfo, $exception)
    {
        /** @var RememberedEntityInterface $rememberedEntity */
        $rememberedEntity = $this->rememberedEntityF->create();
        $rememberedEntity = $this->fillRememberedEntityModelWithData($rememberedEntity, $entityKey, $dataImportInfo);
        $this->rememberedEntityRepository->save($rememberedEntity);
    }

    public function getCurrentRememberMode(): string
    {
        return $this->rememberProcessorPool->getProcessModeByClass($this);
    }

    public function fillRememberedEntityModelWithData(
        RememberedEntityInterface $rememberedEntity,
        int $entityKey,
        DataImportInfoInterface $dataImportInfo
    ): RememberedEntityInterface {
        /** @var \ElogicCo\MagentoImport\Model\Extensions\ApplyObserver $applyObserverModel */
        $applyObserverModel = $this->applyObserverFactory->create();
        $scope = $applyObserverModel->getFullEventName($dataImportInfo);
        $rememberMode = $this->getCurrentRememberMode();

        $rememberedEntity->setScope($scope);
        $rememberedEntity->setRememberMode($rememberMode);
        $rememberedEntity->setRememberedEntityKey($entityKey);

        return $rememberedEntity;
    }

    /**
     * @param array $array
     * @param DataImportInfoInterface $dataImportInfo
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getArraySinceRememberedEntity(array $array, DataImportInfoInterface $dataImportInfo): array
    {
        /** @var \ElogicCo\MagentoImport\Model\Extensions\ApplyObserver $applyObserverModel */
        $applyObserverModel = $this->applyObserverFactory->create();
        $scope = $applyObserverModel->getFullEventName($dataImportInfo);

        $this->searchCriteriaBuilder->addFilter('scope', $scope);

        // Get first remembered entity that was remembered by whole remember
        $sortOrder = $this->sortOrderBuilder
            ->setField('created_at')
            ->setAscendingDirection()
            ->create();
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        $this->searchCriteriaBuilder->setPageSize(1)->setCurrentPage(1);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $rememberedEntitiesResult = $this->rememberedEntityRepository->getList($searchCriteria);

        foreach ($rememberedEntitiesResult->getItems() as $rememberedEntity) {
            $rememberedEntityKey = $rememberedEntity->getRememberedEntityKey();
        }

        if (isset($rememberedEntityKey) && array_key_exists($rememberedEntityKey, $array)) {
            $array = array_slice($array, $rememberedEntityKey, null, true);
        }
        return $array;
    }

    public function getRememberedStateDataForImport(array $dataForImport, DataImportInfoInterface $dataImportInfo): array
    {
        $rememberedEntitiesResult = $this->getRememberedEntitiesByScope($dataImportInfo);

        $rememberedDataForImport = [];
        foreach ($rememberedEntitiesResult->getItems() as $rememberedEntity) {
            $rememberedDataForImport[$rememberedEntity->getRememberedEntityKey()] = $dataForImport[$rememberedEntity->getRememberedEntityKey()];
        }
        return $rememberedDataForImport;
    }

    public function getRememberedEntitiesByScope(DataImportInfoInterface $dataImportInfo): RememberedEntitySearchResultInterface
    {
        $applyObserverModel = $this->applyObserverFactory->create();
        $scope = $applyObserverModel->getFullEventName($dataImportInfo);
        $rememberMode = $this->getCurrentRememberMode();

        $this->searchCriteriaBuilder->addFilter('scope', $scope);
        $this->searchCriteriaBuilder->addFilter('remember_mode', $rememberMode);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $rememberedEntitiesResult = $this->rememberedEntityRepository->getList($searchCriteria);

        return $rememberedEntitiesResult;
    }

    public function forgetEntity(int $entityKey, DataImportInfoInterface $dataImportInfo)
    {
        /** @var RememberedEntityInterface $rememberedEntity */
        $rememberedEntity = $this->rememberedEntityF->create();
        $rememberedEntity = $this->fillRememberedEntityModelWithData($rememberedEntity, $entityKey, $dataImportInfo);
        $rememberedEntityId = $this->rememberedEntityResource->getRememberedEntityIdByModeScopeAndKey($rememberedEntity);
        if (false !== $rememberedEntityId) {
            $this->rememberedEntityRepository->deleteById($rememberedEntityId);
        }
    }
}
