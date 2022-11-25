<?php

namespace ElogicCo\MagentoImport\Model\Solver;

use ElogicCo\MagentoImport\Api\EntitiesStorageSolverInterface;
use Magento\Framework\Exception\LocalizedException;

class StorageSolverPool
{
    private $solvers;

    public function __construct(
        array $solvers = []
    ) {
        $this->solvers = $solvers;
    }

    /**
     * @param string $name
     * @return EntitiesStorageSolverInterface
     * @throws LocalizedException
     */
    public function getSolver(string $name): EntitiesStorageSolverInterface
    {
        if (!array_key_exists($name, $this->solvers)) {
            throw new \InvalidArgumentException('Solver for name ' . $name . ' not found.
             Solver must be defined in di.xml file for ' . self::class);
        }

        $solverInstance = $this->solvers[$name];

        if (!($solverInstance instanceof EntitiesStorageSolverInterface)) {
            throw new LocalizedException(
                __('Instance of Solver must implement "' . EntitiesStorageSolverInterface::class . '".')
            );
        }

        return $solverInstance;
    }
}
