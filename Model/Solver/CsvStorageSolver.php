<?php

namespace ElogicCo\MagentoImport\Model\Solver;

use ElogicCo\MagentoImport\Api\EntitiesStorageSolverInterface;

class CsvStorageSolver implements EntitiesStorageSolverInterface
{
    public function execute(string $storagePath): string
    {
        $resource = fopen($storagePath, "a");
        fclose($resource);

        return 'Success';
    }
}
