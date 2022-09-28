<?php

namespace Guentur\MagentoImport\Model;

use Guentur\MagentoImport\Model\Mapper\DefaultMapping;
use Guentur\MagentoImport\Api\Data\DataImportInfoInterfaceFactory;

class EntityManager
{
    private $entityScopeManager;

    /**
     * @var DataImportInfoInterfaceFactory
     */
    private $dataImportInfoF;

    public function __construct(
        EntityScopeManager $entityScopeManager,
        DataImportInfoInterfaceFactory $dataImportInfoF
    ) {
        $this->entityScopeManager = $entityScopeManager;
        $this->dataImportInfoF = $dataImportInfoF;
    }

    /**
     * @todo make validation or create DataModel, Collection for this
     *
     * @param array $importFormatEntityList = [
     *      key => [
     *          'path_to_provider' => 'path_to_provider',
     *          'path_to_recipient' => 'path_to_recipient',
     *          'entity_key' => (int) entity_key,
     *      ],
     *      ...
     * ]
     * @return array = [
    basename('path_to_provider') . DefaultMapping::DEFAULT_SEPARATOR . basename('path_to_recipient') => (int) entity_key,
        ...
    ];
     */
    public function getScopeFormatEntityList(array $importFormatEntityList): array
    {
        $scopeFormatEntityList = [];
        foreach ($importFormatEntityList as $importData) {
            /** \Guentur\MagentoImport\Api\Data\DataImportInfoInterface $dataImportInfo */
            $dataImportInfo = $this->dataImportInfoF->create();
            $dataImportInfo->setPathToDataProvider($importData['path_to_provider']);
            $dataImportInfo->setPathToRecipient($importData['path_to_recipient']);
            $entityScope = $this->entityScopeManager->getEntityScope($dataImportInfo);
            $scopeFormatEntityList[$entityScope] = $importData['entity_key'];
        }
        return $scopeFormatEntityList;
    }

    public function getImportFormatEntityList(array $scopeFormatEntityList): array
    {
        $importFormatEntityList = [];
        foreach ($scopeFormatEntityList as $scope => $entityId) {
            $importFormatEntityList[$scope] = $this->entityScopeManager->parseEntityScope($scope);
            $importFormatEntityList[$scope]['entity_key'] = $entityId;
        }
        return $importFormatEntityList;
    }
}
