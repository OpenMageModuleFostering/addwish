<?php
class Addwish_Integrator_Model_Observer
    {

	public function inspectCartAddData($observer=null){
		$model = Mage::getModel('integrator/integrator')->load(1);
		if($model->getData('enableUpsells')==1){
			$url = Mage::getUrl('integrator/index/upsells');
			Mage::getSingleton('core/session')->addSuccess('Product Added to Cart!');
			Mage::app()->getFrontController()->getResponse()->setRedirect($url);
			Mage::app()->getResponse()->sendResponse();
			exit;
			exit;
		}
	}
	public function addWishCron($observer=null){
		$model = Mage::getModel('integrator/integrator')->load(1);
		$currentHour=date("h");
		if($model->getData('cron_time')!==$currentHour){
		return;
		}
		Mage::log("Running AddWish Cron");

		$products_out='<?xml version="1.0" encoding="UTF-8"?><products>';
		$products = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('visibility', 4);
		$products->addAttributeToSelect('*');
		foreach($products as $product) {
			$imageUrl = Mage::helper('catalog/image')->init($product , 'thumbnail')->resize(256);
			$specialPrice = number_format($product->getSpecialPrice(), 2, '.', '');
			$regularPrice = number_format($product->getPrice(), 2, '.', '');
			$products_out.="<product><url>".$product->getProductUrl()."</url><title>".htmlentities($product->getName(),ENT_QUOTES,'UTF-8')."</title><imgurl>".$imageUrl."</imgurl>";
			if(isset($specialPrice) && $specialPrice>0){
				$products_out.= "<price>".$specialPrice."</price>";
				$products_out.= "<previousprice>".$regularPrice."</previousprice>";
			}else{
				$products_out.= "<price>".$regularPrice."</price>";
			}
			if($product->getMetaKeyword()!=''){
				$products_out.= "<keywords>".htmlentities($product->getMetaKeyword(),ENT_QUOTES,'UTF-8')."</keywords>";
			}
			if($product->getDescription()!=''){
				$products_out.= "<description>".htmlentities($product->getDescription(),ENT_QUOTES,'UTF-8')."</description>";
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
		Mage::log('Feed Generated successfully.');
	}
		
    }
?>
