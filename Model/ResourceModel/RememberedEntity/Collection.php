<?php

namespace ElogicCo\MagentoImport\Model\ResourceModel\RememberedEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\ElogicCo\MagentoImport\Model\RememberedEntity::class,
                     \ElogicCo\MagentoImport\Model\ResourceModel\RememberedEntity::class);
    }
}
