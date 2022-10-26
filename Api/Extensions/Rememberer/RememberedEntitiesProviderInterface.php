<?php

namespace Guentur\MagentoImport\Api\Extensions\Rememberer;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;

interface RememberedEntitiesProviderInterface
{
    public function getRememberedEntitiesByScope(DataImportInfoInterface $dataImportInfo,
                                                 string $rememberedEntitiesStoragePath,
                                                 string $rememberedEntitiesStorageType
    );

    public function getRememberedEntities(string $rememberedEntitiesStoragePath,
                                          string $rememberedEntitiesStorageType
    ): array;
}
