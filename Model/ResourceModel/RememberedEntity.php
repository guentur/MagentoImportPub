<?php

namespace Guentur\MagentoImport\Model\ResourceModel;

use Guentur\MagentoImport\Api\Data\RememberedEntityInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDB;

class RememberedEntity extends AbstractDB
{
    public function _construct()
    {
        $this->_init('remembered_import_entities', 'remembered_entry_id');
    }

    /**
     * @param RememberedEntityInterface $rememberedEntity
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isRememberedEntityExists(RememberedEntityInterface $rememberedEntity): bool
    {
        $dbAdapter = $this->getConnection();
        $table = $this->getMainTable();
        $rememberedEntityKey = $rememberedEntity->getRememberedEntityKey();
        $rememberMode = $rememberedEntity->getRememberMode();
        $scope = $rememberedEntity->getScope();

        $select = $dbAdapter->select()->from($table, [$this->getIdFieldName()])
            ->where('remembered_entity_key = ?', $rememberedEntityKey)
            ->where('remember_mode = ?', $rememberMode)
            ->where('scope = ?', $scope);
        $result = $dbAdapter->fetchOne($select);

        if ($result === false) {
            return false;
        }
        return true;
    }
}
