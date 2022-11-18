<?php

namespace Elogic\MagentoImport\Api\DataProvider;

interface DataProviderPoolInterface
{
    /**
     * @param string $name
     * @return \Elogic\MagentoImport\Api\DataProvider\DataProviderInterface
     */
    public function getDataProvider(string $name): DataProviderInterface;
}
