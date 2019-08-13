<?php
class Addwish_Integrator_Block_Adminhtml_Integrator extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_integrator';
    $this->_blockGroup = 'integrator';
    $this->_headerText = Mage::helper('integrator')->__('AddWish');
	$this->_addButtonLabel = Mage::helper('integrator')->__('AddWish');
    parent::__construct();
  }
}