<?php
/** @noinspection PhpUnused */
declare(strict_types=1);

namespace Magenest\OutOfStockAtLast\Plugin\Model\ResourceModel\Product;

use Magenest\Directory\Block\Adminhtml\Area\Field\Area;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Http\Context;
use Magento\Framework\DB\Select;

/**
 * Class CollectionPlugin applying sort order
 */
class CollectionPlugin
{
    /**
     * @var \Magenest\CustomSource\Helper\Data
     */
    private $dataHelper;

    /**
     * CollectionPlugin constructor.
     * @param Context $context
     */
    public function __construct(\Magenest\CustomSource\Helper\Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @var array
     */
    private $skipFlags = [];

    /**
     * Setting order and determine flags
     *
     * @param Collection $subject
     * @param mixed $attribute
     * @param string $dir
     * @return array
     * @noinspection PhpClassConstantAccessedViaChildClassInspection
     */
    public function beforeSetOrder(
        Collection $subject,
        $attribute,
        string $dir = Select::SQL_DESC
    ): array {
        $subject->setFlag('is_processing', true);
        $this->applyOutOfStockAtLastOrders($subject);

        $flagName = $this->_getFlag($attribute);

        if ($subject->getFlag($flagName)) {
            $this->skipFlags[] = $flagName;
        }

        $subject->setFlag('is_processing', false);
        return [$attribute, $dir];
    }

    /**
     * Get flag by attribute
     *
     * @param string $attribute
     * @return string
     */
    private function _getFlag(string $attribute): string
    {
        return 'sorted_by_' . $attribute;
    }

    /**
     * Try to determine applied sorting attribute flags
     *
     * @param Collection $subject
     * @param callable $proceed
     * @param mixed $attribute
     * @param string $dir
     * @return Collection
     * @noinspection PhpUnused
     * @noinspection PhpClassConstantAccessedViaChildClassInspection
     */
    public function aroundSetOrder(
        Collection $subject,
        callable $proceed,
        $attribute,
        string $dir = Select::SQL_DESC
    ): Collection {
        $flagName = $this->_getFlag($attribute);
        if (!in_array($flagName, $this->skipFlags)) {
            $proceed($attribute, $dir);
        }

        return $subject;
    }

    /**
     * Apply sort orders
     *
     * @param Collection $collection
     * @noinspection PhpClassConstantAccessedViaChildClassInspection
     * @noinspection PhpRedundantOptionalArgumentInspection
     */
    private function applyOutOfStockAtLastOrders(Collection $collection)
    {
        if (!$collection->getFlag('is_sorted_by_oos')) {
            $areaCode = $this->dataHelper->getCurrentArea();
            $collection->setFlag('is_sorted_by_oos', true);
            $collection->setOrder('out_of_stock_es_at_last_' . $areaCode, Select::SQL_DESC);
        }
    }

    /**
     * Determine and set order if necessary
     *
     * @param Collection $subject
     * @param mixed $attribute
     * @param string $dir
     * @return array
     * @noinspection PhpUnused
     * @noinspection PhpClassConstantAccessedViaChildClassInspection
     */
    public function beforeAddOrder(
        Collection $subject,
        $attribute,
        string $dir = Select::SQL_DESC
    ): array {
        if (!$subject->getFlag('is_processing')) {
            $result = $this->beforeSetOrder($subject, $attribute, $dir);
        }

        return $result ?? [$attribute, $dir];
    }
}
