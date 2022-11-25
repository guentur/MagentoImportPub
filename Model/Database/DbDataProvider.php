<?php

namespace ElogicCo\MagentoImport\Model\Database;

use ElogicCo\MagentoImport\Api\DataProvider\TableDataProviderInterface;
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

        //@todo optimize
        $data = $dbAdapter->describeTable($tableName);
        $columnNames = array_keys($data);

        return $columnNames;
    }
}
