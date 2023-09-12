<?php
declare(strict_types=1);

namespace Aha\Organization\Model;

use Aha\Organization\Api\Data\TncAcceptanceInterface;
use Aha\Organization\Api\Data\TncAcceptanceInterfaceFactory;
use Aha\Organization\Api\Data\TncAcceptanceSearchResultsInterfaceFactory;
use Aha\Organization\Api\TncAcceptanceRepositoryInterface;
use Aha\Organization\Model\ResourceModel\TncAcceptance as ResourceTncAcceptance;
use Aha\Organization\Model\ResourceModel\TncAcceptance\CollectionFactory as TncAcceptanceCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class TncAcceptanceRepository implements TncAcceptanceRepositoryInterface
{

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var TncAcceptanceCollectionFactory
     */
    protected $tncAcceptanceCollectionFactory;

    /**
     * @var TncAcceptance
     */
    protected $searchResultsFactory;

    /**
     * @var TncAcceptanceInterfaceFactory
     */
    protected $tncAcceptanceFactory;

    /**
     * @var ResourceTncAcceptance
     */
    protected $resource;


    /**
     * @param ResourceTncAcceptance $resource
     * @param TncAcceptanceInterfaceFactory $tncAcceptanceFactory
     * @param TncAcceptanceCollectionFactory $tncAcceptanceCollectionFactory
     * @param TncAcceptanceSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceTncAcceptance $resource,
        TncAcceptanceInterfaceFactory $tncAcceptanceFactory,
        TncAcceptanceCollectionFactory $tncAcceptanceCollectionFactory,
        TncAcceptanceSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->tncAcceptanceFactory = $tncAcceptanceFactory;
        $this->tncAcceptanceCollectionFactory = $tncAcceptanceCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(TncAcceptanceInterface $tncAcceptance)
    {
        try {
            $this->resource->save($tncAcceptance);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the tncAcceptance: %1',
                $exception->getMessage()
            ));
        }
        return $tncAcceptance;
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        $tncAcceptance = $this->tncAcceptanceFactory->create();
        $this->resource->load($tncAcceptance, $id);
        if (!$tncAcceptance->getId()) {
            throw new NoSuchEntityException(__('TncAcceptance with id "%1" does not exist.', $id));
        }
        return $tncAcceptance;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->tncAcceptanceCollectionFactory->create();
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(TncAcceptanceInterface $tncAcceptance)
    {
        try {
            $tncAcceptanceModel = $this->tncAcceptanceFactory->create();
            $this->resource->load($tncAcceptanceModel, $tncAcceptance->getTncacceptanceId());
            $this->resource->delete($tncAcceptanceModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the TncAcceptance: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($id)
    {
        return $this->delete($this->get($id));
    }
}
