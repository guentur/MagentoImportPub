<?php

declare(strict_types=1);

namespace ElogicCo\MagentoImport\Model\Data;

use ElogicCo\MagentoImport\Api\Data\DataImportInfoInterface;
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

    public function setDataProviderType(string $dataProviderType): void
    {
        $this->setData('data_provider_type', $dataProviderType);
    }

    public function getDataProviderType()
    {
        return $this->_get('data_provider_type');
    }

    public function setRecipientType(string $recipientType): void
    {
        $this->setData('recipient_type', $recipientType);
    }

    public function getRecipientType()
    {
        return $this->_get('recipient_type');
    }

//    public function setDataForImport(array $data)
//    {
//        $this->setData('data_for_import', $data);
//    }
//
//    public function getDataForImport()
//    {
//        return $this->_get('data_for_import');
//    }
}
