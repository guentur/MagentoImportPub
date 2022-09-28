<?php

namespace Guentur\MagentoImport\Api;

use Guentur\MagentoImport\Api\DataProviderInterface;

interface DataProviderPoolInterface
{
    /**
     * @param string $name
     * @return \Guentur\MagentoImport\Api\DataProviderInterface
     */
    public function getDataProvider(string $name): DataProviderInterface;
}
