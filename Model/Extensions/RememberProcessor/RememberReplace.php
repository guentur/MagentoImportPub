<?php

namespace Guentur\MagentoImport\Model\Extensions\RememberProcessor;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Api\Data\DataImportInfoInterfaceFactory;
use Guentur\MagentoImport\Api\DataImporter\DataImporterPoolInterface;
use Guentur\MagentoImport\Api\DataProvider\DataProviderPoolInterface;
use Guentur\MagentoImport\Model\EntityManager;
use Guentur\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorInterface;
use Guentur\MagentoImport\Model\EntityScopeManager;
use Guentur\MagentoImport\Model\Solver\StorageSolverPool;

class RememberReplace extends RememberProcessorAbstract implements RememberProcessorInterface
{
    // @todo setup only filename. Make absolute path by function like getMediaPath() in Magento
    const IMPORT_STATE_FILE_NAME = __DIR__ . '/../../../etc/import_state.csv';

    public function __construct(
        DataImporterPoolInterface $dataImporterPool,
        DataImportInfoInterfaceFactory $dataImportInfoF,
        DataProviderPoolInterface $dataProviderPool,
        EntityManager $entityManager,
        StorageSolverPool $storageSolverPool,
        EntityScopeManager $entityScopeManager,
        string $rememberedEntitiesStorageType,
        string $rememberedEntitiesStoragePath
    ) {
        $this->rememberedEntitiesStorageType = $rememberedEntitiesStorageType;
        $this->rememberedEntitiesStoragePath = $rememberedEntitiesStoragePath;
        parent::__construct(
            $dataImporterPool,
            $dataImportInfoF,
            $dataProviderPool,
            $entityManager,
            $storageSolverPool,
            $entityScopeManager
        );
    }

    /**
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @param $exception
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
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
        $this->importRememberedEntities($allRememberedEntities);

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

    public function getStoragePath(): string
    {
        return $this->rememberedEntitiesStoragePath;
    }

    public function forgetEntity(int $entityKey, DataImportInfoInterface $dataImportInfo)
    {
        //@todo
    }
}
