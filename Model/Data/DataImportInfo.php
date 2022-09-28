<?php

namespace Guentur\MagentoImport\Model\Data;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class DataImportInfo extends AbstractSimpleObject implements DataImportInfoInterface
{
    public function setPathToDataProvider(string $pathToProvider)
    {
        $this->setData('path_to_data_provider', $pathToProvider);
    }

    public function setPathToRecipient(string $pathToRecipient)
    {
        $this->setData('path_to_data_recipient', $pathToRecipient);
    }

    public function getPathToDataProvider(): string
    {
        return $this->_get('path_to_data_provider');
    }

    public function getPathToRecipient(): string
    {
        return $this->_get('path_to_data_recipient');
    }

    public function getDataProviderName(): string
    {
        $pathToProvider = $this->getPathToDataProvider();
        return pathinfo($pathToProvider)['filename'];
    }

    public function getRecipientName(): string
    {
        $pathToRecipient = $this->getPathToRecipient();
        return pathinfo($pathToRecipient)['filename'];
    }
}
