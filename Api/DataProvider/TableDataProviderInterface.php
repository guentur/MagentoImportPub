<?php

declare(strict_types=1);

namespace Guentur\MagentoImport\Api\DataProvider;

interface TableDataProviderInterface extends DataProviderInterface
{
    /**
     * $columnNames = [
     *      1 => column_name,
     *      ...
     * ]
     *
     * @param string $dataProviderPath
     * @return array
     */
    public function getColumnNames(string $dataProviderPath): array;
}