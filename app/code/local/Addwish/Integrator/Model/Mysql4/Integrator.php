<?php

class Addwish_Integrator_Model_Mysql4_Integrator extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('integrator/integrator', 'id');
    }
}