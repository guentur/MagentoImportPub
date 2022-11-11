<?php

namespace Guentur\MagentoImport\Model\ResourceModel\RememberedEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Guentur\MagentoImport\Model\RememberedEntity::class,
                     \Guentur\MagentoImport\Model\ResourceModel\RememberedEntity::class);
    }
}
