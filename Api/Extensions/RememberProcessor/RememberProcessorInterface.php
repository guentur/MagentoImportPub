<?php

namespace Guentur\MagentoImport\Api\Extensions\RememberProcessor;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;

interface RememberProcessorInterface
{
    /**
     * @todo remove Exception param
     *
     * @param int $entityKey
     * @param DataImportInfoInterface $dataImportInfo
     * @param \RuntimeException $exception
     * @return mixed
     */
    public function rememberEntity(int $entityKey, DataImportInfoInterface $dataImportInfo, \RuntimeException $exception);

    /**
     * @param array $array
     * @param DataImportInfoInterface $dataImportInfo
     * @return array
     */
    public function getArraySinceRememberedEntity(array $array, DataImportInfoInterface $dataImportInfo): array;
}
