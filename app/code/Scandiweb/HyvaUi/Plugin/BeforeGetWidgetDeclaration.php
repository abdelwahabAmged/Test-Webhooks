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
use Magento\Widget\Model\Widget;

/**
 * Class BeforeGetWidgetDeclaration
 */
class BeforeGetWidgetDeclaration
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
	 * @param Widget $subject
	 * @param $type
	 * @param array $params
	 * @param bool $asIs
	 *
	 * @return mixed
	 */
	public function beforeGetWidgetDeclaration(Widget $subject, $type, $params = [], $asIs = true)
	{
		if (isset($params['slides_data'])) {
			foreach ($params['slides_data'] as $key => &$param) {
				if ($key === '__empty') {
					unset($params['slides_data']['__empty']);

					continue;
				}

				$param['uniqueid'] = $key;
				$param = $this->serializer->serialize($param);
			}
		}

		if (isset($params['sections'])) {
			foreach ($params['sections'] as $key => &$param) {
				if ($key === '__empty') {
					unset($params['sections']['__empty']);

					continue;
				}

				$param['uniqueid'] = $key;
				$param = $this->serializer->serialize($param);
			}
		}


		return [$type, $params, $asIs];
	}
}