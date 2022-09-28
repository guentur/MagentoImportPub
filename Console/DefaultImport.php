<?php

namespace Guentur\MagentoImport\Console;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use Guentur\MagentoImport\Api\DataProviderPoolInterface;
use Guentur\MagentoImport\Api\DataImporterPoolInterface;
use Guentur\MagentoImport\Api\DataImporterInterface;
use Guentur\MagentoImport\Model\Mapper\DefaultMapping;
use Guentur\MagentoImport\Api\Data\DataImportInfoInterfaceFactory;
use Guentur\MagentoImport\Api\ImportWithProgressBarInterface;
use Guentur\MagentoImport\Model\ProgressBarWrapper;
use Magento\Framework\DB\Adapter\TableNotFoundException;

class DefaultImport extends Command
{
    /**
     * required. Database table name OR path to file where to import values
     * @see OPTION_RECIPIENT
     */
    private const OPTION_PATH_TO_RECIPIENT = 'path-to-recipient';

    /**
     * default = db
     */
    private const OPTION_RECIPIENT = 'recipient';

    /**
     * Path to file OR Database table name with data.
     * @see OPTION_DATA_PROVIDER
     */
    private const OPTION_PATH_TO_DATA_PROVIDER = 'path-to-data-provider';

    /**
     * default = csv
     */
    private const OPTION_DATA_PROVIDER = 'data-provider';

    private const OPTION_COLUMNS_MAPPING = 'columns-mapping';

    /**
     * @var DataProviderPoolInterface
     */
    private $dataProviderPool;

    /**
     * @var DataImporterPoolInterface
     */
    private $dataImporterPool;

    /**
     * @var ProgressBarWrapper
     */
    private $progressBarWrapper;

    /**
     * @var DataImportInfoInterfaceFactory
     */
    private $dataImportInfoF;

    /**
     * @var DefaultMapping
     */
    private $defaultMapping;

    /**
     * @param DataProviderPoolInterface $dataProviderPool
     * @param DataImporterPoolInterface $dataImporterPool
     * @param DataImportInfoInterfaceFactory $dataImportInfoF
     * @param ProgressBarWrapper $progressBarWrapper
     * @param DefaultMapping $defaultMapping
     * @param string|null $name
     */
    public function __construct(
        DataProviderPoolInterface $dataProviderPool,
        DataImporterPoolInterface $dataImporterPool,
        DataImportInfoInterfaceFactory $dataImportInfoF,
        ProgressBarWrapper $progressBarWrapper,
        DefaultMapping $defaultMapping,
        string $name = null
    ) {
        $this->dataProviderPool = $dataProviderPool;
        $this->dataImporterPool = $dataImporterPool;
        $this->dataImportInfoF = $dataImportInfoF;
        $this->progressBarWrapper = $progressBarWrapper;
        $this->defaultMapping = $defaultMapping;
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('guentur:import')
            ->setDescription(__('Quick data import with memory and flexible settings'))
            ->setDefinition($this->getCommandOptions());

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statusCode = Cli::RETURN_FAILURE;
        $dataForImport = $this->getDataForImport($input, $output);
        try {
            $statusCode = $this->importData($dataForImport, $input, $output);
        }
        catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        if ($statusCode === Cli::RETURN_SUCCESS) {
            $output->writeln('<fg=green>' . __("Import completed.") . '</>');
        } else {
            $output->writeln('<error>' . __("Something went wrong while import processing.") . '</error>');
        }

        return $statusCode;
    }


    /**
     * Creation admin user in interaction mode.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $optionMapping = $input->getOption(self::OPTION_COLUMNS_MAPPING);
        $enteredMapping = $this->defaultMapping->formatMapping($optionMapping);
        $enteredMapping = $this->interactMappingOption($input, $output, $enteredMapping);

        $this->defaultMapping->setMapping($enteredMapping);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    public function getDataForImport(InputInterface $input, OutputInterface $output): array
    {
        $dataProviderType = $input->getOption(self::OPTION_DATA_PROVIDER);
        $dataProviderPath = $input->getOption(self::OPTION_PATH_TO_DATA_PROVIDER);
        $output->writeln(__("Data-provider type: %1", $dataProviderType));
        $output->writeln(__("Path to data-provider: %1", $dataProviderPath));

        $dataForImport = [];
        try {
            /** @var \Guentur\MagentoImport\Api\TableDataProviderInterface $dataProvider */
            $dataProvider = $this->dataProviderPool->getDataProvider($dataProviderType);
            $dataForImport = $dataProvider->getData($dataProviderPath);
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        return $dataForImport;
    }

    /**
     * @param array $dataForImport
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function importData(array $dataForImport, InputInterface $input, OutputInterface $output): int
    {
        $recipientType = $input->getOption(self::OPTION_RECIPIENT);
        $pathToRecipient = $input->getOption(self::OPTION_PATH_TO_RECIPIENT);
        $pathToProvider = $input->getOption(self::OPTION_PATH_TO_DATA_PROVIDER);
        $output->writeln(__("Recipient type: %1", $recipientType));
        $output->writeln(__("Path to recipient: %1", $pathToRecipient));

        /** \Guentur\MagentoImport\Api\Data\DataImportInfoInterface $dataImportInfo */
        $dataImportInfo = $this->dataImportInfoF->create();
        $dataImportInfo->setPathToDataProvider($pathToProvider);
        $dataImportInfo->setPathToRecipient($pathToRecipient);

        $output->writeln(__("Recipient name: %1", $dataImportInfo->getRecipientName()));
        $output->writeln(__("Data-provider name: %1", $dataImportInfo->getDataProviderName()));

        /** @var \Guentur\MagentoImport\Api\DataImporterInterface $dataImporter */
        $dataImporter = $this->dataImporterPool->getDataImporter($recipientType);
        $dataImporter->setDataImportInfo($dataImportInfo);

        if ($dataImporter instanceof ImportWithProgressBarInterface) {
            $this->progressBarWrapper->setOutput($output);
            $dataImporter->setProgressBarWrapper($this->progressBarWrapper);
        }
        try {
            $dataImporter->importData($dataForImport);
        } catch (TableNotFoundException $e) {
            $message = __('Cannot find table by path to Recipient: %1', $pathToRecipient);
            $secondMessage = __('Check if you rightly set up Recipient Type. Your Recipient Type is "%1"',
                                '<fg=cyan>' . $recipientType . '</>');
            $infoMessage = __('Use option %1 to set up a Recipient Type. Run %2 for more info',
                '<fg=cyan>--recipient</>',
                              '<fg=cyan>' . $this->getName() . ' --help</>');
            $output->writeln('<error>' . $message . '</error>');
            $output->writeln('<error>' . $secondMessage . '</error>');
            $output->writeln('<info>' . $infoMessage . '</info>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * You can set up you plugin here ;)
     * @see \Guentur\MagentoImport\Console\DefaultImport::interact()
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $enteredMapping
     * @return array
     */
    public function interactMappingOption(InputInterface $input, OutputInterface $output, array $enteredMapping): array
    {
        $dataProviderType = $input->getOption(self::OPTION_DATA_PROVIDER);
        $dataProviderPath = $input->getOption(self::OPTION_PATH_TO_DATA_PROVIDER);
        /** @var \Guentur\MagentoImport\Api\TableDataProviderInterface $dataProvider */
        $dataProvider = $this->dataProviderPool->getDataProvider($dataProviderType);
        $dataProviderColumns = $dataProvider->getColumnNames($dataProviderPath);

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        foreach ($dataProviderColumns as $dataProviderColumn) {
            if (!isset($enteredMapping[$dataProviderColumn])) {
                $question = new Question('<question>Recipient column for ' . $dataProviderColumn . ' data-provider column:</question> ',
                                         $dataProviderColumn);
                $enteredMapping[$dataProviderColumn] = $questionHelper->ask($input, $output, $question);
            }
        }
        return $enteredMapping;
    }

    /**
     * @return array
     */
    public function getCommandOptions()
    {
        return [
            new InputOption(
                self::OPTION_RECIPIENT,
                null, InputOption::VALUE_OPTIONAL,
                'value: either `database` or `csv`',
                'database'),
            new InputOption(
                self::OPTION_PATH_TO_RECIPIENT,
                null, InputOption::VALUE_REQUIRED,
                'Database table name or path to file where to import data'),
            new InputOption(
                self::OPTION_DATA_PROVIDER,
                null, InputOption::VALUE_OPTIONAL,
                'value: either `database` or `csv`',
                'csv'),
            new InputOption(
                self::OPTION_PATH_TO_DATA_PROVIDER,
                null, InputOption::VALUE_REQUIRED,
                'Path to file or Database table from where you export data'),
            new InputOption(
                self::OPTION_COLUMNS_MAPPING,
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Format: data_provider_column/data_recipient_column',
                []),

            //@todo
//            new InputOption(
//                'data_bunch_limit',
//                null,
//                InputOption::VALUE_OPTIONAL,
//                '',
//                200),
//            new InputOption(
//                'continue_since_state',
//                null,
//                InputOption::VALUE_OPTIONAL,
//                'Value is the key of particular data-provider element from which it will start importing
//                not taking into account value of import_state',
//                null),
        ];
    }
}
