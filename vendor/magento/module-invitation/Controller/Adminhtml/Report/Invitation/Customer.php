<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Invitation\Controller\Adminhtml\Report\Invitation;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Customer extends \Magento\Invitation\Controller\Adminhtml\Report\Invitation implements HttpGetActionInterface
{
    /**
     * Report by customers action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Invitation::report_magento_invitation_customer'
        )->_addBreadcrumb(
            __('Invitation Report by Customers'),
            __('Invitation Report by Customers')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Invited Customers Report'));
        $this->_view->renderLayout();
    }
}
