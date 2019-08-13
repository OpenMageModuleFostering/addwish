<?php
class Addwish_Awext_IndexController extends Mage_Core_Controller_Front_Action
{
	public function searchAction(){
		$this->loadLayout();
		$this->renderLayout();			   
	}
	public function upsellsAction(){          
		$this->loadLayout();
		$this->renderLayout();			   
	}
	public function orderListAction(){
		$model = Mage::getModel('awext/awext')->load(1);
		if($model->getData('ipaddress')!=''){
		$allowedIps=explode(",",$model->getData('ipaddress'));
		 $ipaddress = '';
		    if ($_SERVER['HTTP_CLIENT_IP'])
		        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		    else if($_SERVER['HTTP_X_FORWARDED_FOR'])
		        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		    else if($_SERVER['HTTP_X_FORWARDED'])
		        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		    else if($_SERVER['HTTP_FORWARDED_FOR'])
		        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		    else if($_SERVER['HTTP_FORWARDED'])
		        $ipaddress = $_SERVER['HTTP_FORWARDED'];
		    else if($_SERVER['REMOTE_ADDR'])
		        $ipaddress = $_SERVER['REMOTE_ADDR'];
		    else
		        $ipaddress = 'UNKNOWN';
	        if(!in_array($ipaddress,$allowedIps)){
	       	 echo "Access Denied";
	       	 exit;
	        }
		
		}
 if($model->getData('enable_order_export')==0){
	       	 echo "Access Denied";
	       	 exit;
	        }

		$exportFromDate=$this->getRequest()->getParam('exportFromDate');
		$exportToDate=$this->getRequest()->getParam('exportToDate');
		$fromDate = date('Y-m-d H:i:s', strtotime($exportFromDate));
		$toDate = date('Y-m-d H:i:s', strtotime($exportToDate));
		header("Content-type: text/xml");
		echo '<?xml version="1.0" encoding="UTF-8"?><orders><exportFromDate>'.$exportFromDate.'</exportFromDate><exportToDate>'.$exportToDate.'</exportToDate>';
		$orders = Mage::getModel('sales/order')->getCollection()
		    ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
		    ->addAttributeToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE));
		    foreach($orders  as $order){
		    $order_total=number_format($order->getData('base_grand_total'), 2, '.', '');
			    echo '<order><orderDate>'.$order->getData('created_at').'</orderDate><orderNumber>'.$order->getData('increment_id').'</orderNumber><orderTotal>'.$order_total.'</orderTotal><orderLines>';
			    $items = $order->getAllItems();
			foreach($items as $orderItem):
			  $_product = Mage::getModel('catalog/product')
			            ->load($orderItem->getProductId());
			            echo '<orderLine><productnumber>'.$_product->getId().'</productnumber><productURL>'.$_product->getProductUrl().'</productURL><quantity>'.(int)$orderItem->getData('qty_ordered').'</quantity></orderLine>';
			endforeach;
			echo '</orderLines></order>';
		    }
		    echo '</orders>';
		    		//echo "</pre>";


		exit;
	}
	public function indexAction()
	{
$model = Mage::getModel('awext/awext')->load(1);
 if($model->getData('enable_product_feed')==0){
	       	 echo "Access Denied";
	       	 exit;
	        }
if($model->getData('ipaddress')!=''){
$allowedIps=explode(",",$model->getData('ipaddress'));
		 $ipaddress = '';
		    if ($_SERVER['HTTP_CLIENT_IP'])
		        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		    else if($_SERVER['HTTP_X_FORWARDED_FOR'])
		        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		    else if($_SERVER['HTTP_X_FORWARDED'])
		        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		    else if($_SERVER['HTTP_FORWARDED_FOR'])
		        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		    else if($_SERVER['HTTP_FORWARDED'])
		        $ipaddress = $_SERVER['HTTP_FORWARDED'];
		    else if($_SERVER['REMOTE_ADDR'])
		        $ipaddress = $_SERVER['REMOTE_ADDR'];
		    else
		        $ipaddress = 'UNKNOWN';
	        if(!in_array($ipaddress,$allowedIps)){
	       	 echo "Access Denied";
	       	 exit;
	        }
}
		header("Content-type: text/xml");
		$products_out='<?xml version="1.0" encoding="UTF-8"?><products>';
		$products = Mage::getModel('catalog/product')->getCollection()->addStoreFilter(Mage::app()->getStore()->getStoreId())->addAttributeToFilter('visibility', 4);
		$products->addAttributeToSelect('*')->addAttributeToFilter(
    'status',
    array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
);
		foreach($products as $product) {
			$imageUrl = Mage::helper('catalog/image')->init($product , 'thumbnail')->resize(256);
			$specialPrice = number_format($product->getSpecialPrice(), 2, '.', '');
			$regularPrice = number_format($product->getPrice(), 2, '.', '');
			$products_out.="<product><url>".$product->getProductUrl()."</url><title>".htmlspecialchars(htmlentities($product->getName(),ENT_QUOTES,'UTF-8'))."</title><imgurl>".$imageUrl."</imgurl>";
			if(isset($specialPrice) && $specialPrice>0 && $specialPrice<$regularPrice ){
				$today = date('Y-m-d');
				$today=date('Y-m-d', strtotime($today));;
				$spcialPriceDateBegin = date('Y-m-d', strtotime($product->getSpecialFromDate()));
				$spcialPriceDateEnd = date('Y-m-d', strtotime($product->getSpecialToDate()));

				if (($today > $spcialPriceDateBegin) && ($today < $spcialPriceDateEnd))
				{
					$products_out.= "<price>".number_format(Mage::getModel('directory/currency')->formatTxt($specialPrice, array('display' => Zend_Currency::NO_SYMBOL)), 2, '.', '')."</price>";
					$products_out.= "<previousprice>".number_format(Mage::getModel('directory/currency')->formatTxt($regularPrice, array('display' => Zend_Currency::NO_SYMBOL)), 2, '.', '')."</previousprice>";
				}
				else
				{
					$products_out.= "<price>".number_format(Mage::getModel('directory/currency')->formatTxt($regularPrice, array('display' => Zend_Currency::NO_SYMBOL)), 2, '.', '')."</price>";
				}
				
				
				
				
				
			}else{
				$products_out.= "<price>".number_format(Mage::getModel('directory/currency')->formatTxt($regularPrice, array('display' => Zend_Currency::NO_SYMBOL)), 2, '.', '')."</price>";
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
			$product_inStock=0;
			if($product->isConfigurable()){
				$allProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
				foreach ($allProducts as $productD) {
					if (!$productD->isSaleable()|| $productD->getIsInStock()==0) {
						//out of stock for check child simple product
					}else{
						$product_inStock=1;
					}
				}
				if($product_inStock==1){
					$products_out.= "<instock>true</instock>";
				}else{
					$products_out.= "<instock>false</instock>";
				}
			}else{
				if($product->isInStock()){
					$products_out.= "<instock>true</instock>";
				}else{
					$products_out.= "<instock>false</instock>";
				}
			}
			
			if($product->getData('gender')){
				$attr = $product->getResource()->getAttribute("gender");
				$genderLabel = $attr->getSource()->getOptionText($product->getData('gender'));
				$products_out.= "<gender>".$genderLabel."</gender>";
			}
			if($product->getData('pricedetail')){
				$products_out.= "<pricedetail>".$product->getData('pricedetail')."</pricedetail>";
			}
			//
			$products_out.= "</product>";
		}
		$products_out.= "</products>";
		echo $products_out;
		exit;
	}
}
?>