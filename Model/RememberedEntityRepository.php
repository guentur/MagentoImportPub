<?php

namespace Guentur\MagentoImport\Model;

use Guentur\MagentoImport\Api\Data\RememberedEntitySearchResultInterface;
use Guentur\MagentoImport\Api\Data\RememberedEntitySearchResultInterfaceFactory;
use Guentur\MagentoImport\Api\RememberedEntityRepositoryInterface;
use Guentur\MagentoImport\Model\ResourceModel\RememberedEntity as RememberedEntityResource;
use Guentur\MagentoImport\Model\ResourceModel\RememberedEntity\CollectionFactory as RememberedEntityCollectionFactory;
use Guentur\MagentoImport\Model\RememberedEntityFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Guentur\MagentoImport\Api\Data\RememberedEntityInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;

class RememberedEntityRepository implements RememberedEntityRepositoryInterface
{
    private $rememberedEntityResource;

    private $rememberedEntityFactory;

    private $rememberedEntityRegistry;

    private $searchResultFactory;

    private $collectionProcessor;

    public function __construct(
        RememberedEntityResource $rememberedEntityResource,
        RememberedEntityFactory $rememberedEntityFactory,
        RememberedEntityRegistry $rememberedEntityRegistry,
        RememberedEntitySearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->rememberedEntityResource = $rememberedEntityResource;
        $this->rememberedEntityFactory = $rememberedEntityFactory;
        $this->rememberedEntityRegistry = $rememberedEntityRegistry;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param RememberedEntityInterface $rememberedEntity
     * @return mixed|void
     * @throws CouldNotSaveException
     */
    public function save(RememberedEntityInterface $rememberedEntity)
    {
        try {
            if (!$this->rememberedEntityResource->isRememberedEntityExists($rememberedEntity)) {
                $rememberedEntityModel = $this->rememberedEntityFactory->create();
                $rememberedEntityModel->setData($rememberedEntity->__toArray());
                $this->rememberedEntityResource->save($rememberedEntityModel);
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not remember entity: %1', $exception->getMessage()),
                $exception
            );
        }
    }

    public function getById($rememberedEntityId)
    {
        $customerModel = $this->rememberedEntityRegistry->retrieve($rememberedEntityId);
        return $customerModel->getDataModel();
    }

    /**
     * Delete customer.
     *
     * @param RememberedEntityInterface $rememberedEntity
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(RememberedEntityInterface $rememberedEntity)
    {
        return $this->deleteById($rememberedEntity->getId());
    }

    public function deleteById($rememberedEntityId)
    {
        try {
            $rememberedEntityModel = $this->rememberedEntityRegistry->retrieve($rememberedEntityId);
            $this->rememberedEntityResource->delete($rememberedEntityModel);
            $this->rememberedEntityRegistry->remove($rememberedEntityId);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                    'Could not delete the rememberedEntity: %1',
                    $exception->getMessage()
                ));
        }
        return true;
    }

    public function deleteByScope(string $rememberedEntityScope)
    {
        // TODO: Implement deleteByScope() method.
    }

    /**
     * Retrieve customers which match a specified criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See https://devdocs.magento.com/codelinks/attributes.html#CustomerRepositoryInterface to determine
     * which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Customer\Api\Data\CustomerSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): RememberedEntitySearchResultInterface
    {
        /** @var \Guentur\MagentoImport\Api\Data\RememberedEntitySearchResultInterface $searchResults */
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
        $collection = $this->rememberedEntityFactory->create()->getCollection();

        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setTotalCount($collection->getSize());

        $rememberedEntities = [];
        /** @var \Guentur\MagentoImport\Model\RememberedEntity $rememberedEntityModel */
        foreach ($collection as $rememberedEntityModel) {
            $rememberedEntities[] = $rememberedEntityModel->getDataModel();
        }
        $searchResults->setItems($rememberedEntities);
        return $searchResults;
    }
}
