<?php
class Addwish_Integrator_Block_Adminhtml_Integrator_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{

	}
	public function getHeaderText()
	{
		return Mage::helper('integrator')->__("addwish");
	}
}