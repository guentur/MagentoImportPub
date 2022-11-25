<?php

namespace ElogicCo\MagentoImport\Api\Extensions\RememberProcessor;

use ElogicCo\MagentoImport\Api\Data\DataImportInfoInterface;

interface RememberedEntitiesProviderInterface
{
    public function getRememberedEntitiesByScope(string $entityScope);

    public function getRememberedEntities(): array;
}
