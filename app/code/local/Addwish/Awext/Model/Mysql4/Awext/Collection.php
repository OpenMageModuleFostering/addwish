<?php

class Addwish_Awext_Model_Mysql4_Awext_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('awext/awext');
    }
}