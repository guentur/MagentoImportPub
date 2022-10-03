<?php

namespace Guentur\MagentoImport\Model\Csv;

use Guentur\MagentoImport\Api\TableDataProviderInterface;
use Guentur\MagentoImport\Model\DataProvider\Csv\DataProviderValidator;

class CsvDataProvider implements TableDataProviderInterface
{
    private $validator;

    public function __construct(
        DataProviderValidator $validator
    ) {
        $this->validator = $validator;
    }

    public function getData(string $dataProviderPath): array
    {
        $this->validator->validatePath($dataProviderPath);

        $allRows = [];
        $resource = fopen($dataProviderPath, 'r');
        $header = fgetcsv($resource);
        //@todo optimize
        while ($row = fgetcsv($resource)) {
            $allRows[] = array_combine($header, $row);
        }
        fclose($resource);

        return $allRows;
    }

    public function getColumnNames(string $dataProviderPath): array
    {
        $this->validator->validatePath($dataProviderPath);

        $resource = fopen($dataProviderPath, 'r');
        $header = fgetcsv($resource, 1000, ",");
        fclose($resource);

        return $header;
    }
}
