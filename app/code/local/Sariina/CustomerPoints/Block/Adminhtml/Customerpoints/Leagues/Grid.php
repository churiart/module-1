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
class Sariina_CustomerPoints_Block_Adminhtml_Customerpoints_Leagues_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('sariina_customerpoints_leagues_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection() {
        $firstname = Mage::getModel('eav/entity_attribute')->loadByCode('1', 'firstname');
        $lastname = Mage::getModel('eav/entity_attribute')->loadByCode('1', 'lastname');
        $collection = Mage::getModel("sariina_customerpoints/pointables")->getCollection();
        $collection->getSelect()
            ->useStraightJoin(true)
            ->join(
                ['l' => $collection->getTable('sariina_customerpoints/leagues')],
                'main_table.pointable_id = l.league_id',
                ['l.league_id']
            )
            ->join(
                ['h' => $collection->getTable('sariina_customerpoints/history')],
                'main_table.point_id = h.id',
                ['h.customer_id', 'h.points', 'h.created_at', 'h.from', 'h.description']
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
            ->where('main_table.pointable_type = "League"')
            ->where('ce1.attribute_id = ' . $firstname->getAttributeId()) 
            ->where('ce2.attribute_id = ' . $lastname->getAttributeId())
            ->columns([
                'customer_name' => 'CONCAT_WS(" ", ce1.value, ce2.value)',
                'option_id'
            ]);

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

        $this->addColumn('league_id', array(
            'header' => $helper->__('League name'),
            'index'  => 'league_id',
            'type' => 'options',
            'width'  => '50px',
            'options' => Mage::getModel('sariina_customerpoints/leagues')->getLeagues(),
        ));

        $this->addColumn('option_id', array(
            'header' => $helper->__('Category name'),
            'index'  => 'option_id',
            'type' => 'options',
            'width'  => '50px',
            'options' => Mage::getModel('sariina_customerpoints/options')->getOptions(),
        ));

        $this->addColumn('created_at', array(
            'header'=> $helper->__('Date'),
            'index' => 'created_at',
            'width' => '100px',
            'type' => 'datetime',
            'gmtoffset' => true,
            'filter_index' => 'h.created_at'
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
            'index' => 'total_points',
            'filter_index' => 'main_table.total_points'
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
        return $this->getUrl('*/*/leagues');
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Retunrs grid URL
     * Related to `leaguesGridAction()` of `CustomerpointsController`
     * @return string current grid url
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/leaguesgrid', array('_current' => true));
    }
}