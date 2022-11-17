<?php

namespace Elogic\MagentoImport\Api\Extensions\RememberProcessor;

use Elogic\MagentoImport\Api\Data\DataImportInfoInterface;

interface RememberedEntitiesProviderInterface
{
    public function getRememberedEntitiesByScope(string $entityScope);

    public function getRememberedEntities(): array;
}
