<?php

namespace Elogic\MagentoImport\Model\Extensions\RememberProcessor;

use Elogic\MagentoImport\Api\Data\DataImportInfoInterface;
use Elogic\MagentoImport\Api\Data\DataImportInfoInterfaceFactory;
use Elogic\MagentoImport\Api\Data\RememberedEntityInterface;
use Elogic\MagentoImport\Api\Data\RememberedEntityInterfaceFactory;
use Elogic\MagentoImport\Api\DataImporter\DataImporterPoolInterface;
use Elogic\MagentoImport\Api\DataProvider\DataProviderPoolInterface;
use Elogic\MagentoImport\Model\EntityManager;
use Elogic\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorInterface;
use Elogic\MagentoImport\Model\EntityScopeManager;
use Elogic\MagentoImport\Model\Solver\StorageSolverPool;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Elogic\MagentoImport\Api\RememberedEntityRepositoryInterface;
use Elogic\MagentoImport\Model\Extensions\ApplyObserverFactory;
use Elogic\MagentoImport\Api\Data\RememberedEntitySearchResultInterface;
use Elogic\MagentoImport\Model\Extensions\RememberProcessor\RememberProcessorPool\Proxy as RememberProcessorPoolProxy;
use Elogic\MagentoImport\Model\ResourceModel\RememberedEntity as RememberedEntityResource;
use Elogic\MagentoImport\Api\DataImporter\DataImporterInterface;

class RememberReplace implements RememberProcessorInterface
{
    // @todo setup only filename. Make absolute path by function like getMediaPath() in Magento
    const IMPORT_STATE_FILE_NAME = __DIR__ . '/../../../etc/import_state.csv';

    private $searchCriteriaBuilder;

    private $sortOrderBuilder;

    private $rememberedEntityRepository;

    private $rememberedEntityF;

    private $applyObserverFactory;

    private $rememberProcessorPool;

    private $rememberedEntityResource;

    public function __construct(
        RememberedEntityInterfaceFactory $rememberedEntityF,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ApplyObserverFactory $applyObserverFactory,
        SortOrderBuilder $sortOrderBuilder,
        RememberedEntityRepositoryInterface $rememberedEntityRepository,
        RememberProcessorPoolProxy $rememberProcessorPool,
        RememberedEntityResource $rememberedEntityResource,
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

        $this->rememberProcessorPool = $rememberProcessorPool;
        $this->rememberedEntityResource = $rememberedEntityResource;
    }

    /**
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @param $exception
     * @return mixed|void
     */
    public function rememberEntity(int $entityKey, DataImportInfoInterface $dataImportInfo, \RuntimeException $exception)
    {
        /** @var RememberedEntityInterface $rememberedEntity */
        $rememberedEntity = $this->rememberedEntityF->create();
        $rememberedEntity = $this->fillRememberedEntityModelWithData($rememberedEntity, $entityKey, $dataImportInfo);
        $this->rememberedEntityRepository->save($rememberedEntity);

        throw $exception;
    }

    //@todo refactor to use a constant directly in this class
    public function getCurrentRememberMode(): string
    {
        return $this->rememberProcessorPool->getProcessModeByClass($this);
    }

    public function fillRememberedEntityModelWithData(
        RememberedEntityInterface $rememberedEntity,
        int $entityKey,
        DataImportInfoInterface $dataImportInfo
    ): RememberedEntityInterface {
        /** @var \Elogic\MagentoImport\Model\Extensions\ApplyObserver $applyObserverModel */
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
        /** @var \Elogic\MagentoImport\Model\Extensions\ApplyObserver $applyObserverModel */
        $applyObserverModel = $this->applyObserverFactory->create();
        $scope = $applyObserverModel->getFullEventName($dataImportInfo);

        $this->searchCriteriaBuilder->addFilter('scope', $scope);
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
