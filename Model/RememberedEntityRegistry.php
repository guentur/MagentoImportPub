<?php

namespace ElogicCo\MagentoImport\Model;

use ElogicCo\MagentoImport\Model\RememberedEntity;
use ElogicCo\MagentoImport\Model\RememberedEntityFactory;
use ElogicCo\MagentoImport\Model\ResourceModel\RememberedEntity as RememberedEntityResource;
use Magento\Framework\Exception\NoSuchEntityException;

class RememberedEntityRegistry
{
    const REGISTRY_SEPARATOR = ':';

    /**
     * @var \ElogicCo\MagentoImport\Model\RememberedEntityFactory
     */
    private $rememberedEntityFactory;

    /**
     * @var RememberedEntityResource
     */
    private $rememberedEntityResource;

    /**
     * @var array
     */
    private $rememberedEntityRegistryById = [];

    /**
     * @param \ElogicCo\MagentoImport\Model\RememberedEntityFactory $rememberedEntityFactory
     * @param RememberedEntityResource $rememberedEntityResource
     */
    public function __construct(
        RememberedEntityFactory $rememberedEntityFactory,
        RememberedEntityResource $rememberedEntityResource
    ) {
        $this->rememberedEntityFactory = $rememberedEntityFactory;
        $this->rememberedEntityResource = $rememberedEntityResource;
    }

    /**
     * Retrieve Customer Model from registry given an id
     *
     * @param string $customerId
     * @return RememberedEntity
     * @throws NoSuchEntityException
     */
    public function retrieve($rememberedEntityId)
    {
        if (isset($this->rememberedEntityRegistryById[$rememberedEntityId])) {
            return $this->rememberedEntityRegistryById[$rememberedEntityId];
        }
        $rememberedEntity = $this->rememberedEntityFactory->create();
        $this->rememberedEntityResource->load($rememberedEntity, $rememberedEntityId);
        if (!$rememberedEntity->getId()) {
            // remembered entity does not exist
            throw NoSuchEntityException::singleField('customerId', $rememberedEntityId);
        } else {
            $this->rememberedEntityRegistryById[$rememberedEntityId] = $rememberedEntity;
            return $rememberedEntity;
        }
    }

    /**
     * Remove instance of the Customer Model from registry given an id
     *
     * @param int $rememberedEntityId
     * @return void
     */
    public function remove($rememberedEntityId)
    {
        if (isset($this->rememberedEntityRegistryById[$rememberedEntityId])) {
            unset($this->rememberedEntityRegistryById[$rememberedEntityId]);
        }
    }

    /**
     * Replace existing customer model with a new one.
     *
     * @param RememberedEntity $rememberedEntity
     * @return $this
     */
    public function push(RememberedEntity $rememberedEntity)
    {
        $this->rememberedEntityRegistryById[$rememberedEntity->getId()] = $rememberedEntity;
        return $this;
    }
}
