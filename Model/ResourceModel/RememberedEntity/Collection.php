<?php

namespace Elogic\MagentoImport\Model\ResourceModel\RememberedEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Elogic\MagentoImport\Model\RememberedEntity::class,
                     \Elogic\MagentoImport\Model\ResourceModel\RememberedEntity::class);
    }
}
