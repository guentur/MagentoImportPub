<?php

namespace ElogicCo\MagentoImport\Model\Csv;

use ElogicCo\MagentoImport\Api\DataProvider\TableDataProviderInterface;
use ElogicCo\MagentoImport\Model\Csv\Validator\CsvFileValidator;

class CsvDataProvider implements TableDataProviderInterface
{
    private $validator;

    public function __construct(
        CsvFileValidator $validator
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
