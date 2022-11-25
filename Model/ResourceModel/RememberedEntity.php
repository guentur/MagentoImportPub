<?php

namespace ElogicCo\MagentoImport\Model\ResourceModel;

use ElogicCo\MagentoImport\Api\Data\RememberedEntityInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDB;

class RememberedEntity extends AbstractDB
{
    public function _construct()
    {
        $this->_init('remembered_import_entities', 'remembered_entry_id');
    }

    /**
     * @param RememberedEntityInterface $rememberedEntity
     * @return string|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRememberedEntityIdByModeScopeAndKey(RememberedEntityInterface $rememberedEntity)
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

        return $result;
    }
}
