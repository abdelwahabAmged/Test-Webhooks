<?php

namespace PWA\Shipping\Model\Rate;

use Magento\Shipping\Model\Rate\Result;

class CarrierResult extends \Magento\Shipping\Model\Rate\CarrierResult
{
    /**
     * @var Result[][]
     */
    protected $results = [];

    /**
     * Append result received from a carrier.
     *
     * @param Result $result
     * @param bool $appendFailed Append result's errors as well.
     * @return void
     */
    public function appendResult(Result $result, bool $appendFailed): void
    {
        $this->results[] = ['result' => $result, 'appendFailed' => $appendFailed];
    }

    public function getAllRates()
    {
        //Appending previously received results.
        while ($resultData = array_shift($this->results)) {
            if ($resultData['result']->getError()) {
                if ($resultData['appendFailed']) {
                    $this->append($resultData['result']);
                }
            } else {
                $this->append($resultData['result']);
            }
        }

        return \Magento\Shipping\Model\Rate\Result::getAllRates();
    }
}
