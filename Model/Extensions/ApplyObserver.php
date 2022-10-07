<?php

namespace Guentur\MagentoImport\Model\Extensions;

use Guentur\MagentoImport\Api\Extensions\ApplyObserverInterface;
use Magento\Framework\DataObject;
use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Model\EntityScopeManager;
use Magento\Framework\Event\ManagerInterface;

class ApplyObserver implements ApplyObserverInterface
{
    /**
     * @var EntityScopeManager
     */
    private $entityScopeManager;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param EntityScopeManager $entityScopeManager
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        EntityScopeManager $entityScopeManager,
        ManagerInterface $eventManager
    ) {
        $this->entityScopeManager = $entityScopeManager;
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
            $this->getEventName($dataImportInfo),
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
    public function getEventName(DataImportInfoInterface $dataImportInfo): string
    {
        //@todo add importer type part to event name
        return 'guentur_import_'
            . $this->entityScopeManager->getEntityScopeEventFormat(
                $dataImportInfo
            );
    }
}
