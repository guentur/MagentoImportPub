<?php

namespace ElogicCo\MagentoImport\Api\Extensions;

use ElogicCo\MagentoImport\Model\Extensions\ProgressBarWrapper;

interface ImportWithProgressBarInterface
{
    public function runImportWithProgressBar(array $dataToInsert);

    public function setProgressBarWrapper(ProgressBarWrapper $progressBarWrapper);

    public function getProgressBarWrapper(): ProgressBarWrapper;
}
