<?php

declare(strict_types=1);

namespace Guentur\MagentoImport\Api;

interface TableDataProviderInterface extends DataProviderInterface
{
    /**
     * @param string $dataProviderPath
     * @return array
     */
    public function getColumnNames(string $dataProviderPath): array;
}
