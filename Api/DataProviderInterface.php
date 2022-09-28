<?php

declare(strict_types=1);

namespace Guentur\MagentoImport\Api;

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
