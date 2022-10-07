<?php

namespace Guentur\MagentoImport\Api\Extensions;

interface ApplyObserverInterface
{
    /**
     * @param array $dataItem
     * @return array $dataItem
     */
    public function callObserver(array $dataItem): array;

    /**
     * @return string
     */
    public function getEventName(): string;
}
