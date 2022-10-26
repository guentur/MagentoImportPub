<?php

namespace Guentur\MagentoImport\Model\Extensions\RememberProcessor;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\Data\DataImportInfoInterfaceFactory;
use Guentur\MagentoImport\Api\DataImporter\DataImporterPoolInterface;
use Guentur\MagentoImport\Model\EntityManager;
use Guentur\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorInterface;
use Guentur\MagentoImport\Api\Extensions\RememberProcessor\RememberedEntitiesProviderInterface;

class RememberWhole implements RememberProcessorInterface
{
// @todo setup only filename. Make absolute path by function like getMediaPath() in Magento
    public const IMPORT_STATE_FILE_NAME = __DIR__ . '/../../../etc/whole_broken_entities.csv';

    private $rememberedEntitiesStorageType;

    private $rememberedEntitiesStoragePath;

    /**
     * @var RememberedEntitiesProviderInterface
     */
    private $rememberedEntitiesProvider;

    /**
     * @param RememberedEntitiesProviderInterface $rememberedEntitiesProvider
     * @param string $rememberedEntitiesStorageType
     * @param string $rememberedEntitiesStoragePath
     */
    public function __construct(
        RememberedEntitiesProviderInterface $rememberedEntitiesProvider,
        string $rememberedEntitiesStorageType,
        string $rememberedEntitiesStoragePath
    ) {
        $this->rememberedEntitiesProvider = $rememberedEntitiesProvider;
        $this->rememberedEntitiesStorageType = $rememberedEntitiesStorageType;
        $this->rememberedEntitiesStoragePath = $rememberedEntitiesStoragePath;
    }

    /**
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @param $exception
     * @return mixed|void
     */
    public function rememberEntity(int $entityKey, DataImportInfoInterface $dataImportInfo, $exception)
    {
        $recipientName = $dataImportInfo->getRecipientName();
        $providerName = $dataImportInfo->getDataProviderName();
        //@todo Important. Refactor to use Guentur\MagentoImport\Model\Data\DataImportInfo
        $currentEntityInfo = [
            'path_to_provider' => $providerName,
            'path_to_recipient' => $recipientName,
            'entity_key' => (int) $entityKey,
        ];

        $rememberedEntities = $this->getRememberedEntities();
        $rememberedEntities[] = $currentEntityInfo;
        $this->rememberedEntitiesProvider->importRememberedEntities($rememberedEntities,
                                                                    $this->rememberedEntitiesStoragePath,
                                                                    $this->rememberedEntitiesStorageType);
    }

    /**
     * @return array
     */
    public function getRememberedEntities()
    {
        return $this->rememberedEntitiesProvider->getRememberedEntities(
            $this->rememberedEntitiesStoragePath,
            $this->rememberedEntitiesStorageType
        );
    }

    //@todo refactor
    public function getStoragePath(): string
    {
        return $this->rememberedEntitiesStoragePath;
    }

    public function getStorageType(): string
    {
        return $this->rememberedEntitiesStorageType;
    }
}
