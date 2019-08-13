<?php
class Addwish_Awext_Block_Adminhtml_Awext_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{

	}
	public function getHeaderText()
	{
		return Mage::helper('awext')->__("addwish");
	}
}