<?php
class Addwish_Awext_Block_Adminhtml_Awext extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_awext';
    $this->_blockGroup = 'awext';
    $this->_headerText = Mage::helper('awext')->__('AddWish');
    $this->_addButtonLabel = Mage::helper('awext')->__('AddWish');
    parent::__construct();
  }
}