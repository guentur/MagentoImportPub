<?php

namespace Elogic\MagentoImport\Api\DataImporter;

interface DataImporterPoolInterface
{
    /**
     * @param string $type
     * @return \Elogic\MagentoImport\Api\DataImporter\DataImporterInterface
     */
    public function getDataImporter(string $type): DataImporterInterface;
}
