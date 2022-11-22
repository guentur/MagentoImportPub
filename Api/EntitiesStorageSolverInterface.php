<?php

namespace ElogicCo\MagentoImport\Api;

interface EntitiesStorageSolverInterface
{
    public function execute(string $storagePath): string;
}
