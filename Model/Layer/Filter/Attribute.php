<?php

namespace Algolia\AlgoliaSearch\Model\Layer\Filter;

class Attribute extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute
{
    /** @var array */
    protected $currentFilterValue = [];

    /** @var \Magento\Framework\Escaper */
    private $escaper;

    /** @var \Magento\Framework\Filter\StripTags */
    private $tagFilter;

    /**
     * Constructor.
     *
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $data
        );

        $this->tagFilter = $tagFilter;
        $this->escaper = $escaper;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attribute = $this->getAttributeModel();
        $attributeValue = $request->getParam($this->_requestVar);
        if (!is_null($attributeValue)) {
            $attributeValue = explode("~", $request->getParam($this->_requestVar));
        }

        if (empty($attributeValue)) {
            return $this;
        }

        if (!is_array($attributeValue)) {
            $attributeValue = [$attributeValue];
        }

        $this->currentFilterValue = array_values($attributeValue);

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $productCollection->addFieldToFilter($attribute->getAttributeCode(), ['in' => $this->currentFilterValue]);
        $layerState = $this->getLayer()->getState();

        foreach ($this->currentFilterValue as $currentFilter) {
            $filter = $this->_createItem($this->escaper->escapeHtml(
                $this->getOptionText($currentFilter)),
                $this->currentFilterValue
            );
            $layerState->addFilter($filter);
        }

        return $this;
    }
}
