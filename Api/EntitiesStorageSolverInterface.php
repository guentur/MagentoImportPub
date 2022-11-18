<?php

namespace Elogic\MagentoImport\Api;

interface EntitiesStorageSolverInterface
{
    public function execute(string $storagePath): string;
}
