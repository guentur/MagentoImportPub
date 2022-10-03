<?php

namespace Guentur\MagentoImport\Api\DataImporter;

interface DataImporterPoolInterface
{
    /**
     * @param string $type
     * @return \Guentur\MagentoImport\Api\DataImporter\DataImporterInterface
     */
    public function getDataImporter(string $type): DataImporterInterface;
}
