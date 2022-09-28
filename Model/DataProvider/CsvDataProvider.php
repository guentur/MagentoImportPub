<?php

namespace Guentur\MagentoImport\Model\DataProvider;

use Guentur\MagentoImport\Api\TableDataProviderInterface;

class CsvDataProvider implements TableDataProviderInterface
{
    public function getData(string $dataProviderPath): array
    {
        $allRows = [];

        //@todo throw an exception
        if (file_exists($dataProviderPath)) {
            $resource = fopen($dataProviderPath, 'r');
            $header = fgetcsv($resource);
            //@todo optimize
            while ($row = fgetcsv($resource)) {
                $allRows[] = array_combine($header, $row);
            }
            fclose($resource);
        } else {
            throw new \InvalidArgumentException('File ' . $dataProviderPath . ' does not exist');
        }

        return $allRows;
    }

    public function getColumnNames(string $dataProviderPath): array
    {
        $resource = fopen($dataProviderPath, 'r');
        $header = fgetcsv($resource, 1000, ",");
        fclose($resource);

        return $header;
    }
}
