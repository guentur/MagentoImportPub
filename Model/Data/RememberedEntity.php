<?php

namespace Guentur\MagentoImport\Model\Data;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Guentur\MagentoImport\Api\Data\RememberedEntityInterface;
use Guentur\MagentoImport\Model\ResourceModel\RememberedEntity as RememberedEntityResource;

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

    public function setScope(string $scope)
    {
        $this->setData('scope', $scope);
    }

    public function getScope()
    {
        return $this->_get('scope');
    }

}
