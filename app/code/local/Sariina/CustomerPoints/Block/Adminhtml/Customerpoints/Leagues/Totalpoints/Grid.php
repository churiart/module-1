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
class Sariina_CustomerPoints_Block_Adminhtml_Customerpoints_Leagues_Totalpoints_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('sariina_customerpoints_leagues_totalpoints_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection() {
        $firstname = Mage::getModel('eav/entity_attribute')->loadByCode('1', 'firstname');
        $lastname = Mage::getModel('eav/entity_attribute')->loadByCode('1', 'lastname');
        $collection = Mage::getModel("sariina_customerpoints/leagues")->getCollection();
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->useStraightJoin(true)
            ->join(
                ['p' => $collection->getTable('sariina_customerpoints/pointables')],
                'main_table.league_id = p.pointable_id and p.pointable_type = "League"',
                ['p.total_points']
            )
            ->join(
                ['h' => $collection->getTable('sariina_customerpoints/history')],
                'p.point_id = h.id',
                ['h.customer_id', 'h.created_at']
            )
            ->join(
                ['ce1' => 'customer_entity_varchar'],
                'ce1.entity_id = h.customer_id',
                ['firstname' => 'value']
            )
            ->join(
                ['ce2' => 'customer_entity_varchar'],
                'ce2.entity_id = h.customer_id',
                ['lastname' => 'value']
            )
            ->where('ce1.attribute_id = ' . $firstname->getAttributeId()) 
            ->where('ce2.attribute_id = ' . $lastname->getAttributeId())
            ->where('main_table.is_active = 1')
            ->columns(['customer_name' => 'CONCAT_WS(" ", ce1.value, ce2.value)', 'date_name']);
        $collection->addFieldToFilter('h.id', array('eq' => new Zend_Db_Expr('(select h2.id from sariina_customerpoints_history h2 where h.customer_id = h2.customer_id order by h2.id desc limit 1)')));
        $this->setCollection($collection);
        $this->addExportType('*/*/exportCsv', Mage::helper('sariina_customerpoints')->__('CSV'));
        parent::_prepareCollection();
        return $this;
    }
 
    protected function _prepareColumns() {
        $helper = Mage::helper('sariina_customerpoints');
        $store = $this->_getStore();

        $this->addColumn('customer_id', array(
            'header' => $helper->__('Customer ID'),
            'index'  => 'customer_id',
            'width'  => '20px'
        ));

        $this->addColumn('date_name', array(
            'header' => $helper->__('League name'),
            'index'  => 'date_name',
            'width'  => '30px',
            'filter' => false
        ));

        $this->addColumn('created_at', array(
            'header'=> $helper->__('Latest update'),
            'index' => 'created_at',
            'width' => '100px',
            'type' => 'datetime',
            'filter_index' => 'h.created_at',
            'gmtoffset' => true,
        ));

        $this->addColumn('customer_name', array(
            'header' => $helper->__('Customer'),
            'index'  => 'customer_name',
            'width'  => '300px',
            'filter_condition_callback' => array($this, '_customerNameFilter')
        ));
        
        $this->addColumn('total_points', array(
            'header' => $helper->__('Total points'),
            'type' => 'number',
            'index' => 'total_points',
            'filter_index' => 'p.total_points',
            'default' => '0'
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
        return $this->getUrl('*/*/leaguetotalpoints');
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Retunrs grid URL
     * Related to `leagueTotalpointsGridAction()` of `CustomerpointsController`
     * @return string current grid url
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/leaguetotalpointsgrid', array('_current' => true));
    }
}