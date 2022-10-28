<?php

namespace Guentur\MagentoImport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDB;

class RememberedEntity extends AbstractDB
{
    public function _construct()
    {
        $this->_init('broken_import_entities', 'broken_entity_id');
    }
}
