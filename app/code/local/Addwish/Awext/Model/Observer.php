<?php
class Addwish_Awext_Model_Observer
{

	public function inspectCartAddData($observer=null){
		$model = Mage::getModel('awext/awext')->load(1);
		if($model->getData('enableUpsells')==1) {
			$request = $observer->getEvent()->getRequest();
					
			$url = Mage::getUrl('awext/index/upsells');
			Mage::getSingleton('core/session')->addSuccess('Product Added to Cart!');
			$request->setParam('return_url', $url);		
		} else {
			return;
		}
	}
	
	public function customerRegisterData($observer=null){
		$event = $observer->getEvent();
		$customer = $event->getCustomer();
		$email = $customer->getEmail();
		Mage::getSingleton('core/session')->setAddwishEmail($email);
	}

	public function getBillingData($observer=null){
		$addressObject=$observer->getEvent()->getQuoteAddress();
		$quote = $observer->getEvent()->getQuoteAddress()->getQuote();
		$email=$quote->getBillingAddress()->getEmail();
		Mage::getSingleton('core/session')->setAddwishEmail($email);
	}	

	public function orderSuccess($observer) {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $block = Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('addwish-conversion');
        if ($block) {
            $block->setOrderIds($orderIds);
        }
    }
}
