<?php

namespace ElogicCo\MagentoImport\Model\Extensions;

use ElogicCo\MagentoImport\Api\Extensions\ApplyObserverInterface;
use Magento\Framework\DataObject;
use ElogicCo\MagentoImport\Api\Data\DataImportInfoInterface;
use Magento\Framework\Event\ManagerInterface;

class ApplyObserver implements ApplyObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    private $fullEventName;

    /**
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * @todo ask Is it better to change $dataItem using php linking:
     * callObserver(array &$dataItem
     * or just return value from a method?
     *
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
        if (null === $this->fullEventName) {
            //@todo write in documentation that file name (not path, but exactly filename) of separate dataProviders must be different
            $providerName = $dataImportInfo->getDataProviderName() ?? 'blank';
            $recipientName = $dataImportInfo->getRecipientName() ?? 'blank';
            $providerType = $dataImportInfo->getDataProviderType() ?? 'blank';
            $recipientType = $dataImportInfo->getRecipientType() ?? 'blank';

            $name = $providerName . '_' . $recipientName;
            $type = $providerType . '_' . $recipientType;

            //@todo add importer type part to event name
            $this->fullEventName = 'elogic_import_' . $name . '_' . $type;
        }
        return $this->fullEventName;
    }
}
