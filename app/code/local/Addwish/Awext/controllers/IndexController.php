<?php

class Addwish_Awext_IndexController extends Mage_Core_Controller_Front_Action
{
	protected $model = null;

	public function preDispatch() {
		parent::preDispatch();
		$this->model = Mage::getModel('awext/awext')->load(1);
		return $this;
	}


	public function searchAction(){
		$this->loadLayout();
		$this->renderLayout();			   
	}

	public function upsellsAction(){          
		$this->loadLayout();
		$this->renderLayout();			   
	}

	protected function verifyAccess($return = false) {
		if($this->model->getData('ipaddress') != '') {
			$allowedIps=explode(",",$this->model->getData('ipaddress'));
			$ipaddress = self::getClientIp();
			if(!in_array($ipaddress,$allowedIps)) {
				if($return) {
					return false;
				}
				echo "Access Denied";
				exit;
			}
		}

		$storeId = $this->getRequest()->getParam('store');
        if (isset($storeId) && is_numeric($storeId)) {
            try {
                Mage::app()->getStore((int)$storeId);
                Mage::app()->setCurrentStore((int)$storeId);
                return;
            } catch (Exception $e) {
                echo "Store not found";
                exit;
            }
        } 
		if($return) {			
			return true;
		}
	}

	// No access control on this method
	public function infoAction() {
		header("Content-type: text/xml");		
		echo '<?xml version="1.0" encoding="UTF-8"?><info>';
		echo self::toXmlTag('version', $this->getExtensionVersion());
		$access = $this->verifyAccess(true);
		echo self::toXmlTag('access', $access ? 'true' : 'false');
		echo self::toXmlTag('clientIp', self::getClientIp());
		if($access) {
			$attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
			echo '<product_attributes>';
			foreach ($attributes as $attribute){
				echo '<attribute>';
			    	echo self::toXmlTag('attribute_code', $attribute->getAttributeCode());
			    	echo self::toXmlTag('attribute_name', $attribute->getFrontendLabel());
			    echo '</attribute>';
			}
			echo '</product_attributes>';
		} 

		echo '</info>';
		exit;
	}

	public function storesAction() {
		$this->verifyAccess();

		header("Content-type: text/xml");		
		echo '<?xml version="1.0" encoding="UTF-8"?><stores extension-version="'.self::getExtensionVersion().'">';
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                	echo '<store>';
                	echo self::toXmlTag('id', $store->getId());
                	echo self::toXmlTag('name', $store->getName());
                	echo '</store>';
                }
            }
        }
        echo '</stores>';
        exit;
	}
	
	public function orderListAction(){
		$this->verifyAccess();
		if($this->model->getData('enable_order_export')==0) {
			echo "Access Denied";
			exit;
		}

		$page = (int)$this->getRequest()->getParam('page');
		$pageSize = (int)$this->getRequest()->getParam('pageSize');

		$exportToDate = new DateTime($this->getRequest()->getParam('exportToDate'));
		if($this->getRequest()->getParam('exportFromDate')) {
			$exportFromDate = new DateTime($this->getRequest()->getParam('exportFromDate'));
		} else {
			$days = (int)$this->getRequest()->getParam('days', 7);
			$exportFromDate = clone $exportToDate;
			$exportFromDate->modify('-'.$days.' day');
		}

		$exportFromDate = $exportFromDate->format('Y-m-d H:i:s');
		$exportToDate = $exportToDate->format('Y-m-d H:i:s');

		$orders = Mage::getModel('sales/order')->getCollection()
			->addFieldToFilter('store_id', Mage::app()->getStore()->getStoreId())
			->addAttributeToFilter('created_at', array('from'=>$exportFromDate, 'to'=>$exportToDate))
			->addAttributeToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE));

		header("Content-type: text/xml");
		echo '<?xml version="1.0" encoding="UTF-8"?><orders extension-version="'.self::getExtensionVersion().'"';
		if($pageSize > 0) {
			$orders->setCurPage($page + 1);
			$orders->setPageSize($pageSize);
			echo ' last-page-number="' . ($orders->getLastPageNumber() - 1) . '"';
		} 
		echo '>';

		echo self::toXmlTag('exportFromDate', $exportFromDate);
		echo self::toXmlTag('exportToDate', $exportToDate);

		foreach($orders  as $order){
			$order_total = number_format($order->getData('base_grand_total'), 2, '.', '');
			echo '<order>';
			echo self::toXmlTag('orderDate', $order->getData('created_at'));
			echo self::toXmlTag('orderNumber', $order->getData('increment_id'));
			echo self::toXmlTag('orderTotal', $order_total);
			echo '<orderLines>';
			$items = $order->getAllItems();
			foreach($items as $orderItem) {
			  	$_product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
				echo '<orderLine>';
				echo self::toXmlTag('productnumber', $_product->getSku());
				echo self::toXmlTag('id', $_product->getId());
				echo self::toXmlTag('productURL', $_product->getProductUrl());
				echo self::toXmlTag('quantity', (int)$orderItem->getData('qty_ordered'));
				echo '</orderLine>';
			}
			echo '</orderLines></order>';
		}
		echo '</orders>';
		exit;
	}

	public function indexAction()
	{
		$this->verifyAccess();
		if($this->model->getData('enable_product_feed') == 0){
			echo "Access Denied";
			exit;
		}

		$page = (int)$this->getRequest()->getParam('page');
		$pageSize = (int)$this->getRequest()->getParam('pageSize');

		$products = Mage::getModel('catalog/product')
			->getCollection()
			->addStoreFilter(Mage::app()->getStore()->getStoreId())
			->addAttributeToFilter('visibility', 4)
			->addAttributeToSelect('*')->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));

		header("Content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="UTF-8"?><products extension-version="'.self::getExtensionVersion().'"';
		if($pageSize > 0) {
			$products->setCurPage($page + 1);
			$products->setPageSize($pageSize);
			echo ' last-page-number="' . ($products->getLastPageNumber() - 1) . '"';
		} 
		echo '>';
		$extraAttributes = $this->getRequest()->getParam('extraAttributes', '');
		if(trim($extraAttributes)) {
			$extraAttributes = explode(',', $extraAttributes);
		} else {
			$extraAttributes = array();
		}
		foreach($products as $product) {
			$data = Mage::helper('awext')->getProductData($product, $extraAttributes);
			$productOut = '<product>';
			foreach($data as $key => $value) {
				if($key == 'hierarchies') {
					$productOut.= "<hierarchies>";
					foreach($value as $hierarchy) {
						$productOut.= "<hierarchy>";
						foreach($hierarchy as $category) {
							$productOut .= self::toXmlTag('category', $category);
						}						
						$productOut.= "</hierarchy>";						
					}
					$productOut.= "</hierarchies>";						
				} else {
					if(is_array($value)) {
						$productOut .= "<$key>";
						foreach($value as $v) {
							$productOut .= self::toXmlTag('value', $v);
						}
						$productOut .= "</$key>";
					} else {
						$productOut .= self::toXmlTag($key, $value);
					}
				}
			}

			$productOut.= "</product>";
			echo $productOut;
		}
		echo "</products>";
		exit;
	}

	private static function toXmlTag($tag, $value) {
		if(is_bool($value)) {
			$value = $value ? 'true' : 'false';
		} else {
			$value = htmlspecialchars($value);
		}
		return "<$tag>".$value."</$tag>";
	} 

	private static function getClientIp() {
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
	        if (array_key_exists($key, $_SERVER) === true){
	            foreach (explode(',', $_SERVER[$key]) as $ip){
	                $ip = trim($ip);

	                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
	                    return $ip;
	                }
	            }
	        }
	    }
        return 'UNKNOWN';
	}

	private static function getExtensionVersion()
	{
		return Mage::getConfig()->getNode()->modules->Addwish_Awext->version;		
	}

}
