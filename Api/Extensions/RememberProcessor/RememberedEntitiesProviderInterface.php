<?php

namespace Guentur\MagentoImport\Api\Extensions\RememberProcessor;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;

interface RememberedEntitiesProviderInterface
{
    public function getRememberedEntitiesByScope(string $entityScope);

    public function getRememberedEntities(): array;
}
