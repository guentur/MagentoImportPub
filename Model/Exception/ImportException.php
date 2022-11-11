<?php

declare(strict_types=1);

namespace Guentur\MagentoImport\Model\Exception;

//@todo
//use Guentur\MagentoImport\Model\Exception\ImportExceptionInterface;

class ImportException extends \RuntimeException
{
    private $dataItemKey;

    public function __construct(
        int $dataItemKey,
        $message = "",
        $code = 0,
        \Throwable $previous = null
    ) {
        $this->dataItemKey = $dataItemKey;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getDataItemKey(): int
    {
        return $this->dataItemKey;
    }
}
