<?php

declare(strict_types=1);

namespace Guentur\MagentoImport\Api\Data;

interface DataImportInfoInterface
{
    // Path
    /**
     * @param string $pathToProvider
     * @return void
     */
    public function setPathToDataProvider(string $pathToProvider): void;

    /**
     * @return string
     */
    public function getPathToDataProvider(): string;

    /**
     * @param string $pathToRecipient
     * @return void
     */
    public function setPathToRecipient(string $pathToRecipient): void;

    /**
     * @return string
     */
    public function getPathToRecipient(): string;

    // Name

    /**
     * @return string
     */
    public function getRecipientName(): string;

    /**
     * @return string
     */
    public function getDataProviderName(): string;

    // Type

    /**
     * @param string $dataProviderType
     * @return void
     */
    public function setDataProviderType(string $dataProviderType): void;

    /**
     * @return string
     */
    public function getDataProviderType(): string;

    /**
     * @param string $recipientType
     * @return void
     */
    public function setRecipientType(string $recipientType): void;

    /**
     * @return string
     */
    public function getRecipientType(): string;
}
