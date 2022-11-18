<?php

namespace Elogic\MagentoImport\Api\Extensions;

use Elogic\MagentoImport\Model\Extensions\ProgressBarWrapper;

interface ImportWithProgressBarInterface
{
    public function runImportWithProgressBar(array $dataToInsert);

    public function setProgressBarWrapper(ProgressBarWrapper $progressBarWrapper);

    public function getProgressBarWrapper(): ProgressBarWrapper;
}
