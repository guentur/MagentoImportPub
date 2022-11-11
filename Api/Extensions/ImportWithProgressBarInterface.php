<?php

namespace Guentur\MagentoImport\Api\Extensions;

use Guentur\MagentoImport\Model\Extensions\ProgressBarWrapper;

interface ImportWithProgressBarInterface
{
//    public function runImportWithProgressBar(array $dataForImport);

    public function setProgressBarWrapper(ProgressBarWrapper $progressBarWrapper);

    public function getProgressBarWrapper(): ProgressBarWrapper;
}
