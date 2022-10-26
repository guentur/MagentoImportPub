<?php

namespace Guentur\MagentoImport\Model\Extensions\Rememberer;

use Guentur\MagentoImport\Api\Extensions\Rememberer\RememberProcessorPoolInterface;
use Guentur\MagentoImport\Api\Extensions\Rememberer\RememberProcessorInterface;
use Magento\Framework\Exception\LocalizedException;

class RememberProcessorPool implements RememberProcessorPoolInterface
{
    /**
     * @var array
     */
    private $rememberProcessors;

    /**
     * @var string
     */
    private $defaultProcessorMode;

    /**
     * @param string $defaultProcessorMode
     * @param array $rememberProcessors
     */
    public function __construct(
        string $defaultProcessorMode,
        array $rememberProcessors = []
    ) {
        $this->rememberProcessors = $rememberProcessors;
        $this->defaultProcessorMode = $defaultProcessorMode;
    }

    /**
     * @param string $processorMode
     * @return RememberProcessorInterface
     * @throws LocalizedException
     */
    public function getRememberProcessor(string $processorMode): RememberProcessorInterface
    {
        $this->validateProcessorMode($this->defaultProcessorMode);

        return $this->rememberProcessors[$processorMode];
    }

    /**
     * @return array
     */
    public function getProcessorsModes(): array
    {
        return array_keys($this->rememberProcessors);
    }

    /**
     * @return string
     * @throws LocalizedException|\InvalidArgumentException
     */
    public function getDefaultProcessorMode(): string
    {
        $this->validateProcessorMode($this->defaultProcessorMode);

        return $this->defaultProcessorMode;
    }

    /**
     * @todo Create a validator class; one for all the Pools, if possible
     * @param $processorMode
     * @return void
     * @throws LocalizedException|\InvalidArgumentException
     */
    public function validateProcessorMode($processorMode)
    {
        if (!array_key_exists($processorMode, $this->rememberProcessors)) {
            throw new \InvalidArgumentException('RememberProcessor for mode ' . $processorMode . ' not found.
             RememberProcessor must be defined in di.xml file for ' . self::class);
        }

        $rememberProcessorInstance = $this->rememberProcessors[$processorMode];

        if (!($rememberProcessorInstance instanceof RememberProcessorInterface)) {
            throw new LocalizedException(
                __('Instance of RememberProcessor must implement "' . RememberProcessorInterface::class . '".')
            );
        }
    }
}
