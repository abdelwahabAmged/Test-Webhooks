<?php

namespace Murergrej\ConfigurableProduct\Plugin;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ConfigurableBlock;

class AddOptionWeightToConfigurable
{
    /**
     * @param ConfigurableBlock $subject
     * @param string $jsonResult
     * @return string
     */
    public function afterGetJsonConfig(ConfigurableBlock $subject, $jsonResult)
    {
        // Decode the existing JSON result
        $config = json_decode($jsonResult, true);

        // Retrieve the product weights
        $optionWeights = $this->getOptionWeight($subject);

        // Add optionWeight to the config array
        $config['optionWeight'] = $optionWeights;

        // Encode the modified config back to JSON
        return json_encode($config);
    }

    /**
     * Retrieve option weights
     *
     * @param ConfigurableBlock $subject
     * @return array
     */
    protected function getOptionWeight(ConfigurableBlock $subject): array
    {
        $weights = [];
        $allowProducts = $subject->getAllowProducts();

        foreach ($allowProducts as $product) {
            $weights[$product->getId()] = (float)$product->getWeight();
        }

        return $weights;
    }
}
