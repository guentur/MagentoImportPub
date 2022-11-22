<?php

declare(strict_types=1);

namespace ElogicCo\MagentoImport\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface RememberedEntityInterface extends ExtensibleDataInterface
{
    public function getId();

    public function setScope(string $scope);

    public function getScope();

    public function setRememberMode(string $rememberMode);

    public function getRememberMode();

    public function setRememberedEntityKey($rememberedEntityKey);

    public function getRememberedEntityKey();

    public function getCreatedAt();

    public function setCreatedAt($createdAt);

    public function getUpdatedAt();

    public function setUpdatedAt($updatedAt);

    /////////////////////// Additional logic

    public function setPathToRecipient(string $pathToRecipient): void;

    public function setPathToDataProvider(string $path): void;

    public function getPathToDataProvider();

    public function getPathToRecipient(): string;

    public function getDataProviderName();

    public function getRecipientName();
}
