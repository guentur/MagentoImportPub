<?php

namespace Guentur\MagentoImport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDB;

class RememberedEntity extends AbstractDB
{
    public function _construct()
    {
        $this->_init('remembered_import_entities', 'remembered_entry_id');
    }
}
