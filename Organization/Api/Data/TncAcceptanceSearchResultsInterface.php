<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aha\Organization\Api\Data;

interface TncAcceptanceSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get TncAcceptance list.
     * @return \Aha\Organization\Api\Data\TncAcceptanceInterface[]
     */
    public function getItems();

    /**
     * Set entity_id list.
     * @param \Aha\Organization\Api\Data\TncAcceptanceInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

