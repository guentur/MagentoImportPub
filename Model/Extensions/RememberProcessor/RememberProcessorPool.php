<?php

namespace ElogicCo\MagentoImport\Model\Extensions\RememberProcessor;

use ElogicCo\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorPoolInterface;
use ElogicCo\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorInterface;
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
        $this->validateProcessMode($this->defaultProcessorMode);

        return $this->rememberProcessors[$processorMode];
    }

    /**
     * @return array
     */
    public function getProcessModes(): array
    {
        return array_keys($this->rememberProcessors);
    }

    public function getProcessModeByClass($processClass): string
    {
        $processMode = array_search($processClass, $this->rememberProcessors);
        if ($processMode === false) {
            throw new \InvalidArgumentException('RememberProcessor mode for class ' . $processClass . ' not found.
             RememberProcessor must be defined in di.xml file for ' . self::class);
        }
        return $processMode;
    }

    /**
     * @return string
     * @throws LocalizedException|\InvalidArgumentException
     */
    public function getDefaultProcessMode(): string
    {
        $this->validateProcessMode($this->defaultProcessorMode);

        return $this->defaultProcessorMode;
    }

    /**
     * @todo Create a validator class; one for all the Pools, if possible
     * @param $processorMode
     * @return void
     * @throws LocalizedException|\InvalidArgumentException
     */
    public function validateProcessMode($processorMode)
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
