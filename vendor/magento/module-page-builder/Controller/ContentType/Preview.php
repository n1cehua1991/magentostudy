<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PageBuilder\Controller\ContentType;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Preview controller to render blocks preview on Stage
 *
 * This isn't placed within the adminhtml folder as it has to extend from the front-end controllers app action to
 * ensure the content is rendered in the storefront scope.
 *
 * @api
 */
class Preview extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\PageBuilder\Model\Stage\RendererPool
     */
    private $rendererPool;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\PageBuilder\Model\Stage\RendererPool $rendererPool
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\PageBuilder\Model\Stage\RendererPool $rendererPool
    ) {
        parent::__construct($context);

        $this->rendererPool = $rendererPool;
    }

    /**
     * Generates an HTML preview for the stage
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $pageResult = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        // Some template filters and directive processors expect this to be called in order to function.
        $pageResult->initLayout();

        $params = $this->getRequest()->getParams();
        $renderer = $this->rendererPool->getRenderer($params['role']);
        $result = ['data' => $renderer->render($params)];

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
