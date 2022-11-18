<?php

declare(strict_types=1);

namespace Elogic\MagentoImport\Model\Data;

use Elogic\MagentoImport\Api\Data\RememberedEntitySearchResultInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with RememberedEntity search results.
 */
class RememberedEntitySearchResults extends SearchResults implements RememberedEntitySearchResultInterface
{
}
