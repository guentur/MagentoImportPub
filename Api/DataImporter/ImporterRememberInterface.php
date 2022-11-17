<?php

namespace Elogic\MagentoImport\Api\DataImporter;

use Elogic\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorInterface;

interface ImporterRememberInterface extends DataImporterInterface
{
    public function getRememberProcessor(): RememberProcessorInterface;

    public function setRememberProcessor(RememberProcessorInterface $rememberProcessor);
}
