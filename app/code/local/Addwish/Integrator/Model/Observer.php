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
	public function customerRegisterData($observer=null){
		$event = $observer->getEvent();
		$customer = $event->getCustomer();
		$email = $customer->getEmail();
		Mage::getSingleton('core/session')->setAddWishEmail($email);
	}
	public function addwishHeader($observer=null){
		$update=Mage::getSingleton('core/layout')->getUpdate();
		$update->addHandle('addwish_head_add');
	}
	public function getbillingData($observer=null){
		$addressObject=$observer->getEvent()->getQuoteAddress();
		$quote = $observer->getEvent()->getQuoteAddress()->getQuote();
		$email=$quote->getBillingAddress()->getEmail();
		Mage::getSingleton('core/session')->setAddWishEmail($email);
	}	
    }

?>
