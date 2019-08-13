<?php

class Addwish_Awext_Adminhtml_AwextController extends Mage_Adminhtml_Controller_action
{
	public function viewAction(){
		$this->loadLayout();
		$this->_initLayoutMessages('core/session');
		$this->_setActiveMenu('awext/awext');
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('AddWish'), Mage::helper('adminhtml')->__('AddWish'));
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

		$currentStore= Mage::app()->getDefaultStoreView()->getStoreId();
		if(Mage::app()->getRequest()->getParam('storeconfig')!=""){
			$currentStore=Mage::app()->getRequest()->getParam('storeconfig');
		}

		$loadValues= Mage::getModel('awext/awext')->load($currentStore);
		if(count($loadValues->getData())<=0){
			
			$data = array('id'=>$currentStore,'searchUUID'=>'');
			$model = Mage::getModel('awext/awext')->setData($data);
			$insertId = $model ->save()->getId();
		}
		if ($data = $this->getRequest()->getPost()) {
			switch($data['action']){
			case "dataexport":
				$k['ipaddress']=$data['ipaddress'];
				if(isset($data['enable_order_feed'])){
					$k['enable_order_export']=$data['enable_order_feed'];
				}else{
					$k['enable_order_export']=0;
				}
				if(isset($data['enable_product_feed'])){
					$k['enable_product_feed']=$data['enable_product_feed'];
				}else{
					$k['enable_product_feed']=0;
				}			
			break;
			case "scriptsetup":
				$k['userId']=$data['addwishID'];
			break;
			case "recomendations":
				if(isset($data['addwishUpsells'])){
					$k['enableUpsells']=$data['addwishUpsells'];
				}else{
					$k['enableUpsells']=0;
				}
			break;
			}
			
			$model = Mage::getModel('awext/awext');
			$model->setData($k)->setId($currentStore);

			try {
				$model->save();
				Mage::getSingleton('core/session')->addSuccess('Configuration was successfully saved.');
				$this->_redirectReferer();
			} catch (Exception $e) {
				Mage::getSingleton('core/session')->addError('Some error occured.');
			}
		}
		$this->_addContent($this->getLayout()->createBlock('awext/adminhtml_awext_view'))
			->_addLeft($this->getLayout()->createBlock('awext/adminhtml_awext_view_tabs'));
		$this->renderLayout();
	}
}