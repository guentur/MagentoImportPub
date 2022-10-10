<?php

declare(strict_types=1);

namespace Guentur\MagentoImport\Model\Data;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class DataImportInfo extends AbstractSimpleObject implements DataImportInfoInterface
{
    public function setPathToDataProvider(string $pathToProvider): void
    {
        $this->setData('path_to_data_provider', $pathToProvider);
    }

    public function setPathToRecipient(string $pathToRecipient): void
    {
        $this->setData('path_to_recipient', $pathToRecipient);
    }

    public function getPathToDataProvider(): string
    {
        return $this->_get('path_to_data_provider');
    }

    public function getPathToRecipient(): string
    {
        return $this->_get('path_to_recipient');
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

    public function setDataProviderType(string $dataProviderType): void
    {
        $this->setData('data_provider_type', $dataProviderType);
    }

    public function getDataProviderType(): string
    {
        return $this->_get('data_provider_type');
    }

    public function setRecipientType(string $recipientType): void
    {
        $this->setData('recipient_type', $recipientType);
    }

    public function getRecipientType(): string
    {
        return $this->_get('recipient_type');
    }
}
