<?php

namespace ElogicCo\MagentoImport\Model\DataProvider;

use ElogicCo\MagentoImport\Api\DataProvider\DataProviderInterface;
use ElogicCo\MagentoImport\Api\DataProvider\DataProviderPoolInterface;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;

class DataProviderPool implements DataProviderPoolInterface
{
    private $dataProviders;

    public function __construct(
        array $dataProviders = []
    ) {
        $this->dataProviders = $dataProviders;
    }

    /**
     * @param string $name
     * @return DataProviderInterface
     * @throws LocalizedException
     */
    public function getDataProvider(string $name): DataProviderInterface
    {
        if (!array_key_exists($name, $this->dataProviders)) {
            throw new InvalidArgumentException('Data provider for name ' . $name . ' not found.
             Data provider must be defined in di.xml file for ' . DataProviderPoolInterface::class);
        }

        $dataProviderInstance = $this->dataProviders[$name];

        if (!($dataProviderInstance instanceof DataProviderInterface)) {
            throw new LocalizedException(
                __('Instance of DataImporter must implement "' . DataProviderInterface::class . '".')
            );
        }

        return $dataProviderInstance;
    }
}
