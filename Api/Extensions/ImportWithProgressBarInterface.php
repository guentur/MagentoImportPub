<?php

namespace Guentur\MagentoImport\Api\Extensions;

use Guentur\MagentoImport\Model\ProgressBarWrapper;

interface ImportWithProgressBarInterface
{
    public function runImportWithProgressBar(array $dataToInsert);

    public function setProgressBarWrapper(ProgressBarWrapper $progressBarWrapper);

    public function getProgressBarWrapper(): ProgressBarWrapper;
}
