<?php

class Addwish_Awext_Adminhtml_AwextController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('awext/awext')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('AddWish Settings'), Mage::helper('adminhtml')->__('AddWish Settings'));
		return $this;
	}
	public function indexAction() {
		$this->_initAction()
		->renderLayout();
	}
	protected function _awextModule($moduleName) {
		// Disable the module itself
		$nodePath = "modules/$moduleName/active";
		if (Mage::helper('core/data')->isModuleEnabled($moduleName)) {
			Mage::getConfig()->setNode($nodePath, 'false', true);
		}

		// Disable its output as well (which was already loaded)
		$outputPath = "advanced/modules_disable_output/$moduleName";
		if (!Mage::getStoreConfig($outputPath)) {
			Mage::app()->getStore()->setConfig($outputPath, true);
		}
	}
	public function viewAction(){
		$this->loadLayout();
		$this->_initLayoutMessages('core/session');
		$this->_setActiveMenu('awext/awext');
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('AddWish'), Mage::helper('adminhtml')->__('AddWish'));
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('AddWish'), Mage::helper('adminhtml')->__('AddWish'));

		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		if ($data = $this->getRequest()->getPost()) {
			switch($data['action']){
			case "dataexport":
				$k['ipaddress']=$data['ipaddress'];
				$k['cron_time']=$data['cron_hour'];
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
			$model->setData($k)
			->setId(1);

			try {
				$model->save();
				Mage::getSingleton('core/session')->addSuccess('Configuration was successfully saved.');
			} catch (Exception $e) {
				Mage::getSingleton('customer/session')->addSuccess('Some error occured.');
				exit;
			}
		}
		$this->_addContent($this->getLayout()->createBlock('awext/adminhtml_awext_view'))
		->_addLeft($this->getLayout()->createBlock('awext/adminhtml_awext_view_tabs'));
		$this->renderLayout();
	}
	public function editAction() {

	}
	public function generatefeedAction(){
		$products_out='<?xml version="1.0" encoding="UTF-8"?><products>';
$currentStore=Mage::app()->getStore()->getStoreId();
		$products = Mage::getModel('catalog/product')->getCollection()->setStoreId($currentStore)->addAttributeToFilter('visibility', 4);
		$products->addAttributeToSelect('*');
		foreach($products as $product) {
			$imageUrl = Mage::helper('catalog/image')->init($product , 'thumbnail')->resize(256);
			$specialPrice = number_format($product->getSpecialPrice(), 2, '.', '');
			$regularPrice = number_format($product->getPrice(), 2, '.', '');
			$products_out.="<product><url>".$product->getProductUrl()."</url><title>".htmlspecialchars(htmlentities($product->getName(),ENT_QUOTES,'UTF-8'))."</title><imgurl>".$imageUrl."</imgurl>";
			if(isset($specialPrice) && $specialPrice>0){
				$products_out.= "<price>".$specialPrice."</price>";
				$products_out.= "<previousprice>".$regularPrice."</previousprice>";
			}else{
				$products_out.= "<price>".$regularPrice."</price>";
			}
			if($product->getMetaKeyword()!=''){
				$products_out.= "<keywords>".htmlspecialchars(htmlentities($product->getMetaKeyword(),ENT_QUOTES,'UTF-8'))."</keywords>";
			}
			if($product->getDescription()!=''){
				$products_out.= "<description>".htmlspecialchars(htmlentities($product->getDescription(),ENT_QUOTES,'UTF-8'))."</description>";
			}
			$products_out.= "<productnumber>".$product->getId()."</productnumber>";
			$products_out.= "<currency>".Mage::app()->getStore()->getCurrentCurrencyCode()."</currency>";
			$cats = $product->getCategoryIds();
			$products_out.= "<hierarchies>";
			foreach ($cats as $category_id) {
				$products_out.= "<hierarchy> ";
				$category = Mage::getModel('catalog/category')->load($category_id) ;
				$catnames = array();
				foreach ($category->getParentCategories() as $parent) {
					if(trim($parent->getName())!=""){
					 $catnames[] = $parent->getName();
					
					 $products_out.= "<category>".htmlspecialchars(htmlentities($parent->getName(),ENT_QUOTES,'UTF-8'))."</category>";
				       }
				}
				
				$products_out.= "</hierarchy> ";
			} 
			$products_out.= "</hierarchies>";
			if($product->getData('brand')){
				$products_out.= "<brand>".$product->getData('brand')."</brand>";
			}
			
			if($product->isInStock()){
				$products_out.= "<instock>true</instock>";
			}else{
				$products_out.= "<instock>false</instock>";
			}
			
			if($product->getData('gender')){
				$products_out.= "<gender>".$product->getData('gender')."</gender>";
			}
			if($product->getData('pricedetail')){
				$products_out.= "<pricedetail>".$product->getData('pricedetail')."</pricedetail>";
			}
			//
			$products_out.= "</product>";
		}
		$products_out.= "</products>";
		$file_path=Mage::getBaseDir()."/addwishProductExport.xml";
	 	$addwishProductExport = fopen($file_path, "w");
		fwrite($addwishProductExport, $products_out);
		fclose($addwishProductExport);
		Mage::getSingleton('core/session')->addSuccess('Feed Generated successfully.');
		$this->_redirect('*/*/view');

	}
	public function newAction()
	{
		$this->_forward('edit');
	}
}