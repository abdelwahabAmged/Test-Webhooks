<?php

namespace Murergrej\Hyva\Helper;

use DateTime;
use Magento\Framework\App\Helper\AbstractHelper;

class DeliveringDaysFromWarehouse extends AbstractHelper
{
    /**
     * Calculate remaining days from today to a specified delivery date.
     *
     * @param string|null $deliveryTime
     * @param string|null $defaultDeliveryTime
     * @return string
     */
    public function getRemainingDays($deliveryTime = null, $defaultDeliveryTime = null)
    {
        $today = new DateTime();

        if ($deliveryTime) {
            $remainingDays = $this->calculateRemainingDays($today, $deliveryTime);

            if ($remainingDays === '0') {
                return '2-3';
            }

            return $remainingDays;
        }

        if ($defaultDeliveryTime) {
            return $defaultDeliveryTime;
        }

        return '2-3'; // Default value if neither is set
    }

    /**
     * Calculate remaining days between today and a specified date.
     *
     * @param DateTime $today
     * @param string $targetDate
     * @return string
     */
    private function calculateRemainingDays(DateTime $today, string $targetDate): string
    {
        $today->setTime(0, 0);
        $deliveryDate = new DateTime($targetDate);

        if ($deliveryDate < $today) {
            return '0'; // Return 0 for past dates
        }

        $interval = $today->diff($deliveryDate);
        return $interval->format('%a');
    }
}
