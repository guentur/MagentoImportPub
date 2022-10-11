<?php

namespace Guentur\MagentoImport\Api\DataProvider;

interface DataProviderPoolInterface
{
    /**
     * @param string $name
     * @return \Guentur\MagentoImport\Api\DataProvider\DataProviderInterface
     */
    public function getDataProvider(string $name): DataProviderInterface;
}
