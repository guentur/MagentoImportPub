<?php

namespace Guentur\MagentoImport\Model\Extensions\Rememberer;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\Data\DataImportInfoInterfaceFactory;
use Guentur\MagentoImport\Api\DataImporter\DataImporterPoolInterface;
use Guentur\MagentoImport\Model\EntityManager;
use Guentur\MagentoImport\Api\Extensions\Rememberer\RememberedEntitiesProviderInterface;
use Guentur\MagentoImport\Api\Extensions\Rememberer\RememberProcessorInterface;

class RememberReplace implements RememberProcessorInterface
{
    // @todo setup only filename. Make absolute path by function like getMediaPath() in Magento
    const IMPORT_STATE_FILE_NAME = __DIR__ . '/../../../etc/import_state.csv';

    private $entityManager;

    private $rememberedEntitiesStorageType;

    private $rememberedEntitiesStoragePath;

    /**
     * @var RememberedEntitiesProviderInterface
     */
    private $rememberedEntitiesProvider;

    public function __construct(
        EntityManager $entityManager,
        RememberedEntitiesProviderInterface $rememberedEntitiesProvider,
        string $rememberedEntitiesStorageType,
        string $rememberedEntitiesStoragePath
    ) {
        $this->entityManager = $entityManager;
        $this->rememberedEntitiesProvider = $rememberedEntitiesProvider;
        $this->rememberedEntitiesStorageType = $rememberedEntitiesStorageType;
        $this->rememberedEntitiesStoragePath = $rememberedEntitiesStoragePath;
    }

    /**
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @param $exception
     * @return void
     */
    public function rememberEntity(int $entityKey, DataImportInfoInterface $dataImportInfo, $exception)
    {
        $pathToRecipient = $dataImportInfo->getPathToRecipient();
        $pathToProvider = $dataImportInfo->getPathToDataProvider();
        //@todo Important. Refactor to use Guentur\MagentoImport\Model\Data\DataImportInfo
        $currentEntityInfo = [
            'path_to_provider' => $pathToProvider,
            'path_to_recipient' => $pathToRecipient,
            'entity_key' => (int) $entityKey,
        ];

        $rememberedEntities = $this->getRememberedEntities();
        $allRememberedEntities = $this->mergeWithAllRememberedEntities($rememberedEntities, $currentEntityInfo);
        $this->rememberedEntitiesProvider->importRememberedEntities($allRememberedEntities,
                                                                    $this->rememberedEntitiesStoragePath,
                                                                    $this->rememberedEntitiesStorageType
        );

        throw $exception;
    }

    public function mergeWithAllRememberedEntities(array $rememberedEntities, array $currentEntityInfo): array
    {
        $rememberedEntities[] = $currentEntityInfo;
        // Merge the entity that is processing with already saved entities by path_to_provider and path_to_recipient
        $scopeFormatEntityList = $this->entityManager->getScopeFormatEntityList($rememberedEntities);
        $rememberedEntities = $this->entityManager->getImportFormatEntityList($scopeFormatEntityList);

        return $rememberedEntities;
    }

    public function getRememberedEntities()
    {
        return $this->rememberedEntitiesProvider->getRememberedEntities(
            $this->rememberedEntitiesStoragePath,
            $this->rememberedEntitiesStorageType
        );
    }

    public function getStoragePath(): string
    {
        return $this->rememberedEntitiesStoragePath;
    }

    public function getStorageType(): string
    {
        return $this->rememberedEntitiesStorageType;
    }
}
