<?php

namespace Guentur\MagentoImport\Model\Extensions\Rememberer;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\Data\DataImportInfoInterfaceFactory;
use Guentur\MagentoImport\Api\DataImporter\DataImporterPoolInterface;
use Guentur\MagentoImport\Model\EntityManager;
use Guentur\MagentoImport\Api\Extensions\Rememberer\RememberProcessorInterface;

class RememberWhole implements RememberProcessorInterface
{
// @todo setup only filename. Make absolute path by function like getMediaPath() in Magento
    const IMPORT_STATE_FILE_NAME = __DIR__ . '/../../../etc/whole_broken_entities.csv';

    private $dataImporterPool;

    private $rememberedEntitiesStorageType;

    private $rememberedEntitiesStoragePath;

    /**
     * @var DataImportInfoInterfaceFactory
     */
    private $dataImportInfoF;

    /**
     * @var \Guentur\MagentoImport\Model\Extensions\Rememberer\RememberedEntitiesProvider
     */
    private $rememberedEntitiesProvider;

    public function __construct(
        DataImporterPoolInterface $dataImporterPool,
        DataImportInfoInterfaceFactory $dataImportInfoF,
        RememberedEntitiesProvider $rememberedEntitiesProvider,
        string $rememberedEntitiesStorageType,
        string $rememberedEntitiesStoragePath
    ) {
        $this->dataImporterPool = $dataImporterPool;
        $this->dataImportInfoF = $dataImportInfoF;
        $this->rememberedEntitiesProvider = $rememberedEntitiesProvider;
        $this->rememberedEntitiesStorageType = $rememberedEntitiesStorageType;
        $this->rememberedEntitiesStoragePath = $rememberedEntitiesStoragePath;
    }

    /**
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @return void
     */
    public function rememberEntity(int $entityKey, DataImportInfoInterface $dataImportInfo)
    {
        $pathToRecipient = $dataImportInfo->getPathToRecipient();
        $pathToProvider = $dataImportInfo->getPathToDataProvider();
        //@todo Important. Refactor to use Guentur\MagentoImport\Model\Data\DataImportInfo
        $currentEntityInfo = [
            'path_to_provider' => $pathToProvider,
            'path_to_recipient' => $pathToRecipient,
            'entity_key' => (int) $entityKey,
        ];
        $rememberedEntities = $this->rememberedEntitiesProvider->getRememberedEntities();
        $rememberedEntities[] = $currentEntityInfo;
        $this->importRememberedEntities($rememberedEntities);
    }

    protected function importRememberedEntities(array $allRememberedEntities)
    {
        /** DataImportInfoInterface $dataImportInfo */
        $dataImportInfo = $this->dataImportInfoF->create();
        // Hide not used cells from dataImportInfo. In this case pathToDataProvider
        // if we dont remember failed entity while import remembered entities data there is not required path to data-provider
        $dataImportInfo->setPathToRecipient($this->rememberedEntitiesStoragePath);

        /** @var \Guentur\MagentoImport\Api\DataImporter\DataImporterInterface $dataImporter */
        $dataImporter = $this->dataImporterPool->getDataImporter($this->rememberedEntitiesStorageType);
        $dataImporter->setDataImportInfo($dataImportInfo);
        $dataImporter->importData($allRememberedEntities);
    }
}
