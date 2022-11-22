<?php

namespace ElogicCo\MagentoImport\Api\DataImporter;

interface DataImporterPoolInterface
{
    /**
     * @param string $type
     * @return \ElogicCo\MagentoImport\Api\DataImporter\DataImporterInterface
     */
    public function getDataImporter(string $type): DataImporterInterface;
}
