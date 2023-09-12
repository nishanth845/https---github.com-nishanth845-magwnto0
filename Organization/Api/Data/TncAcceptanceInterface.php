<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aha\Organization\Api\Data;

interface TncAcceptanceInterface
{

    const CUSTOMER_ID = 'customer_id';
    const STORE_ID = 'store_id';
    const ORG_ID = 'org_id';
    const UPDATED_AT = 'updated_at';
    const GROUP_ID = 'group_id';
    const WEBSITE_ID = 'website_id';
    const CREATED_AT = 'created_at';
    const ENTITY_ID = 'entity_id';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Aha\Organization\TncAcceptance\Api\Data\TncAcceptanceInterface
     */
    public function setEntityId($entityId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Aha\Organization\TncAcceptance\Api\Data\TncAcceptanceInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get org_id
     * @return string|null
     */
    public function getOrgId();

    /**
     * Set org_id
     * @param string $orgId
     * @return \Aha\Organization\TncAcceptance\Api\Data\TncAcceptanceInterface
     */
    public function setOrgId($orgId);

    /**
     * Get website_id
     * @return string|null
     */
    public function getWebsiteId();

    /**
     * Set website_id
     * @param string $websiteId
     * @return \Aha\Organization\TncAcceptance\Api\Data\TncAcceptanceInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * Get group_id
     * @return string|null
     */
    public function getGroupId();

    /**
     * Set group_id
     * @param string $groupId
     * @return \Aha\Organization\TncAcceptance\Api\Data\TncAcceptanceInterface
     */
    public function setGroupId($groupId);

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param string $storeId
     * @return \Aha\Organization\TncAcceptance\Api\Data\TncAcceptanceInterface
     */
    public function setStoreId($storeId);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Aha\Organization\TncAcceptance\Api\Data\TncAcceptanceInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Aha\Organization\TncAcceptance\Api\Data\TncAcceptanceInterface
     */
    public function setUpdatedAt($updatedAt);
}

