<?php

class Addwish_Awext_Model_Awext extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('awext/awext');
    }
}