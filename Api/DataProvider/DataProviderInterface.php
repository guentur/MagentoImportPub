<?php

declare(strict_types=1);

namespace ElogicCo\MagentoImport\Api\DataProvider;

interface DataProviderInterface
{
    const MODE_ALL = 'all';

    const MODE_FIRST_ROW = 'first_row';

    /**
     * @param string $dataProviderPath
     * @return array
     * @throw \InvalidArgumentException
     */
    public function getData(string $dataProviderPath): array;
}
