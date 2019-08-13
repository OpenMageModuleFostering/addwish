<?php

class Addwish_Awext_Block_Adminhtml_Awext_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('awextGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('awext/awext')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
			'header'    => Mage::helper('awext')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'id',
		));

		$this->addColumn('email', array(
			'header'    => Mage::helper('awext')->__('Email ID'),
			'align'     => 'left',
			'index'     => 'email',
		));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('awext');
		return $this;
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

}