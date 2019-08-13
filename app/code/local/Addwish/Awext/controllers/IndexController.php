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

	protected function verifyAccess() {
		if($this->model->getData('ipaddress')!='') {
			$allowedIps=explode(",",$this->model->getData('ipaddress'));
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
			if(!in_array($ipaddress,$allowedIps)) {
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

	}

	public function storesAction() {
		$this->verifyAccess();

		header("Content-type: text/xml");		
		echo '<?xml version="1.0" encoding="UTF-8"?><stores>';
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

	}


	public function orderListAction(){
		$this->verifyAccess();
		if($this->model->getData('enable_order_export')==0) {
			echo "Access Denied";
			exit;
		}

		$page = (int)$this->getRequest()->getParam('page');
		$pageSize = (int)$this->getRequest()->getParam('pageSize');


		$exportFromDate=$this->getRequest()->getParam('exportFromDate');
		$exportToDate=$this->getRequest()->getParam('exportToDate');
		$fromDate = date('Y-m-d H:i:s', strtotime($exportFromDate));
		$toDate = date('Y-m-d H:i:s', strtotime($exportToDate));
		$orders = Mage::getModel('sales/order')->getCollection()
			->addFieldToFilter('store_id', Mage::app()->getStore()->getStoreId())
			->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
			->addAttributeToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE));
		header("Content-type: text/xml");
		echo '<?xml version="1.0" encoding="UTF-8"?><orders';
		if($pageSize > 0) {
			$orders->setCurPage($page + 1);
			$orders->setPageSize($pageSize);
			echo ' last-page-number="' . ($orders->getLastPageNumber() - 1) . '"';
		} 
		echo '><exportFromDate>'.$exportFromDate.'</exportFromDate><exportToDate>'.$exportToDate.'</exportToDate>';
		foreach($orders  as $order){
			$order_total=number_format($order->getData('base_grand_total'), 2, '.', '');
			echo '<order><orderDate>'.$order->getData('created_at').'</orderDate><orderNumber>'.$order->getData('increment_id').'</orderNumber><orderTotal>'.$order_total.'</orderTotal><orderLines>';
			$items = $order->getAllItems();
			foreach($items as $orderItem) {
			  	$_product = Mage::getModel('catalog/product')
					->load($orderItem->getProductId());
				echo '<orderLine><productnumber>'.$_product->getId().'</productnumber><productURL>'.$_product->getProductUrl().'</productURL><quantity>'.(int)$orderItem->getData('qty_ordered').'</quantity></orderLine>';
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
		
		echo '<?xml version="1.0" encoding="UTF-8"?><products';
		if($pageSize > 0) {
			$products->setCurPage($page + 1);
			$products->setPageSize($pageSize);
			echo ' last-page-number="' . ($products->getLastPageNumber() - 1) . '"';
		} 
		echo '>';

		foreach($products as $product) {

			$data = Mage::helper('awext')->getProductData($product);
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
					$productOut .= self::toXmlTag($key, $value);
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

}
