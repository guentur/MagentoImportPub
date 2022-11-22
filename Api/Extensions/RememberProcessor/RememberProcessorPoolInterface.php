<?php

namespace ElogicCo\MagentoImport\Api\Extensions\RememberProcessor;

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
    public function getProcessModes(): array;

    /**
     * @return string
     * @throws LocalizedException|\InvalidArgumentException
     */
    public function getDefaultProcessMode(): string;
}
