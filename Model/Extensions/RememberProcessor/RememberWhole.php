<?php

namespace Guentur\MagentoImport\Model\Extensions\RememberProcessor;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorInterface;
use Guentur\MagentoImport\Api\Data\RememberedEntityInterfaceFactory;
use Guentur\MagentoImport\Api\Data\RememberedEntityInterface;
use Guentur\MagentoImport\Api\RememberedEntityRepositoryInterface;
use Guentur\MagentoImport\Model\ResourceModel\RememberedEntity as RememberedEntityResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Guentur\MagentoImport\Model\Extensions\ApplyObserverFactory;
use Guentur\MagentoImport\Model\Extensions\RememberProcessor\RememberProcessorPool\Proxy as RememberProcessorPoolProxy;

use Guentur\MagentoImport\Model\Exception\ImportException;
use Guentur\MagentoImport\Api\DataImporter\DataImporterInterface;

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
        //@todo refactor rememberedEntitiesStoragePath etc.
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

    /**
     * @param array $dataForImport
     * @param $dataImportInfo
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
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
            $dataForImport = $this->getArraySinceRememberedEntity($dataForImport, $dataImporter->getDataImportInfo());
            $this->importData($dataForImport, $dataImporter);
        }
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
