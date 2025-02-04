<?php
/**
 * @category  Scandiweb
 * @package   Scandiweb\HyvaUi
 * @author    Scandiweb <info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\HyvaUi\Helper;

use Scandiweb\HyvaUi\Helper\FileParser;
use Exception;
use Magento\Cms\Api\PageRepositoryInterface as CmsPageRepository;
use Magento\Cms\Model\BlockFactory as CmsBlockFactory;
use Magento\Cms\Model\BlockRepository as CmsBlockRepository;
use Magento\Cms\Model\ResourceModel\Block as CmsBlockResource;
use Magento\Cms\Model\Page as CmsPage;
use Magento\Cms\Model\PageFactory as CmsPageFactory;
use Magento\Cms\Model\ResourceModel\Page as CmsPageResource;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class CmsHelper
 * @package Scandiweb\HyvaUi\Helper
 */
class CmsHelper
{
    /**
     * CmsBlockHelper constructor.
     * @param CmsBlockFactory $blockFactory
     * @param CmsBlockRepository $blockRepository
     * @param CmsBlockResource $blockResource
     * @param CmsPage $page
     * @param CmsPageFactory $pageFactory
     * @param CmsPageRepository $pageRepository
     * @param CmsPageResource $pageResource
     * @param FileParser $fileParser
     * @param ResourceConnection $resourceConnection
     * @param ScopeConfigInterface $scopeConfig
     * @param State $appState
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        protected CmsBlockRepository $blockRepository,
        protected CmsBlockFactory $blockFactory,
        protected CmsBlockResource $blockResource,
        protected CmsPage $page,
        protected CmsPageFactory $pageFactory,
        protected CmsPageRepository $pageRepository,
        protected CmsPageResource $pageResource,
        protected FileParser $fileParser,
        protected ResourceConnection $resourceConnection,
        protected ScopeConfigInterface $scopeConfig,
        protected State $appState,
        protected SchemaSetupInterface $schemaSetup
    ) {
        try {
            $appState->getAreaCode();
        } catch (Exception $e) {
            $appState->setAreaCode(Area::AREA_ADMINHTML);
        }
    }

    /**
     * Create CMS block. If block already exists, update content.
     *
     * @param string $identifier
     * @param string $title
     * @param string $filePath
     * @param string|int|array $storeId
     * @return void
     *
     * @throws Exception
     */
    public function createOrUpdateCmsBlock($identifier, $title, $filePath = null, $storeId = 0, $forceUpdate = false): void
    {
        $mainStoreId = is_array($storeId) ? $storeId[0] : $storeId;
        $cmsBlock = $this->blockFactory->create();
        $cmsBlock->setStoreId($mainStoreId);
        $this->blockResource->load($cmsBlock, $identifier);

        if (!$cmsBlock->getId() || $forceUpdate) {
            $cmsBlock
                ->setIdentifier($identifier)
                ->setTitle($title);

            if ($mainStoreId !== $storeId) {
                $cmsBlock->setStores($storeId);
            } else {
                $cmsBlock->setStores([$mainStoreId]);
            }

            $content = "";
            if($filePath && $filePath !== '') {
                $content = $this->fileParser->getHtmlContent($filePath);
            }

            $cmsBlock->setContent($content);
            $this->blockRepository->save($cmsBlock);
        }
    }

    /**
     * Create CMS page. If page already exists, update content.
     *
     * @param string $identifier
     * @param string $title
     * @param string $filePath
     * @param string|int|array $storeId
     * @return void
     *
     * @throws Exception
     */
    public function createOrUpdateCmsPage($data, $filePath, $storeId = 0, $forceUpdate = false): void
    {
        $mainStoreId = is_array($storeId) ? $storeId[0] : $storeId;
        $pageContent = $this->fileParser->getHtmlContent($filePath);

        $defaultData = [
            'title' => 'Default Page Title',
            'page_layout' => $this->scopeConfig
                ->getValue('web/default_layouts/default_cms_layout'),
            'meta_keywords' => '',
            'meta_description' => '',
            'identifier' => 'default-page-identifier',
            'content_heading' => '',
            'content' => $pageContent,
            'layout_update_xml' => '',
            'is_active' => 1,
            'stores' => [$storeId],
            'sort_order' => 0
        ];

        $completeData = array_merge($defaultData, $data);

        $cmsPage = $this->pageFactory->create();
        $this->pageResource->load($cmsPage, $completeData['identifier']);

        if (!$cmsPage->getId() || $forceUpdate) {
            $cmsPage->setData($completeData);
            $this->pageRepository->save($cmsPage);
        }
    }

    /**
     * Create CMS page. If page already exists, update content.
     *
     * @param array $template
     * @return void
     *
     * @throws Exception
     */
    public function createPbTemplate($template): void
    {
        $this->schemaSetup->startSetup();

        $table = $this->schemaSetup->getTable('pagebuilder_template');
        $data = $template;
        $templateHtml = $this->fileParser->getHtmlContent($template['path']);
        $data['template'] = $templateHtml;
        unset($data['path']);
        $this->schemaSetup->getConnection()->insert($table, $data);

        $this->schemaSetup->endSetup();
    }
}
