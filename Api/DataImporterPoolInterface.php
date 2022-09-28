<?php

namespace Guentur\MagentoImport\Api;

use Guentur\MagentoImport\Api\DataImporterInterface;

interface DataImporterPoolInterface
{
    /**
     * @param string $name
     * @return \Guentur\MagentoImport\Api\DataImporterInterface
     */
    public function getDataImporter(string $name): DataImporterInterface;
}
