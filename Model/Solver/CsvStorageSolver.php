<?php

namespace Guentur\MagentoImport\Model\Solver;

use Guentur\MagentoImport\Api\EntitiesStorageSolverInterface;

class CsvStorageSolver implements EntitiesStorageSolverInterface
{
    public function execute(string $storagePath): string
    {
        $resource = fopen($storagePath, "a");
        fclose($resource);

        return 'Success';
    }
}
