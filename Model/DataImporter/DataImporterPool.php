<?php

namespace Guentur\MagentoImport\Model\DataImporter;

use Guentur\MagentoImport\Api\DataImporterPoolInterface;
use Guentur\MagentoImport\Api\DataImporterInterface;
use Magento\Framework\Exception\LocalizedException;
use \InvalidArgumentException;
use Magento\Framework\ObjectManagerInterface;

class DataImporterPool implements DataImporterPoolInterface
{
    private $dataImporters;

    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        array $dataImporters = []
    ) {
        $this->objectManager = $objectManager;
        $this->dataImporters = $dataImporters;
    }

    /**
     * @param string $name
     * @return DataImporterInterface
     * @throws LocalizedException
     */
    public function getDataImporter(string $name): DataImporterInterface
    {
        if (!array_key_exists($name, $this->dataImporters)) {
            throw new InvalidArgumentException('Data importer for name ' . $name . ' not found.
             Data importer must be defined in di.xml file for Guentur\MagentoImport\Api\DataImporterPoolInterface');
        }

        $dataImporterInstance = $this->create($this->dataImporters[$name]);

        return $dataImporterInstance;
    }

    /**
     * @param $name
     * @return DataImporterInterface
     * @throws LocalizedException
     */
    protected function create($name): DataImporterInterface
    {
        $dataImporterInstance = $this->objectManager->create($name);

        if (!($dataImporterInstance instanceof DataImporterInterface)) {
            throw new LocalizedException(
                __('Instance of DataImporter must implement "' . DataImporterInterface::class . '".')
            );
        }
        return $dataImporterInstance;
    }

    /**
     * Return array of data importer names.
     *
     * @return array
     * @since 102.0.4
     */
    public function getDataImporterNames(): array
    {
        return array_keys($this->dataImporters);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function getAllDataImporters(): array
    {
        $data = [];
        foreach ($this->dataImporters as $sectionName => $sectionClass) {
            $data[$sectionName] = $this->create($sectionClass);
        }
        return $data;
    }
}
