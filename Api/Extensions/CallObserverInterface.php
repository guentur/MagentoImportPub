<?php

namespace Guentur\MagentoImport\Api\Extensions;

interface CallObserverInterface
{
    /**
     * @param array $dataItem
     * @return array $dataItem
     */
    public function applyObserver(array $dataItem): array;

    /**
     * @return string
     */
    public function getEventName(): string;
}
