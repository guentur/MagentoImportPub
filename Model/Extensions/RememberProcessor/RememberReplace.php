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
use Guentur\MagentoImport\Model\Exception\ImportException;
use Guentur\MagentoImport\Model\Solver\StorageSolverPool;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Guentur\MagentoImport\Api\RememberedEntityRepositoryInterface;
use Guentur\MagentoImport\Model\Extensions\ApplyObserverFactory;
use Guentur\MagentoImport\Model\Extensions\RememberProcessor\RememberProcessorPool\Proxy as RememberProcessorPoolProxy;
use Guentur\MagentoImport\Model\ResourceModel\RememberedEntity as RememberedEntityResource;
use Guentur\MagentoImport\Api\DataImporter\DataImporterInterface;

class RememberReplace extends RememberProcessorAbstract implements RememberProcessorInterface
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
     * @param array $dataForImport
     * @param DataImporterInterface $dataImporter
     * @return mixed|void
     */
    public function importData(
        array $dataForImport,
        DataImporterInterface $dataImporter
    ) {
        $dataImportInfo = $dataImporter->getDataImportInfo();
        try {
            foreach ($dataImporter->runImport($dataForImport) as $dataItemKey) {
                // if entity was imported successfully we should delete it from list of broken entities
                $this->forgetEntity($dataItemKey, $dataImportInfo);
            }
            //@todo Write in documentation that remember processor remember entity only if it throws my custom exception.
            /*
             * For example
             * try {
    //            $itemKey = 1;
    //            $dataForImport = $observer->getData('data_item');
    //        } catch (\Throwable $exception) {
    //            throw new ImportException($dataItemKey, $exception->getMessage(), $exception->getCode(), $exception);
    //        }
             */
        } catch (ImportException $e) {
            $dataItemKey = $e->getDataItemKey();
            $this->rememberEntity($dataItemKey, $dataImportInfo, $e);
            throw $e;
        }
    }

    /**
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @param $exception
     * @return mixed|void
     */
    public function rememberEntity(int $entityKey, DataImportInfoInterface $dataImportInfo)
    {
        /** @var RememberedEntityInterface $rememberedEntity */
        $rememberedEntity = $this->rememberedEntityF->create();
        $rememberedEntity = $this->fillRememberedEntityModelWithData($rememberedEntity, $entityKey, $dataImportInfo);
        $this->rememberedEntityRepository->save($rememberedEntity);
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
        /** @var \Guentur\MagentoImport\Model\Extensions\ApplyObserver $applyObserverModel */
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
        /** @var \Guentur\MagentoImport\Model\Extensions\ApplyObserver $applyObserverModel */
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
