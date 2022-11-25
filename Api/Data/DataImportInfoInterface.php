<?php

declare(strict_types=1);

namespace ElogicCo\MagentoImport\Api\Data;

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
    public function getPathToDataProvider();

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
    public function getRecipientName();

    /**
     * @return string
     */
    public function getDataProviderName();

    // Type

    /**
     * @param string $dataProviderType
     * @return void
     */
    public function setDataProviderType(string $dataProviderType): void;

    /**
     * @return string
     */
    public function getDataProviderType();

    /**
     * @param string $recipientType
     * @return void
     */
    public function setRecipientType(string $recipientType): void;

    /**
     * @return string
     */
    public function getRecipientType();
}
