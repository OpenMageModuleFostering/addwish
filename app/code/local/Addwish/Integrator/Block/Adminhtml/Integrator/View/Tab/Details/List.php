<?php
class Addwish_Integrator_Block_Adminhtml_Integrator_View_Tab_Details_List extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        $this->setTemplate('integrator/integrator/list.phtml');
    }

    protected function _toHtml()
    {
		$integratorCollection = Mage::getModel('integrator/integrator')->getCollection();
		$this->assign('integratorCollection', $integratorCollection->getData());
		return parent::_toHtml();
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
