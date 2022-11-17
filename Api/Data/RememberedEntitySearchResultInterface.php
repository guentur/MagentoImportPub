<?php

namespace Elogic\MagentoImport\Api\Data;

/**
 * @api
 */
interface RememberedEntitySearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Elogic\MagentoImport\Api\Data\RememberedEntityInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Elogic\MagentoImport\Api\Data\RememberedEntityInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
