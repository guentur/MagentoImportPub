<?php

declare(strict_types=1);

namespace ElogicCo\MagentoImport\Model\Data;

use ElogicCo\MagentoImport\Api\Data\RememberedEntitySearchResultInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with RememberedEntity search results.
 */
class RememberedEntitySearchResults extends SearchResults implements RememberedEntitySearchResultInterface
{
}
