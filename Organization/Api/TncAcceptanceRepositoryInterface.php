<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aha\Organization\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface TncAcceptanceRepositoryInterface
{

    /**
     * Save TncAcceptance
     * @param \Aha\Organization\Api\Data\TncAcceptanceInterface $tncAcceptance
     * @return \Aha\Organization\Api\Data\TncAcceptanceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Aha\Organization\Api\Data\TncAcceptanceInterface $tncAcceptance
    );

    /**
     * Retrieve TncAcceptance
     * @param string $tncacceptanceId
     * @return \Aha\Organization\Api\Data\TncAcceptanceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($tncacceptanceId);

    /**
     * Retrieve TncAcceptance matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aha\Organization\Api\Data\TncAcceptanceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete TncAcceptance
     * @param \Aha\Organization\Api\Data\TncAcceptanceInterface $tncAcceptance
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Aha\Organization\Api\Data\TncAcceptanceInterface $tncAcceptance
    );

    /**
     * Delete TncAcceptance by ID
     * @param string $tncacceptanceId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($tncacceptanceId);
}

