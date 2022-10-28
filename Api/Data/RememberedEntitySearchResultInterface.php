<?php

namespace Guentur\MagentoImport\Api\Data;

/**
 * @api
 */
interface RememberedEntitySearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Guentur\MagentoImport\Api\Data\RememberedEntityInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Guentur\MagentoImport\Api\Data\RememberedEntityInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
