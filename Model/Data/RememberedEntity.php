<?php

namespace ElogicCo\MagentoImport\Model\Data;

use ElogicCo\MagentoImport\Api\Data\DataImportInfoInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use ElogicCo\MagentoImport\Api\Data\RememberedEntityInterface;
use ElogicCo\MagentoImport\Model\ResourceModel\RememberedEntity as RememberedEntityResource;
use Magento\Framework\Api\AbstractExtensibleObject;

class RememberedEntity extends AbstractSimpleObject implements RememberedEntityInterface
{
    private $rememberedEntityResource;

    public function __construct(
        RememberedEntityResource $rememberedEntityResource,
        array $data = []
    ) {
        $this->rememberedEntityResource = $rememberedEntityResource;
        parent::__construct($data);
    }

    public function getId()
    {
        $idFieldName = $this->rememberedEntityResource->getIdFieldName();
        return $this->_get($idFieldName);
    }

    public function setScope(string $scope)
    {
        $this->setData('scope', $scope);
    }

    public function getScope()
    {
        return $this->_get('scope');
    }

    public function setRememberMode(string $rememberMode)
    {
        $this->setData('remember_mode', $rememberMode);
    }

    public function getRememberMode()
    {
        return $this->_get('remember_mode');
    }

    public function setRememberedEntityKey($rememberedEntityKey)
    {
        $this->setData('remembered_entity_key', $rememberedEntityKey);
    }

    /**
     * @return mixed|null
     */
    public function getRememberedEntityKey()
    {
        return $this->_get('remembered_entity_key');
    }

    public function getCreatedAt()
    {
        return $this->_get('created_at');
    }

    public function getUpdatedAt()
    {
        return $this->_get('updated_at');
    }

    public function setCreatedAt($createdAt)
    {
        $this->setData('created_at', $createdAt);
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->setData('updated_at', $updatedAt);
    }

/////////////// Additional logic
    public function setPathToRecipient(string $pathToRecipient): void
    {
        $this->setData('path_to_recipient', $pathToRecipient);
    }

    public function setPathToDataProvider(string $path): void
    {
        $this->setData('path_to_data_provider', $path);
    }

    public function getPathToDataProvider()
    {
        return $this->_get('path_to_data_provider');
    }

    public function getPathToRecipient(): string
    {
        return $this->_get('path_to_recipient');
    }

    public function getDataProviderName()
    {
        $pathToProvider = $this->getPathToDataProvider();
        return $pathToProvider == null ? null : pathinfo($pathToProvider)['filename'];
    }

    public function getRecipientName()
    {
        $pathToRecipient = $this->getPathToRecipient();
        return $pathToRecipient == null ? null : pathinfo($pathToRecipient)['filename'];
    }

//@todo extend Magento\Framework\Api\AbstractExtensibleObject to give capability for third party developers to use extension attributes
}
