<?php

namespace Guentur\MagentoImport\Model\DataProvider;

use Guentur\MagentoImport\Api\TableDataProviderInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class DbDataProvider implements TableDataProviderInterface
{
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function getData(string $dataProviderPath): array
    {
        $tableName = $this->moduleDataSetup->getTable($dataProviderPath);
        $dbAdapter = $this->moduleDataSetup->getConnection();

        $selectDataToImport = $dbAdapter->select()->from($tableName);
        $data = $dbAdapter->fetchAll($selectDataToImport);

        return $data;
    }

    public function getColumnNames(string $dataProviderPath): array
    {
        $tableName = $this->moduleDataSetup->getTable($dataProviderPath);
        $dbAdapter = $this->moduleDataSetup->getConnection();

        $data = $dbAdapter->describeTable($tableName);

        return $data;
    }
}
