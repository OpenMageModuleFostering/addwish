<?php
class Addwish_Awext_Block_Adminhtml_Awext_View_Tab_Search_List extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        $this->setTemplate('awext/search.phtml');
    }

    protected function _toHtml()
    {
		$awextCollection = Mage::getModel('awext/awext')->getCollection();
		$this->assign('awextCollection', $awextCollection->getData());
		return parent::_toHtml();
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
