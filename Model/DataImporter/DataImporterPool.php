<?php

namespace ElogicCo\MagentoImport\Model\DataImporter;

use ElogicCo\MagentoImport\Api\DataImporter\DataImporterInterface;
use ElogicCo\MagentoImport\Api\DataImporter\DataImporterPoolInterface;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
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
     * @param string $type
     * @return DataImporterInterface
     * @throws LocalizedException
     */
    public function getDataImporter(string $type): DataImporterInterface
    {
        if (!array_key_exists($type, $this->dataImporters)) {
            throw new InvalidArgumentException('Data importer for name ' . $type . ' not found.
             Data importer must be defined in di.xml file for ElogicCo\MagentoImport\Api\DataImporterPoolInterface');
        }

        $dataImporterInstance = $this->create($this->dataImporters[$type]);

        return $dataImporterInstance;
    }

    /**
     * @param $type
     * @return DataImporterInterface
     * @throws LocalizedException
     */
    protected function create($type): DataImporterInterface
    {
        $dataImporterInstance = $this->objectManager->create($type);

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
