<?php

namespace Guentur\MagentoImport\Model\Extensions;

use Guentur\MagentoImport\Api\Extensions\ApplyObserverInterface;
use Magento\Framework\DataObject;
use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Magento\Framework\Event\ManagerInterface;

class ApplyObserver implements ApplyObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * @param array $dataItem
     * @param DataImportInfoInterface $dataImportInfo
     * @return array
     */
    public function callObserver(array $dataItem, DataImportInfoInterface $dataImportInfo): array
    {
        $dataItemObject = new DataObject($dataItem);
        // if there is error throw \RuntimeException
        $this->eventManager->dispatch(
            $this->getFullEventName($dataImportInfo),
            [
                'data_item' => $dataItemObject
            ]
        );
        return $dataItemObject->getData();
    }

    /**
     * @param DataImportInfoInterface $dataImportInfo
     * @return string
     */
    public function getFullEventName(DataImportInfoInterface $dataImportInfo): string
    {
        //@todo write in documentation that file name (not path, but exactly filename) of separate dataProviders must be different
        $providerName = $dataImportInfo->getDataProviderName();
        $recipientName = $dataImportInfo->getRecipientName();
        $name = $providerName . '_' . $recipientName;

        $providerType = $dataImportInfo->getDataProviderType();
        $recipientType = $dataImportInfo->getRecipientType();
        $type = $providerType . '_' . $recipientType;

        //@todo add importer type part to event name
        return 'guentur_import_' . $name . '_' . $type;
    }
}
