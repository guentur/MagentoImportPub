<?php

namespace Guentur\MagentoImport\Model;

use Guentur\MagentoImport\Api\Data\DataImportInfoInterface;
use Guentur\MagentoImport\Model\Mapper\DefaultMapping;

class EntityScopeManager
{
    private $defaultMapping;

    public function __construct(
        DefaultMapping $defaultMapping
    ) {
        $this->defaultMapping = $defaultMapping;
    }

    public function getEntityScope(DataImportInfoInterface $dataImportInfo): string
    {
        //@todo write in documentation that file name (not path, but exactly filename) of separate dataProviders must be different
        $providerName = $dataImportInfo->getDataProviderName();
        $recipientName = $dataImportInfo->getRecipientName();
        return $providerName . DefaultMapping::DEFAULT_SEPARATOR . $recipientName;
    }

    public function getEntityScopeEventFormat(DataImportInfoInterface $dataImportInfo): string
    {
        //@todo write in documentation that file name (not path, but exactly filename) of separate dataProviders must be different
        $providerName = $dataImportInfo->getDataProviderName();
        $recipientName = $dataImportInfo->getRecipientName();
        return $providerName . '_' . $recipientName;
    }

    /**
     * @see self::getEntityScope()
     *
     * @param string $scope
     * @return array
     */
    public function parseEntityScope(string $scope): array
    {
        // pattern of mapping and pattern of entity-scope are the same, so that I use one validator for them
        $this->defaultMapping->validateUnformattedMapping($scope);

        $importFormatEntity = [
            'path_to_provider' => explode(DefaultMapping::DEFAULT_SEPARATOR, $scope)[0],
            'path_to_recipient' => explode(DefaultMapping::DEFAULT_SEPARATOR, $scope)[1],
        ];

        return $importFormatEntity;
    }
}
