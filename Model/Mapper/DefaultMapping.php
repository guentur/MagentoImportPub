<?php

namespace ElogicCo\MagentoImport\Model\Mapper;

use \InvalidArgumentException;

class DefaultMapping
{
    const DEFAULT_SEPARATOR = '/';

    const DEFAULT_PATTERN = '#\w+' . self::DEFAULT_SEPARATOR . '\w+#i';

    /**
     * array $mapping
     */
    private $mapping;

    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param array $enteredMapping
     * @return array
     */
    public function formatMapping(array $enteredMapping): array
    {
        $formattedMapping = [];
        foreach ($enteredMapping as $map) {
            $this->validateUnformattedMapping($map);
            $formattedMapping[explode(self::DEFAULT_SEPARATOR, $map)[0]] = explode(self::DEFAULT_SEPARATOR, $map)[1];
        }

        return $formattedMapping;
    }

    /**
     * @param $map
     * @return void
     */
    public function validateUnformattedMapping($map)
    {
        if (!is_string($map)) {
            throw new InvalidArgumentException('Map must be string type. You have passed '
                                               . gettype($map));
        }

        if (!preg_match(self::DEFAULT_PATTERN, $map)) {
            throw new InvalidArgumentException('Map must match regex pattern: '
                                               . self::DEFAULT_PATTERN . '. You have passed ' . (string) $map);
        }
    }

    public function applyMappingForItem(array &$dataItem)
    {
        if ($this->mapping !== null) {
            foreach ($this->mapping as $dataProviderColumn => $dataRecipientColumn) {
                //@todo execute validator before building map
                if (!array_key_exists($dataProviderColumn, $dataItem)
                    || strcmp($dataRecipientColumn, $dataProviderColumn) === 0
                ) {
                    continue;
                }
                $dataItem[$dataRecipientColumn] = $dataItem[$dataProviderColumn];
                unset($dataItem[$dataProviderColumn]);
            }
        }

        return $dataItem;
    }
}
