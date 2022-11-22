<?php

namespace ElogicCo\MagentoImport\Console;

use ElogicCo\MagentoImport\Api\Data\DataImportInfoInterfaceFactory;
use ElogicCo\MagentoImport\Api\DataImporter\DataImporterPoolInterface;
use ElogicCo\MagentoImport\Api\DataProvider\DataProviderPoolInterface;
use ElogicCo\MagentoImport\Api\Extensions\RememberProcessor\RememberProcessorPoolInterface;
use ElogicCo\MagentoImport\Api\Extensions\ImportWithProgressBarInterface;
use ElogicCo\MagentoImport\Api\DataImporter\ImporterRememberInterface;
use ElogicCo\MagentoImport\Model\Extensions\ProgressBarWrapper;
use ElogicCo\MagentoImport\Model\Mapper\DefaultMapping;
use Magento\Framework\Console\Cli;
use Magento\Framework\DB\Adapter\TableNotFoundException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

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
    private const OPTION_RECIPIENT = 'recipient-type';

    /**
     * Path to file OR Database table name with data.
     * @see OPTION_DATA_PROVIDER
     */
    private const OPTION_PATH_TO_DATA_PROVIDER = 'path-to-data-provider';

    /**
     * default = csv
     */
    private const OPTION_DATA_PROVIDER = 'data-provider-type';

    private const OPTION_COLUMNS_MAPPING = 'columns-mapping';

    private const OPTION_IMPORT_REMEMBERED_ENTITIES = 'import-remembered-entities';

    private const OPTION_REMEMBER_MODE = 'remember-mode';

    private const REMEMBER_MODE_DONT_REMEMBER_FAILED_ENTITY = 'dont-remember-failed-entity';

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
     * @var RememberProcessorPoolInterface
     */
    private $rememberProcessorPool;

    /**
     * @param DataProviderPoolInterface $dataProviderPool
     * @param DataImporterPoolInterface $dataImporterPool
     * @param RememberProcessorPoolInterface $rememberProcessorPool
     * @param DataImportInfoInterfaceFactory $dataImportInfoF
     * @param ProgressBarWrapper $progressBarWrapper
     * @param DefaultMapping $defaultMapping
     * @param string|null $name
     */
    public function __construct(
        DataProviderPoolInterface $dataProviderPool,
        DataImporterPoolInterface $dataImporterPool,
        RememberProcessorPoolInterface $rememberProcessorPool,
        DataImportInfoInterfaceFactory $dataImportInfoF,
        ProgressBarWrapper $progressBarWrapper,
        DefaultMapping $defaultMapping,
        string $name = null
    ) {
        $this->dataProviderPool = $dataProviderPool;
        $this->dataImporterPool = $dataImporterPool;
        $this->rememberProcessorPool = $rememberProcessorPool;
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
        $this->setName('elogic:import')
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
        } catch (\Exception $e) {
            $output->writeln('<error>' . 'There was error processed during the import progress' . '</error>');
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $output->writeln('<error>' . $e->getTraceAsString() . '</error>');
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
        try {
            $enteredMapping = $this->interactMappingOption($input, $output, $enteredMapping);
        } catch (TableNotFoundException $e) {
            $dataProviderType = $input->getOption(self::OPTION_DATA_PROVIDER);
            $pathToDataProvider = $input->getOption(self::OPTION_PATH_TO_DATA_PROVIDER);
            $message = $this->getTableNotFoundException($dataProviderType, $pathToDataProvider, 'DataProvider');
            $output->writeln('<error>' . $message . '</error>');
            exit(1);
        }

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
        $pathToDataProvider = $input->getOption(self::OPTION_PATH_TO_DATA_PROVIDER);
        $output->writeln(__("Data-provider type: %1", $dataProviderType));
        $output->writeln(__("Path to data-provider: %1", $pathToDataProvider));

        $dataForImport = [];
        try {
            /** @var \ElogicCo\MagentoImport\Api\DataProvider\TableDataProviderInterface $dataProvider */
            $dataProvider = $this->dataProviderPool->getDataProvider($dataProviderType);
            $dataForImport = $dataProvider->getData($pathToDataProvider);
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } catch (TableNotFoundException $e) {
            $message = $this->getTableNotFoundException($dataProviderType, $pathToDataProvider, 'DataProvider');
            $output->writeln('<error>' . $message . '</error>');
            exit(1);
        }

        return $dataForImport;
    }

    /**
     * @param array $dataForImport
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws LocalizedException
     */
    public function importData(array $dataForImport, InputInterface $input, OutputInterface $output): int
    {
        $result = Cli::RETURN_SUCCESS;
        $recipientType = $input->getOption(self::OPTION_RECIPIENT);
        $pathToRecipient = $input->getOption(self::OPTION_PATH_TO_RECIPIENT);
        $pathToProvider = $input->getOption(self::OPTION_PATH_TO_DATA_PROVIDER);
        $output->writeln(__("Recipient type: %1", $recipientType));
        $output->writeln(__("Path to recipient: %1", $pathToRecipient));

        /** \ElogicCo\MagentoImport\Api\Data\DataImportInfoInterface $dataImportInfo */
        $dataImportInfo = $this->dataImportInfoF->create();
        $dataImportInfo->setPathToDataProvider($pathToProvider);
        $dataImportInfo->setPathToRecipient($pathToRecipient);
        $dataProviderType = $input->getOption(self::OPTION_DATA_PROVIDER);
        $dataImportInfo->setDataProviderType($dataProviderType);
        $dataImportInfo->setRecipientType($recipientType);

        $output->writeln(__("Recipient name: %1", $dataImportInfo->getRecipientName()));
        $output->writeln(__("Data-provider name: %1", $dataImportInfo->getDataProviderName()));

        //@todo refactor
        $rememberMode = $input->getOption(self::OPTION_REMEMBER_MODE);
        $importRememberedEntities = $input->getOption(self::OPTION_IMPORT_REMEMBERED_ENTITIES);

        if ($rememberMode !== self::REMEMBER_MODE_DONT_REMEMBER_FAILED_ENTITY) {
            $recipientType .= '_remember';
        }

        /** @var \ElogicCo\MagentoImport\Api\DataImporter\DataImporterInterface $dataImporter */
        $dataImporter = $this->dataImporterPool->getDataImporter($recipientType);
        $dataImporter->setDataImportInfo($dataImportInfo);

        if ($dataImporter instanceof ImportWithProgressBarInterface) {
            $this->progressBarWrapper->setOutput($output);
            $dataImporter->setProgressBarWrapper($this->progressBarWrapper);
        }
        if ($dataImporter instanceof ImporterRememberInterface) {
            //@todo refactor to use different remember processor from import through remembered entities
            $rememberProcessor = $this->rememberProcessorPool->getRememberProcessor($rememberMode);
            $dataImporter->setRememberProcessor($rememberProcessor);
            //@todo refactor to use constants and classes to run import through remembered entities
            if ($importRememberedEntities) {
                if ($rememberMode === 'remember-with-replace') {
                    $dataForImport = $rememberProcessor->getRememberedStateDataForImport($dataForImport, $dataImporter->getDataImportInfo());
                } else if ($rememberMode === 'remember-all-failed-entities') {
                    $dataForImport = $rememberProcessor->getRememberedStateDataForImport($dataForImport, $dataImporter->getDataImportInfo());
                }
                if (empty($dataForImport)) {
                    $output->writeln('<bg=yellow>' . __("There are not remembered entities for this provider+recipient+remember_mode combination") . '</>');
                    return $result;
                }
            }
        }
        try {
            $dataImporter->importData($dataForImport);
        } catch (TableNotFoundException $e) {
            $message = $this->getTableNotFoundException($recipientType, $pathToRecipient, 'Recipient');
            $output->writeln('<error>' . $message . '</error>');
            $result = Cli::RETURN_FAILURE;
        }  catch (\Exception $e) {
            $output->writeln('<error>' . 'There was error processed during the import progress' . '</error>');
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $output->writeln('<error>' . $e->getTraceAsString() . '</error>');
            $result = Cli::RETURN_FAILURE;
        }

        //@todo refactor
        if ($dataImporter instanceof ImporterRememberInterface) {
            $rememberProcessor = $dataImporter->getRememberProcessor();
            $dataImportInfo = $dataImporter->getDataImportInfo();
            $rememberedEntities = $rememberProcessor->getRememberedEntitiesByScope($dataImportInfo);

            $output->writeln('<bg=yellow>'
                             . __("Count of entities that were remembered cause there were exceptions during importing: ")
                             . '</>'
                             . '<bg=yellow;options=bold,underscore>'
                             . $rememberedEntities->getTotalCount()
                             . '</>');
        }

        return $result;
    }

    /**
     * You can set up you plugin here ;)
     * @see \ElogicCo\MagentoImport\Console\DefaultImport::interact()
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $enteredMapping
     * @return array
     */
    public function interactMappingOption(InputInterface $input, OutputInterface $output, array $enteredMapping): array
    {
        $dataProviderType = $input->getOption(self::OPTION_DATA_PROVIDER);
        $pathToDataProvider = $input->getOption(self::OPTION_PATH_TO_DATA_PROVIDER);
        /** @var \ElogicCo\MagentoImport\Api\DataProvider\TableDataProviderInterface $dataProvider */
        $dataProvider = $this->dataProviderPool->getDataProvider($dataProviderType);
        $dataProviderColumns = $dataProvider->getColumnNames($pathToDataProvider);
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
     * @param string $type
     * @param string $path
     * @param string $logicType
     * @return string
     */
    public function getTableNotFoundException(string $type, string $path, string $logicType): string
    {
        $message = __('Cannot find table by path to ' . $logicType . ': %1', $path);
        $secondMessage = __('Check if you rightly set up ' . $logicType . ' Type. Your ' . $logicType . ' Type is "%1"',
                            '<fg=cyan>' . $type . '</>');
        $infoMessage = __('Use option %1 to set up a ' . $logicType . ' Type. Run %2 for more info',
                          '<fg=cyan>--' . self::OPTION_RECIPIENT . '</>',
                          '<fg=cyan>' . $this->getName() . ' --help</>');
        return $message . PHP_EOL . $secondMessage . PHP_EOL . $infoMessage;
    }

    /**
     * @return array
     * @throws LocalizedException
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
            //@todo refactor description to pass only constants
            new InputOption(
                self::OPTION_IMPORT_REMEMBERED_ENTITIES,
                null,
                InputOption::VALUE_NONE,
                'Start import from remembered entity if there is ' . self::OPTION_REMEMBER_MODE
                . ' with value `remember-with-replace`. Run import through all remembered entities if there is option '
                . self::OPTION_REMEMBER_MODE . ' with value `remember-all-failed-entities`',
            ),
            $this->getInstanceOfRememberModeOption(),

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

    /**
     * @return InputOption
     * @throws LocalizedException
     */
    public function getInstanceOfRememberModeOption(): InputOption
    {
        $processorModes = $this->rememberProcessorPool->getProcessModes();
//@todo Test throwing exception
//        try {
        $defaultProcessorMode = $this->rememberProcessorPool->getDefaultProcessMode();
//        } catch (LocalizedException|\InvalidArgumentException $exception) {
//
//        }
        //@todo Write further description of the options
        return new InputOption(
            self::OPTION_REMEMBER_MODE,
            null,
            InputOption::VALUE_OPTIONAL,
            'Exist remember modes: '
            . implode(', ', $processorModes)
            . ', ' . self::REMEMBER_MODE_DONT_REMEMBER_FAILED_ENTITY,
            $defaultProcessorMode);
    }
}
