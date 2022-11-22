<?php

namespace ElogicCo\MagentoImport\Api\DataProvider;

interface DataProviderPoolInterface
{
    /**
     * @param string $name
     * @return \ElogicCo\MagentoImport\Api\DataProvider\DataProviderInterface
     */
    public function getDataProvider(string $name): DataProviderInterface;
}
