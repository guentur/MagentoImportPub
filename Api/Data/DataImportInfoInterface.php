<?php

declare(strict_types=1);

namespace Guentur\MagentoImport\Api\Data;

interface DataImportInfoInterface
{
    public function setPathToDataProvider(string $pathToProvider);

    public function setPathToRecipient(string $pathToRecipient);

    public function getPathToDataProvider(): string;

    public function getPathToRecipient(): string;

    public function getRecipientName(): string;

    public function getDataProviderName(): string;
}
