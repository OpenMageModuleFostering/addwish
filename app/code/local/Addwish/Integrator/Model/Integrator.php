<?php

class Addwish_Integrator_Model_Integrator extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('integrator/integrator');
    }
}