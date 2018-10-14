<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Eav;

use Magento\Catalog\Model\Attribute\LockValidatorInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Catalog attribute model
 *
 * @api
 * @method \Magento\Catalog\Model\ResourceModel\Eav\Attribute getFrontendInputRenderer()
 * @method string setFrontendInputRenderer(string $value)
 * @method int setIsGlobal(int $value)
 * @method int getSearchWeight()
 * @method int setSearchWeight(int $value)
 * @method bool getIsUsedForPriceRules()
 * @method int setIsUsedForPriceRules(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @since 100.0.2
 */
class Attribute extends \Magento\Eav\Model\Entity\Attribute implements
    \Magento\Catalog\Api\Data\ProductAttributeInterface,
    \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface
{
    const MODULE_NAME = 'Magento_Catalog';

    const ENTITY = 'catalog_eav_attribute';

    const KEY_IS_GLOBAL = 'is_global';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'attribute';

    /**
     * Array with labels
     *
     * @var array
     */
    protected static $_labels = null;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'catalog_entity_attribute';

    /**
     * @var SaveProcessor
     */
    private $saveProcessor;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Eav\Processor $indexerEavProcessor
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productFlatIndexerHelper
     * @param LockValidatorInterface $lockValidator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param \Magento\Eav\Api\Data\AttributeExtensionFactory|null $eavAttributeFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        DateTimeFormatterInterface $dateTimeFormatter,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor = null,
        \Magento\Catalog\Model\Indexer\Product\Eav\Processor $indexerEavProcessor = null,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productFlatIndexerHelper = null,
        LockValidatorInterface $lockValidator = null,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Eav\Api\Data\AttributeExtensionFactory $eavAttributeFactory = null,
        SaveProcessor $saveProcessor = null
    ) {
        $this->saveProcessor = $saveProcessor ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(SaveProcessor::class);
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $eavConfig,
            $eavTypeFactory,
            $storeManager,
            $resourceHelper,
            $universalFactory,
            $optionDataFactory,
            $dataObjectProcessor,
            $dataObjectHelper,
            $localeDate,
            $reservedAttributeList,
            $localeResolver,
            $dateTimeFormatter,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Catalog\Model\ResourceModel\Attribute::class);
    }

    /**
     * Processing object before save data
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeSave()
    {
        $this->saveProcessor->beforeSave($this, $this->_data, $this->_origData);
        return parent::beforeSave();
    }

    /**
     * Processing object after save data
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws LocalizedException
     */
    public function afterSave()
    {
        $this->saveProcessor->afterSave($this);
        return parent::afterSave();
    }

    /**
     * Is attribute enabled for flat indexing
     *
     * @return bool
     */
    public function isEnabledInFlat()
    {
        return $this->_isEnabledInFlat();
    }

    /**
     * Is attribute enabled for flat indexing
     *
     * @return bool
     */
    protected function _isEnabledInFlat()
    {
        return $this->saveProcessor->isEnabledInFlat($this, $this->_getData('backend_type'));
    }

    /**
     * Is original attribute enabled for flat indexing
     *
     * @return bool
     */
    protected function _isOriginalEnabledInFlat()
    {
        return $this->saveProcessor->isOriginalEnabledInFlat($this);
    }

    /**
     * Register indexing event before delete catalog eav attribute
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        $this->saveProcessor->beforeDelete($this);
        return parent::beforeDelete();
    }

    /**
     * Init indexing process after catalog eav attribute delete commit
     *
     * @return $this
     */
    public function afterDeleteCommit()
    {
        parent::afterDeleteCommit();
        $this->saveProcessor->afterDeleteCommit($this);
        return $this;
    }

    /**
     * Return is attribute global
     *
     * @return integer
     */
    public function getIsGlobal()
    {
        return $this->_getData(self::KEY_IS_GLOBAL);
    }

    /**
     * Retrieve attribute is global scope flag
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getIsGlobal() == self::SCOPE_GLOBAL;
    }

    /**
     * Retrieve attribute is website scope website
     *
     * @return bool
     */
    public function isScopeWebsite()
    {
        return $this->getIsGlobal() == self::SCOPE_WEBSITE;
    }

    /**
     * Retrieve attribute is store scope flag
     *
     * @return bool
     */
    public function isScopeStore()
    {
        return !$this->isScopeGlobal() && !$this->isScopeWebsite();
    }

    /**
     * Retrieve store id
     *
     * @return int
     */
    public function getStoreId()
    {
        $dataObject = $this->getDataObject();
        if ($dataObject) {
            return $dataObject->getStoreId();
        }
        return $this->_getData('store_id');
    }

    /**
     * Retrieve apply to products array
     *
     * Return empty array if applied to all products
     *
     * @return string[]
     */
    public function getApplyTo()
    {
        $applyTo = $this->_getData(self::APPLY_TO) ?: [];
        if (!is_array($applyTo)) {
            $applyTo = explode(',', $applyTo);
        }
        return $applyTo;
    }

    /**
     * Retrieve source model
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
     */
    public function getSourceModel()
    {
        $model = $this->_getData('source_model');
        if (empty($model)) {
            if ($this->getBackendType() == 'int' && $this->getFrontendInput() == 'select') {
                return $this->_getDefaultSourceModel();
            }
        }
        return $model;
    }

    /**
     * Whether allowed for rule condition
     *
     * @return bool
     */
    public function isAllowedForRuleCondition()
    {
        $allowedInputTypes = [
            'boolean',
            'date',
            'datetime',
            'multiselect',
            'price',
            'select',
            'text',
            'textarea',
            'weight',
        ];
        return $this->getIsVisible() && in_array($this->getFrontendInput(), $allowedInputTypes);
    }

    /**
     * Get default attribute source model
     *
     * @return string
     */
    public function _getDefaultSourceModel()
    {
        return \Magento\Eav\Model\Entity\Attribute\Source\Table::class;
    }

    /**
     * Check is an attribute used in EAV index
     *
     * @return bool
     */
    public function isIndexable()
    {
        return $this->saveProcessor->isIndexable($this);
    }

    /**
     * Is original attribute config indexable
     *
     * @return bool
     */
    protected function _isOriginalIndexable()
    {
        return $this->saveProcessor->isOriginalIndexable($this);
    }

    /**
     * Retrieve index type for indexable attribute
     *
     * @return string|false
     */
    public function getIndexType()
    {
        return $this->saveProcessor->getIndexType($this);
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnoreStart
     */
    public function getIsWysiwygEnabled()
    {
        return $this->_getData(self::IS_WYSIWYG_ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function getIsHtmlAllowedOnFront()
    {
        return $this->_getData(self::IS_HTML_ALLOWED_ON_FRONT);
    }

    /**
     * @inheritdoc
     */
    public function getUsedForSortBy()
    {
        return $this->_getData(self::USED_FOR_SORT_BY);
    }

    /**
     * @inheritdoc
     */
    public function getIsFilterable()
    {
        return $this->_getData(self::IS_FILTERABLE);
    }

    /**
     * @inheritdoc
     */
    public function getIsFilterableInSearch()
    {
        return $this->_getData(self::IS_FILTERABLE_IN_SEARCH);
    }

    /**
     * @inheritdoc
     */
    public function getIsUsedInGrid()
    {
        return (bool)$this->_getData(self::IS_USED_IN_GRID);
    }

    /**
     * @inheritdoc
     */
    public function getIsVisibleInGrid()
    {
        return (bool)$this->_getData(self::IS_VISIBLE_IN_GRID);
    }

    /**
     * @inheritdoc
     */
    public function getIsFilterableInGrid()
    {
        return (bool)$this->_getData(self::IS_FILTERABLE_IN_GRID);
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->_getData(self::POSITION);
    }

    /**
     * @inheritdoc
     */
    public function getIsSearchable()
    {
        return $this->_getData(self::IS_SEARCHABLE);
    }

    /**
     * @inheritdoc
     */
    public function getIsVisibleInAdvancedSearch()
    {
        return $this->_getData(self::IS_VISIBLE_IN_ADVANCED_SEARCH);
    }

    /**
     * @inheritdoc
     */
    public function getIsComparable()
    {
        return $this->_getData(self::IS_COMPARABLE);
    }

    /**
     * @inheritdoc
     */
    public function getIsUsedForPromoRules()
    {
        return $this->_getData(self::IS_USED_FOR_PROMO_RULES);
    }

    /**
     * @inheritdoc
     */
    public function getIsVisibleOnFront()
    {
        return $this->_getData(self::IS_VISIBLE_ON_FRONT);
    }

    /**
     * @inheritdoc
     */
    public function getUsedInProductListing()
    {
        return $this->_getData(self::USED_IN_PRODUCT_LISTING);
    }

    /**
     * @inheritdoc
     */
    public function getIsVisible()
    {
        return $this->_getData(self::IS_VISIBLE);
    }

    //@codeCoverageIgnoreEnd

    /**
     * @inheritdoc
     */
    public function getScope()
    {
        if ($this->isScopeGlobal()) {
            return self::SCOPE_GLOBAL_TEXT;
        } elseif ($this->isScopeWebsite()) {
            return self::SCOPE_WEBSITE_TEXT;
        } else {
            return self::SCOPE_STORE_TEXT;
        }
    }

    /**
     * Set whether WYSIWYG is enabled flag
     *
     * @param bool $isWysiwygEnabled
     * @return $this
     */
    public function setIsWysiwygEnabled($isWysiwygEnabled)
    {
        return $this->setData(self::IS_WYSIWYG_ENABLED, $isWysiwygEnabled);
    }

    /**
     * Set whether the HTML tags are allowed on the frontend
     *
     * @param bool $isHtmlAllowedOnFront
     * @return $this
     */
    public function setIsHtmlAllowedOnFront($isHtmlAllowedOnFront)
    {
        return $this->setData(self::IS_HTML_ALLOWED_ON_FRONT, $isHtmlAllowedOnFront);
    }

    /**
     * Set whether it is used for sorting in product listing
     *
     * @param bool $usedForSortBy
     * @return $this
     */
    public function setUsedForSortBy($usedForSortBy)
    {
        return $this->setData(self::USED_FOR_SORT_BY, $usedForSortBy);
    }

    /**
     * Set whether it used in layered navigation
     *
     * @param bool $isFilterable
     * @return $this
     */
    public function setIsFilterable($isFilterable)
    {
        return $this->setData(self::IS_FILTERABLE, $isFilterable);
    }

    /**
     * Set whether it is used in search results layered navigation
     *
     * @param bool $isFilterableInSearch
     * @return $this
     */
    public function setIsFilterableInSearch($isFilterableInSearch)
    {
        return $this->setData(self::IS_FILTERABLE_IN_SEARCH, $isFilterableInSearch);
    }

    /**
     * Set position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * Set apply to value for the element
     *
     * @param string[]|string $applyTo
     * @return $this
     */
    public function setApplyTo($applyTo)
    {
        if (is_array($applyTo)) {
            $applyTo = implode(',', $applyTo);
        }
        return $this->setData(self::APPLY_TO, $applyTo);
    }

    /**
     * Whether the attribute can be used in Quick Search
     *
     * @param string $isSearchable
     * @return $this
     */
    public function setIsSearchable($isSearchable)
    {
        return $this->setData(self::IS_SEARCHABLE, $isSearchable);
    }

    /**
     * Set whether the attribute can be used in Advanced Search
     *
     * @param string $isVisibleInAdvancedSearch
     * @return $this
     */
    public function setIsVisibleInAdvancedSearch($isVisibleInAdvancedSearch)
    {
        return $this->setData(self::IS_VISIBLE_IN_ADVANCED_SEARCH, $isVisibleInAdvancedSearch);
    }

    /**
     * Set whether the attribute can be compared on the frontend
     *
     * @param string $isComparable
     * @return $this
     */
    public function setIsComparable($isComparable)
    {
        return $this->setData(self::IS_COMPARABLE, $isComparable);
    }

    /**
     * Set whether the attribute can be used for promo rules
     *
     * @param string $isUsedForPromoRules
     * @return $this
     */
    public function setIsUsedForPromoRules($isUsedForPromoRules)
    {
        return $this->setData(self::IS_USED_FOR_PROMO_RULES, $isUsedForPromoRules);
    }

    /**
     * Set whether the attribute is visible on the frontend
     *
     * @param string $isVisibleOnFront
     * @return $this
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        return $this->setData(self::IS_VISIBLE_ON_FRONT, $isVisibleOnFront);
    }

    /**
     * Set whether the attribute can be used in product listing
     *
     * @param string $usedInProductListing
     * @return $this
     */
    public function setUsedInProductListing($usedInProductListing)
    {
        return $this->setData(self::USED_IN_PRODUCT_LISTING, $usedInProductListing);
    }

    /**
     * Set whether attribute is visible on frontend.
     *
     * @param bool $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible)
    {
        return $this->setData(self::IS_VISIBLE, $isVisible);
    }

    /**
     * Set attribute scope
     *
     * @param string $scope
     * @return $this
     */
    public function setScope($scope)
    {
        if ($scope == self::SCOPE_GLOBAL_TEXT) {
            return $this->setData(self::KEY_IS_GLOBAL, self::SCOPE_GLOBAL);
        } elseif ($scope == self::SCOPE_WEBSITE_TEXT) {
            return $this->setData(self::KEY_IS_GLOBAL, self::SCOPE_WEBSITE);
        } elseif ($scope == self::SCOPE_STORE_TEXT) {
            return $this->setData(self::KEY_IS_GLOBAL, self::SCOPE_STORE);
        } else {
            //Ignore unrecognized scope
            return $this;
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        $this->saveProcessor->afterDelete();
        return parent::afterDelete();
    }

    /**
     * @inheritdoc
     * @since 100.0.9
     */
    public function __sleep()
    {
        $this->unsetData('entity_type');
        return array_diff(
            parent::__sleep(),
            [
                'saveProcessor'
            ]
        );
    }

    /**
     * @inheritdoc
     * @since 100.0.9
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->saveProcessor = $objectManager->get(SaveProcessor::class);
    }

    /**
     * @inheritdoc
     * @since 101.1.0
     */
    public function setIsUsedInGrid($isUsedInGrid)
    {
        $this->setData(self::IS_USED_IN_GRID, $isUsedInGrid);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 101.1.0
     */
    public function setIsVisibleInGrid($isVisibleInGrid)
    {
        $this->setData(self::IS_VISIBLE_IN_GRID, $isVisibleInGrid);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 101.1.0
     */
    public function setIsFilterableInGrid($isFilterableInGrid)
    {
        $this->setData(self::IS_FILTERABLE_IN_GRID, $isFilterableInGrid);
        return $this;
    }
}
