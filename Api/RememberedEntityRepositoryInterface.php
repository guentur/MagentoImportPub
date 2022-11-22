<?php

namespace ElogicCo\MagentoImport\Api;

use ElogicCo\MagentoImport\Api\Data\RememberedEntityInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use ElogicCo\MagentoImport\Api\Data\RememberedEntitySearchResultInterface;

/**
 * @api
 */
interface RememberedEntityRepositoryInterface
{
    /**
     * @param RememberedEntityInterface $rememberedEntity
     * @return mixed
     */
    public function save(RememberedEntityInterface $rememberedEntity);

    /**
     * @param $rememberedEntityId
     * @return mixed
     */
    public function getById($rememberedEntityId);

    /**
     * @param RememberedEntityInterface $rememberedEntity
     * @return mixed
     */
    public function delete(RememberedEntityInterface $rememberedEntity);

    /**
     * @param $rememberedEntityId
     * @return mixed
     */
    public function deleteById($rememberedEntityId);

    /**
     * @param string $rememberedEntityScope
     * @return mixed
     */
    public function deleteByScope(string $rememberedEntityScope);

    /**
     * Get rememberedEntity list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return RememberedEntitySearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): RememberedEntitySearchResultInterface;
}
