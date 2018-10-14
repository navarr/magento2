<?php

namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\TestFramework\ObjectManager;

/**
 *
 */
class AttributeRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var CategoryAttributeRepositoryInterface */
    private $categoryAttributeRepository;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->categoryAttributeRepository = ObjectManager::getInstance()->get(AttributeRepository::class);
    }

    /**
     * Data Provider of attribute names
     *
     * @return array
     */
    public function sampleAttributeNames()
    {
        return [
            ['name'],
            ['page_layout'],
            ['path'],
            ['url_key'],
            ['meta_title'],
            ['description'],
            ['image'],
            ['is_active'],
        ];
    }

    /**
     * Ensure that a category attribute loaded from the repository matches the service contract
     *
     * @dataProvider sampleAttributeNames
     * @param string $attributeCode
     * @return void
     */
    public function testCategoryAttributeMeetsServiceContract($attributeCode)
    {
        $attribute = $this->categoryAttributeRepository->get($attributeCode);

        $this->assertInstanceOf(CategoryAttributeInterface::class, $attribute);
    }
}
