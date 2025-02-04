<?php
declare(strict_types=1);

namespace DV\ProfitMetrics\Model;

use DV\ProfitMetrics\Model\ResourceModel\Visitor as VisitorResource;

/**
 * Class Visitor
 * @package DV\ProfitMetrics\Model
 *
 * @method int getId()
 * @method string getGacid()
 * @method $this setGacid(string $gacid)
 * @method string getGclid()
 * @method $this setGclid(string $gclid)
 * @method string getFbp()
 * @method $this setFbp(string $fbp)
 * @method string getFbc()
 * @method $this setFbc(string $fbc)
 * @method string getCua()
 * @method $this setCua(string $cua)
 * @method string getCip()
 * @method $this setCip(string $cip)
 * @method int getTimestamp()
 * @method $this setTimestamp(int $timestamp)
 * @method string getCreatedAt()
 * @method $this setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method $this setUpdatedAt(string $updatedAt)
 */
class Visitor extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'dv_profitmetrics_visitor';

    protected $_cacheTag = 'dv_profitmetrics_visitor';

    protected $_eventPrefix = 'dv_profitmetrics_visitor';

    protected function _construct(): void
    {
        $this->_init(VisitorResource::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
