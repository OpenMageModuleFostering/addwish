<?php
class Addwish_Integrator_IndexController extends Mage_Core_Controller_Front_Action
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
		$model = Mage::getModel('integrator/integrator')->load(1);
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
$this->_redirect('/');
	}
}
?>