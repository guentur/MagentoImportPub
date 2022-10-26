<?php

namespace Guentur\MagentoImport\Api\DataImporter;

use Guentur\MagentoImport\Api\Extensions\Rememberer\RememberProcessorInterface;

interface ImporterRememberInterface extends DataImporterInterface
{
    public function getRememberProcessor(): RememberProcessorInterface;

    public function setRememberProcessor(RememberProcessorInterface $rememberProcessor);
}
