<?php

namespace ElogicCo\MagentoImport\Model\Extensions;

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressBarWrapper
{
    private $objectManager;

    /**
     * @var OutputInterface
     */
    private $consoleOutput;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $maxCount
     * @return ProgressBar
     */
    public function getProgressBarInstance($maxCount): ProgressBar
    {
        if (!($this->consoleOutput instanceof OutputInterface)) {
            throw new \InvalidArgumentException(__('consoleOutput is not provided to the ' . self::class
                                                   . '. Use method setOutput(OutputInterface $consoleOutput) in '
                                                   . self::class));
        }
        /** @var ProgressBar $progressBar */
        $progressBar = $this->objectManager->create(
            ProgressBar::class,
            [
                'output' => $this->consoleOutput,
                'max' => $maxCount
            ]
        );

        return $progressBar;
    }

    public function setOutput(OutputInterface $consoleOutput)
    {
        $this->consoleOutput = $consoleOutput;
    }

//    public function getOutput(): OutputInterface
//    {
//        return $this->consoleOutput;
//    }
}
