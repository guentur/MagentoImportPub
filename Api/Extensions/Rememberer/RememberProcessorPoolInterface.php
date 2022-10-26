<?php

namespace Guentur\MagentoImport\Api\Extensions\Rememberer;

use Magento\Framework\Exception\LocalizedException;

interface RememberProcessorPoolInterface
{
    /**
     * @param string $processorMode
     * @return RememberProcessorInterface
     * @throws LocalizedException|\InvalidArgumentException
     */
    public function getRememberProcessor(string $processorMode): RememberProcessorInterface;

    /**
     * @return array
     */
    public function getProcessorsModes(): array;

    /**
     * @return string
     * @throws LocalizedException|\InvalidArgumentException
     */
    public function getDefaultProcessorMode(): string;
}
