<?php
/**
 * @name         :  Sariina Aasud
 * @version      :  0.0.1
 * @since        :  Magento 1.9.1
 * @author       :  Sariina - http://www.sariina.com
 * @copyright    :  Copyright (C) 2015 Powered by Sariina
 * @license      :  This source file is subject to the EULA that is bundled with this package in the file SARIINA-LICENSE.txt.
 * @Creation Date:  Apr 29 2015
 **/
class Sariina_CustomerPoints_Block_Adminhtml_Customerpoints_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('sariina_customerpoints_history_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection() {
        $firstname = Mage::getModel('eav/entity_attribute')->loadByCode('1', 'firstname');
        $lastname = Mage::getModel('eav/entity_attribute')->loadByCode('1', 'lastname');
        $collection = Mage::getModel("sariina_customerpoints/history")->getCollection();
        $collection->getSelect()
            ->join(
                ['ce1' => 'customer_entity_varchar'],
                'ce1.entity_id = main_table.customer_id',
                ['firstname' => 'value']
            )
           ->where('ce1.attribute_id=' . $firstname->getAttributeId()) 
           ->join(
                ['ce2' => 'customer_entity_varchar'],
                'ce2.entity_id = main_table.customer_id',
                ['lastname' => 'value']
            )
            ->where('ce2.attribute_id = ' . $lastname->getAttributeId())
            ->columns(['customer_name' => 'CONCAT_WS(" ", ce1.value, ce2.value)']);

        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }
 
    protected function _prepareColumns() {
        $helper = Mage::helper('sariina_customerpoints');
        $store = $this->_getStore();

        $this->addColumn('id', array(
            'header' => $helper->__('#'),
            'index'  => 'id',
            'width'  => '20px',
            'filter_index' => 'main_table.id'
        ));

        $this->addColumn('created_at', array(
            'header'=> $helper->__('Date'),
            'index' => 'created_at',
            'width' => '100px',
            'type' => 'datetime',
            'gmtoffset' => true,
        ));

        $this->addColumn('customer_name', array(
            'header' => $helper->__('Customer'),
            'index'  => 'customer_name',
            'width'  => '300px',
            'filter_condition_callback' => array($this, '_customerNameFilter')
        ));
        
        $this->addColumn('points', array(
            'header' => $helper->__('Points'),
            'type' => 'number',
            'index' => 'points'
        ));

        $this->addColumn('total_points', array(
            'header' => $helper->__('Total points'),
            'type' => 'number',
            'index' => 'total_points'
        ));

        $this->addColumn('from', array(
            'header' => $helper->__('From'),
            'index' => 'from',
            'width' => '100px'
        ));

        $this->addColumn('description', array(
            'header' => $helper->__('Description'),
            'index' => 'description',
        ));
  
        return parent::_prepareColumns();
    }

    /**
     * Callback filter for customer name
     * @param  Sariina_CustomerPoints_Model_Resource_History $collection
     * @param  Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return object
     */
    protected function _customerNameFilter($collection, $column) {
        if ($value = $column->getFilter()->getValue()) {
            $this->getCollection()
            ->addFieldToFilter(new Zend_Db_Expr('CONCAT_WS(" ", ce1.value, ce2.value)'), ['like' => "%$value%"]);
        }
        return $this;
    }

    public function getRowUrl($row) {
        return false;
    }

    public function getBackUrl() {
        return $this->getUrl('*/*/history');
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Retunrs grid URL
     * Related to `gridAction()` of `CustomerpointsController`
     * @return string current grid url
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}