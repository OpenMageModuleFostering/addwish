<?php
$installer = $this;
$installer->startSetup();
$prefix = Mage::getConfig()->getTablePrefix();

$installer->run("CREATE TABLE IF NOT EXISTS `".$prefix."addwish` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `enableUpsells` int(11) NOT NULL,
  `ipaddress` text NOT NULL,
  `cron_time` varchar(11) NOT NULL,
  `enable_product_feed` tinyint(4) NOT NULL,
  `enable_order_export` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `addwish`
--
truncate `".$prefix."addwish`;
delete from `".$prefix."core_url_rewrite` where request_path in ('addwish-search-result.html','orderExportFeed.xml');
INSERT INTO `".$prefix."addwish` (`id`, `userId`, `enableUpsells`,`ipaddress`) VALUES ('1', '', '0','46.137.110.51');");

Mage::getModel('core/url_rewrite')->setIsSystem(0)->setOptions('')->setIdPath('Add Wish Search')->setTargetPath('/integrator/index/search')->setRequestPath('addwish-search-result.html')->save();
Mage::getModel('core/url_rewrite')->setIsSystem(0)->setOptions('')->setIdPath('Add Wish Order list')->setTargetPath('integrator/index/orderlist')->setRequestPath('orderExportFeed.xml')->save();


$installer->endSetup();
?>
