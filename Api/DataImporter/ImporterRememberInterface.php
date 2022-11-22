<?php

namespace ElogicCo\MagentoImport\Api\DataImporter;

use ElogicCo\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorInterface;

interface ImporterRememberInterface extends DataImporterInterface
{
    public function getRememberProcessor(): RememberProcessorInterface;

    public function setRememberProcessor(RememberProcessorInterface $rememberProcessor);
}
