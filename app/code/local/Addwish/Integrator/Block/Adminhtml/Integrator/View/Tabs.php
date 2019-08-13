<?php

class Addwish_Integrator_Block_Adminhtml_Integrator_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('integrator_tabs');
	  $this->setTitle(Mage::helper('integrator')->__('addwish Settings'));
  }

  protected function _beforeToHtml()
  {
		$this->addTab('details_section', array(
			'label'     => Mage::helper('integrator')->__('Setup'),
			'title'     => Mage::helper('integrator')->__('Setup'),
			'content'   => $this->getLayout()->createBlock('integrator/adminhtml_integrator_view_tab_details_list')
							->toHtml(),
			'active'    => ( $this->getRequest()->getParam('tab') == 'details_section' ) ? true : false,
		));

		$this->addTab('recommendation_section', array(
			'label'     => Mage::helper('integrator')->__('Recommendations'),
			'title'     => Mage::helper('integrator')->__('Recommendations'),
			'content'   => $this->getLayout()->createBlock('integrator/adminhtml_integrator_view_tab_recommend_list')
							->toHtml(),
			'active'    => ( $this->getRequest()->getParam('tab') == 'recommendation_section' ) ? true : false,
		));
		
		$this->addTab('search_section', array(
			'label'     => Mage::helper('integrator')->__('Search'),
			'title'     => Mage::helper('integrator')->__('Search'),
			'content'   => $this->getLayout()->createBlock('integrator/adminhtml_integrator_view_tab_search_list')
							->toHtml(),
			'active'    => ( $this->getRequest()->getParam('tab') == 'search_section' ) ? true : false,
		));
		return parent::_beforeToHtml();
  }
}