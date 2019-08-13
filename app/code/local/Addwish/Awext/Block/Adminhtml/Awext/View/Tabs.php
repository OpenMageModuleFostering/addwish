<?php

class Addwish_Awext_Block_Adminhtml_Awext_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('awext_tabs');
	  $this->setTitle(Mage::helper('awext')->__('addwish settings'));
  }

  protected function _beforeToHtml()
  {
		$this->addTab('details_section', array(
			'label'     => Mage::helper('awext')->__('Setup'),
			'title'     => Mage::helper('awext')->__('Setup'),
			'content'   => $this->getLayout()->createBlock('awext/adminhtml_awext_view_tab_details')
							->toHtml(),
			'active'    => ( $this->getRequest()->getParam('tab') == 'details_section' ) ? true : false,
		));

		$this->addTab('recommendation_section', array(
			'label'     => Mage::helper('awext')->__('Recommendations'),
			'title'     => Mage::helper('awext')->__('Recommendations'),
			'content'   => $this->getLayout()->createBlock('awext/adminhtml_awext_view_tab_recommend')
							->toHtml(),
			'active'    => ( $this->getRequest()->getParam('tab') == 'recommendation_section' ) ? true : false,
		));
		
		$this->addTab('search_section', array(
			'label'     => Mage::helper('awext')->__('Search'),
			'title'     => Mage::helper('awext')->__('Search'),
			'content'   => $this->getLayout()->createBlock('awext/adminhtml_awext_view_tab_search')
							->toHtml(),
			'active'    => ( $this->getRequest()->getParam('tab') == 'search_section' ) ? true : false,
		));
		return parent::_beforeToHtml();
  }
}