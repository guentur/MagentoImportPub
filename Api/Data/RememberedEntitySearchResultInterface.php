<?php

namespace ElogicCo\MagentoImport\Api\Data;

/**
 * @api
 */
interface RememberedEntitySearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \ElogicCo\MagentoImport\Api\Data\RememberedEntityInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \ElogicCo\MagentoImport\Api\Data\RememberedEntityInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
