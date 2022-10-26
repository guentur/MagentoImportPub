<?php

namespace Guentur\MagentoImport\Api\Extensions\RememberProcessor;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;

interface RememberedEntitiesProviderInterface
{
    public function getRememberedEntitiesByScope(DataImportInfoInterface $dataImportInfo);

    public function getRememberedEntities(): array;
}
