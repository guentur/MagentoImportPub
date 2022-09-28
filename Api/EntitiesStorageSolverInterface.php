<?php

namespace Guentur\MagentoImport\Api;

interface EntitiesStorageSolverInterface
{
    public function execute(string $storagePath): string;
}
