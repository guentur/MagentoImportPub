<?php

namespace ElogicCo\MagentoImport\Model\Exception;

use Symfony\Component\Filesystem\Exception\IOException;

class InvalidFileExtensionException extends IOException
{
    public function __construct(
        string $message = null,
        int $code = 0,
        \Throwable $previous = null,
        string $path = null,
        string $extension = null,
        array $allowedExtensions = []
    ) {
        if (null === $message) {
            if (empty($extension)) {
                $message = 'File extension is not allowed.';
            } else {
                $message = sprintf('File extension "%1" is not allowed.', $extension);
            }

            if (null !== $path) {
                $message .= sprintf(' File path: "%s".', $path);
            }

            if (!empty($allowedExtensions)) {
                $message .= sprintf(' Allowed extensions: "%s".', implode(", ", $allowedExtensions));
            }
        }

        parent::__construct($message, $code, $previous, $path);
    }
}
