<?php
namespace Magenest\CareSoft\Model\Config\Source;

use Magenest\CareSoft\Model\CaresoftApi;
use Magento\Framework\Data\OptionSourceInterface;

class Group implements OptionSourceInterface
{
    /** @var CaresoftApi */
    private $caresoftApi;

    /**
     * @param CaresoftApi $caresoftApi
     */
    public function __construct(CaresoftApi $caresoftApi)
    {
        $this->caresoftApi = $caresoftApi;
    }

    /**
	 * @inheritDoc
	 */
	public function toOptionArray()
    {
        $result = [];

        $groups = $this->caresoftApi->getGroup();
        foreach ($groups as $group) {
            if (isset($group['group_id']) && isset($group['group_name'])) {
                $result[] = ['value' => $group['group_id'], 'label' => $group['group_name']];
            }
        }

        return $result;
	}
}
