<?php

namespace ElogicCo\MagentoImport\Model;

use Magento\Framework\Model\AbstractModel;
use ElogicCo\MagentoImport\Api\Data\RememberedEntityInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class RememberedEntity extends AbstractModel
{
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var RememberedEntityInterfaceFactory
     */
    private $rememberedEntityDataFactory;

    public function __construct(
        RememberedEntityInterfaceFactory $rememberedEntityDataFactory,
        DataObjectHelper $dataObjectHelper,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->rememberedEntityDataFactory = $rememberedEntityDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(\ElogicCo\MagentoImport\Model\ResourceModel\RememberedEntity::class);
    }

    public function getDataModel()
    {
        $rememberedEntityData = $this->getData();
        $rememberedEntityDataObject = $this->rememberedEntityDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $rememberedEntityDataObject,
            $rememberedEntityData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        return $rememberedEntityDataObject;
    }
}
