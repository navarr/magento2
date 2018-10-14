<?php

namespace Magento\Catalog\Model\ResourceModel\Eav\Category;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Eav\SaveProcessor;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Represents a Category Attribute
 */
class Attribute extends EavAttribute implements CategoryAttributeInterface, EavAttribute\ScopedAttributeInterface
{
    const KEY_IS_GLOBAL = 'is_global';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'attribute';
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'catalog_entity_attribute';
    /**
     * Array with labels
     *
     * @var array
     */
    protected static $_labels = null;

    /** @var SaveProcessor */
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
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param SaveProcessor $saveProcessor
     * @param array $data
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
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        SaveProcessor $saveProcessor,
        array $data = []
    ) {
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
        $this->saveProcessor = $saveProcessor;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Catalog\Model\ResourceModel\Attribute::class);
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
     * Get default attribute source model
     *
     * @return string
     */
    public function _getDefaultSourceModel()
    {
        return \Magento\Eav\Model\Entity\Attribute\Source\Table::class;
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
     */
    public function afterDeleteCommit()
    {
        parent::afterDeleteCommit();
        $this->saveProcessor->afterDeleteCommit($this);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function afterSave()
    {
        $this->saveProcessor->afterSave($this);
        return parent::afterSave();
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->saveProcessor->beforeDelete($this);
        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        $this->saveProcessor->beforeSave($this, $this->_data, $this->_origData);
        return parent::beforeSave();
    }

    /**
     * @inheritDoc
     */
    public function getApplyTo()
    {
        $applyTo = $this->_getData(self::APPLY_TO) ?: [];
        return is_array($applyTo) ? $applyTo : explode(',', $applyTo);
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
     * @inheritDoc
     */
    public function getIsComparable()
    {
        return $this->_getData(self::IS_COMPARABLE);
    }

    /**
     * @inheritDoc
     */
    public function getIsFilterable()
    {
        return (bool)$this->_getData(self::IS_FILTERABLE);
    }

    /**
     * @inheritDoc
     */
    public function getIsFilterableInGrid()
    {
        return (bool)$this->_getData(self::IS_FILTERABLE_IN_GRID);
    }

    /**
     * @inheritDoc
     */
    public function getIsFilterableInSearch()
    {
        return (bool)$this->_getData(self::IS_FILTERABLE_IN_SEARCH);
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
     * @inheritDoc
     */
    public function getIsHtmlAllowedOnFront()
    {
        return (bool)$this->_getData(self::IS_HTML_ALLOWED_ON_FRONT);
    }

    /**
     * @inheritDoc
     */
    public function getIsSearchable()
    {
        return $this->_getData(self::IS_SEARCHABLE);
    }

    /**
     * @inheritDoc
     */
    public function getIsUsedForPromoRules()
    {
        return $this->_getData(self::IS_USED_FOR_PROMO_RULES);
    }

    /**
     * @inheritDoc
     */
    public function getIsUsedInGrid()
    {
        return (bool)$this->_getData(self::IS_USED_IN_GRID);
    }

    /**
     * @inheritDoc
     */
    public function getIsVisible()
    {
        return $this->_getData(self::IS_VISIBLE);
    }

    /**
     * @inheritDoc
     */
    public function getIsVisibleInAdvancedSearch()
    {
        return $this->_getData(self::IS_VISIBLE_IN_ADVANCED_SEARCH);
    }

    /**
     * @inheritDoc
     */
    public function getIsVisibleInGrid()
    {
        return (bool)$this->_getData(self::IS_VISIBLE_IN_GRID);
    }

    /**
     * @inheritDoc
     */
    public function getIsWysiwygEnabled()
    {
        return (bool)$this->_getData(self::IS_WYSIWYG_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function getPosition()
    {
        return $this->_getData(self::POSITION);
    }

    /**
     * @inheritDoc
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
     * Retrieve source model
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
     */
    public function getSourceModel()
    {
        $model = $this->_getData('source_model');
        if (empty($model)) {
            if ($this->getBackendType() === 'int' && $this->getFrontendInput() === 'select') {
                return $this->_getDefaultSourceModel();
            }
        }
        return $model;
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
     * @inheritDoc
     */
    public function getUsedForSortBy()
    {
        return (bool)$this->_getData(self::USED_FOR_SORT_BY);
    }

    /**
     * @inheritDoc
     */
    public function getUsedInProductListing()
    {
        return $this->_getData(self::USED_IN_PRODUCT_LISTING);
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
     * Determine if the attribute is enabled for flat indexing
     *
     * @return bool
     */
    public function isEnabledInFlat()
    {
        return $this->saveProcessor->isEnabledInFlat($this, $this->_getData('backend_type'));
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
     * Retrieve attribute is global scope flag
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getIsGlobal() == self::SCOPE_GLOBAL;
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
     * Retrieve attribute is website scope website
     *
     * @return bool
     */
    public function isScopeWebsite()
    {
        return $this->getIsGlobal() == self::SCOPE_WEBSITE;
    }

    /**
     * @inheritDoc
     */
    public function setApplyTo($applyTo)
    {
        if (is_array($applyTo)) {
            $applyTo = implode(',', $applyTo);
        }
        return $this->setData(self::APPLY_TO, is_array($applyTo) ? implode(',', $applyTo) : $applyTo);
    }

    /**
     * @inheritDoc
     */
    public function setIsComparable($isComparable)
    {
        return $this->setData(self::IS_COMPARABLE, $isComparable);
    }

    /**
     * @inheritDoc
     */
    public function setIsFilterable($isFilterable)
    {
        return $this->setData(self::IS_FILTERABLE, $isFilterable);
    }

    /**
     * @inheritDoc
     */
    public function setIsFilterableInGrid($isFilterableInGrid)
    {
        return $this->setData(self::IS_FILTERABLE_IN_GRID, $isFilterableInGrid);
    }

    /**
     * @inheritDoc
     */
    public function setIsFilterableInSearch($isFilterableInSearch)
    {
        return $this->setData(self::IS_FILTERABLE_IN_SEARCH, $isFilterableInSearch);
    }

    /**
     * @inheritDoc
     */
    public function setIsHtmlAllowedOnFront($isHtmlAllowedOnFront)
    {
        return $this->setData(self::IS_HTML_ALLOWED_ON_FRONT, $isHtmlAllowedOnFront);
    }

    /**
     * @inheritDoc
     */
    public function setIsSearchable($isSearchable)
    {
        return $this->setData(self::IS_SEARCHABLE, $isSearchable);
    }

    /**
     * @inheritDoc
     */
    public function setIsUsedForPromoRules($isUsedForPromoRules)
    {
        return $this->setData(self::IS_USED_FOR_PROMO_RULES, $isUsedForPromoRules);
    }

    /**
     * @inheritDoc
     */
    public function setIsUsedInGrid($isUsedInGrid)
    {
        return $this->setData(self::IS_USED_IN_GRID, $isUsedInGrid);
    }

    /**
     * @inheritDoc
     */
    public function setIsVisible($isVisible)
    {
        return $this->setData(self::IS_VISIBLE, $isVisible);
    }

    /**
     * @inheritDoc
     */
    public function setIsVisibleInAdvancedSearch($isVisibleInAdvancedSearch)
    {
        return $this->setData(self::IS_VISIBLE_IN_ADVANCED_SEARCH, $isVisibleInAdvancedSearch);
    }

    /**
     * @inheritDoc
     */
    public function setIsVisibleInGrid($isVisibleInGrid)
    {
        return $this->setData(self::IS_VISIBLE_IN_GRID, $isVisibleInGrid);
    }

    /**
     * @inheritDoc
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        return $this->setData(self::IS_VISIBLE_ON_FRONT, $isVisibleOnFront);
    }

    /**
     * @inheritDoc
     */
    public function setIsWysiwygEnabled($isWysiwygEnabled)
    {
        return $this->setData(self::IS_WYSIWYG_ENABLED, $isWysiwygEnabled);
    }

    /**
     * @inheritDoc
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function setUsedForSortBy($usedForSortBy)
    {
        return $this->setData(self::USED_FOR_SORT_BY, $usedForSortBy);
    }

    /**
     * @inheritDoc
     */
    public function setUsedInProductListing($usedInProductListing)
    {
        return $this->setData(self::USED_IN_PRODUCT_LISTING, $usedInProductListing);
    }
}
