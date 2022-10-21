<?php

namespace Guentur\MagentoImport\Api\Extensions\Rememberer;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;

interface RememberedEntitiesProviderInterface
{
    public function getRememberedEntity(DataImportInfoInterface $dataImportInfo);

    public function getArraySinceRememberedEntity(array $array, DataImportInfoInterface $dataImportInfo): array;

    public function getRememberedEntities(): array;
}
