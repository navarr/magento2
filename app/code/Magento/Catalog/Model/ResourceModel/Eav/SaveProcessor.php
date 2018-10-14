<?php

namespace Magento\Catalog\Model\ResourceModel\Eav;

use Magento\Catalog\Helper\Product\Flat\Indexer;
use Magento\Catalog\Model\Attribute\LockValidatorInterface;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor as EavProcessor;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor as FlatProcessor;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as ProductAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\Category\Attribute as CategoryAttribute;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Exception\LocalizedException;

/**
 * Performs before and after save functionality for Product and Category attributes
 */
class SaveProcessor
{
    const MODULE_NAME = 'Magento_Catalog';

    const KEY_IS_GLOBAL = 'is_global';

    /** @var EavProcessor */
    private $eavIndexerProcessor;

    /** @var Indexer */
    private $indexerHelper;

    /** @var Processor */
    private $flatIndexerProcessor;

    /** @var LockValidatorInterface */
    private $lockValidator;

    /**
     * @param LockValidatorInterface $lockValidator
     * @param Indexer $indexerHelper
     * @param FlatProcessor $flatIndexerProcessor
     * @param EavProcessor $eavIndexerProcessor
     */
    public function __construct(
        LockValidatorInterface $lockValidator,
        Indexer $indexerHelper,
        FlatProcessor $flatIndexerProcessor,
        EavProcessor $eavIndexerProcessor
    ) {
        $this->lockValidator = $lockValidator;
        $this->indexerHelper = $indexerHelper;
        $this->flatIndexerProcessor = $flatIndexerProcessor;
        $this->eavIndexerProcessor = $eavIndexerProcessor;
    }

    /**
     * Perform setting modifications before the attribute is saved
     *
     * Here we do things like ensuring a scope change is possible, correcting backend models,
     * determining whether or not HTML is allowed on the frontend, and ensuring that if the
     * attribute is not searchable it cannot be in advanced search.
     *
     * @param ProductAttribute|CategoryAttribute $attribute
     * @param array $data
     * @param array $origData
     * @return void
     * @throws LocalizedException
     */
    public function beforeSave(Attribute $attribute, &$data, $origData)
    {
        $attribute->setData('modulePrefix', self::MODULE_NAME);

        $this->validateScopeChange($attribute, $data, $origData);

        if ($attribute->getFrontendInput() === 'price' && !$attribute->getBackendModel()) {
            $attribute->setBackendModel(Price::class);
        }
        if ($attribute->getFrontendInput() === 'textarea' && $attribute->getIsWysiwygEnabled()) {
            $attribute->setIsHtmlAllowedOnFront(1);
        }
        if (!$attribute->getIsSearchable()) {
            $attribute->setIsVisibleInAdvancedSearch(false);
        }
    }

    /**
     * Determine whether or not the scope of an attribute may be changed
     *
     * @param ProductAttribute|CategoryAttribute $attribute
     * @param array &$data
     * @param array $origData
     * @return void
     * @throws LocalizedException
     */
    private function validateScopeChange(Attribute $attribute, &$data, $origData)
    {
        if (isset($origData[self::KEY_IS_GLOBAL])) {
            if (!isset($data[self::KEY_IS_GLOBAL])) {
                $data[self::KEY_IS_GLOBAL] = Attribute\ScopedAttributeInterface::SCOPE_GLOBAL;
            }
            if ($data[self::KEY_IS_GLOBAL] != $origData[self::KEY_IS_GLOBAL]) {
                try {
                    $this->lockValidator->validate($attribute);
                } catch (LocalizedException $exception) {
                    throw new LocalizedException(__('Do not change the scope. %1', $exception->getMessage()));
                }
            }
        }
    }

    /**
     * Update indexers once an attribute has been saved
     *
     * @param ProductAttribute|CategoryAttribute $attribute
     * @return void
     * @throws LocalizedException
     */
    public function afterSave(Attribute $attribute)
    {
        /**
         * Fix saving attribute in admin
         */
        $this->_eavConfig->clear();

        if ($this->isOriginalEnabledInFlat($attribute) != $attribute->isEnabledInFlat()) {
            $this->flatIndexerProcessor->markIndexerAsInvalid();
        }
        $indexable = $attribute->isIndexable();
        if ($attribute->_isOriginalIndexable() !== $indexable
            || ($indexable && $attribute->dataHasChangedFor(self::KEY_IS_GLOBAL))
        ) {
            $this->eavIndexerProcessor->markIndexerAsInvalid();
        }
    }

    public function beforeDelete(Attribute $attribute)
    {
        $this->lockValidator->validate($attribute);
    }

    public function afterDelete()
    {
        $this->eavConfig->clear();
    }

    public function afterDeleteCommit(Attribute $attribute)
    {
        if ($this->isOriginalEnabledInFlat($attribute)) {
            $this->flatIndexerProcessor->markIndexerAsInvalid();
        }
        if ($this->isOriginalIndexable($attribute)) {
            $this->eavIndexerProcessor->markIndexerAsInvalid();
        }
    }

    /**
     * Retrieve whether or not the original attribute config was enabled in flat table
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function isOriginalEnabledInFlat(Attribute $attribute)
    {
        return $attribute->getOrigData('backend_type') === 'static'
            || ($this->indexerHelper->isAddFilterableAttributes() && $attribute->getOrigData('is_filterable') > 0)
            || $attribute->getOrigData('used_in_product_listing') == 1
            || $attribute->getOrigData('used_for_sort_by') == 1;
    }

    /**
     * Retrieve whether or not the attribute is enabled for flat indexing
     *
     * @param ProductAttribute|CategoryAttribute
     * @return bool
     */
    public function isEnabledInFlat(Attribute $attribute, $backendType)
    {
        return $backendType === 'static'
            || ($this->indexerHelper->isAddFilterableAttributes() && $attribute->getIsFilterable() > 0)
            || $attribute->getUsedInProductListing()
            || $attribute->getUsedForSortBy();
    }

    /**
     * Retrieve whether or not the attribute is indexable
     *
     * @param ProductAttribute|CategoryAttribute $attribute
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isIndexable(Attribute $attribute)
    {
        $backendType = $attribute->getBackendType();
        $frontendInput = $attribute->getFrontendInput();

        $isPrice = $attribute->getAttributeCode() === 'price';
        $isFilterableInSearch = $attribute->getIsFilterableInSearch();
        $isVisibleInAdvSearch = $attribute->getIsVisibleInAdvancedSearch();
        $isFilterable = $attribute->getIsFilterable();

        $isSelectionInt = $backendType === 'int' && ($frontendInput === 'select' || $frontendInput === 'boolean');
        $isMultiselect = $backendType === 'varchar' && $frontendInput === 'multiselect';
        $isDecimal = $backendType === 'decimal';

        return !$isPrice // exclude price attribute
            && !$isFilterableInSearch
            && !$isVisibleInAdvSearch
            && !$isFilterable
            && ($isSelectionInt || $isMultiselect || $isDecimal);
    }

    /**
     * Retrieve whether or not the original attribute config was indexable
     *
     * @param Attribute $attribute
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isOriginalIndexable(Attribute $attribute)
    {
        $backendType = $attribute->getOrigData('backend_type');
        $frontendInput = $attribute->getOrigData('frontend_input');

        $isPrice = $attribute->getOrigData('attribute_code') === 'price';
        $isFilterableInSearch = $attribute->getOrigData('is_filterable_in_search');
        $isVisibleInAdvSearch = $attribute->getOrigData('is_visible_in_advanced_search');
        $isFilterable = $attribute->getOrigData('is_filterable');

        $isSelectionInt = $backendType === 'int' && ($frontendInput === 'select' || $frontendInput === 'boolean');
        $isMultiselect = $backendType === 'varchar' && $frontendInput === 'multiselect';
        $isDecimal = $backendType === 'decimal';

        return !$isPrice // exclude price attribute
            && !$isFilterableInSearch
            && !$isVisibleInAdvSearch
            && !$isFilterable
            && ($isSelectionInt || $isMultiselect || $isDecimal);
    }

    /**
     * Retrieve the index type for an indexable attribute
     *
     * @param ProductAttribute|CategoryAttribute $attribute
     * @return bool|string
     */
    public function getIndexType(Attribute $attribute)
    {
        if (!$attribute->isIndexable()) {
            return false;
        }

        return $attribute->getBackendType() === 'decimal' ? 'decimal' : 'source';
    }
}
