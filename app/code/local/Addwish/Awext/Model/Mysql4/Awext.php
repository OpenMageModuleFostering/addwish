<?php

class Addwish_Awext_Model_Mysql4_Awext extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('awext/awext', 'id');
	$this->_isPkAutoIncrement = false;
    }
}