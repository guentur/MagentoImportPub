<?php

declare(strict_types=1);

namespace Guentur\MagentoImport\Api\Data;

interface RememberedEntityInterface
{
    public function getId();

    public function setPathToRecipient(string $pathToRecipient): void;

    public function setPathToDataProvider(string $path): void;

    public function getPathToDataProvider();

    public function getPathToRecipient(): string;

    public function getDataProviderName();

    public function getRecipientName();

    public function setScope(string $scope);

    public function getScope();
}
