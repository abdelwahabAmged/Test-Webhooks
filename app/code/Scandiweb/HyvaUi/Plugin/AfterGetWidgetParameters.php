<?php
/**
 * @category    Scandiweb
 * @author      Aleksejs Prjahins <info@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Plugin;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Widget\Model\Widget\Instance;

/**
 * Class AfterGetWidgetParameters
 */
class AfterGetWidgetParameters
{
    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * AfterGetWidgetParameters constructor.
     *
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * After Get Widget Parameters
     *
     * @param Instance $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetWidgetParameters(Instance $subject, $result)
    {
        foreach ($result as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $innerKey => $innerValue) {
                    if (is_array($innerValue)) {
                        $value[$innerKey] = $this->serializer->serialize($innerValue);
                    }
                }

                $result[$key] = $value;
            }
        }

        return $result;
    }
}
