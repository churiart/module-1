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
class Sariina_CustomerPoints_Block_Adminhtml_Customerpoints_Leagues_Winners_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('sariina_customerpoints_leagues_winners_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection() {
        $collection = Mage::getModel("sariina_customerpoints/view")->getCollection();
        $leagues = Mage::getModel('sariina_customerpoints/leagues');
        $options = Mage::getModel('sariina_customerpoints/options')->getOptions();

        // Set report to be filtered by default to current league
        if (is_numeric($leagues->getCurrentActiveLeagueId())) {
            $this->setDefaultFilter([
                'league_id' => $leagues->getCurrentActiveLeagueId(),
                'option_id' => array_keys($options)[0]
            ]);
        }
        
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->reset(Zend_Db_Select::GROUP)
            ->columns([
                'amount' => '0',
                'total_points' => 'sum(points)',
                'option_id',
                'customer_name',
                'customer_id',
                'league_id',
                'date_name'])
            ->group([
                'customer_name',
                'customer_id',
                'option_id',
                'league_id',
                'date_name'
            ])
            ->order('total_points desc');

        $this->setCollection($collection);
        $this->addExportType('*/*/winnersExportCsv', Mage::helper('sariina_customerpoints')->__('CSV'));
        parent::_prepareCollection();
        return $this;
    }

    protected function _preparePage() {
        $this->getCollection()->setPageSize(10);
        $this->getCollection()->setCurPage(1);
        $this->getCollection()->getSelect()->order('sum(points) desc')->where('option_id is not null');
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->unsetChild('reset_filter_button');
    }
 
    protected function _prepareColumns() {
        $helper = Mage::helper('sariina_customerpoints');
        $store = $this->_getStore();

        $this->addColumn('customer_id', array(
            'header' => $helper->__('Customer ID'),
            'index'  => 'customer_id',
            'width'  => '20px',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('league_id', array(
            'header' => $helper->__('League name'),
            'index'  => 'league_id',
            'type' => 'options',
            'width'  => '75px',
            'sortable' => false,
            'options' => Mage::getModel('sariina_customerpoints/leagues')->getLeagues(),
        ));

        $this->addColumn('option_id', array(
            'header' => $helper->__('Category name'),
            'index'  => 'option_id',
            'type' => 'options',
            'width'  => '50px',
            'sortable' => false,
            'options' => Mage::getModel('sariina_customerpoints/options')->getOptions(),
        ));

        $this->addColumn('customer_name', array(
            'header' => $helper->__('Customer'),
            'index'  => 'customer_name',
            'width'  => '300px',
            'filter' => false,
            'sortable' => false,
            'filter_condition_callback' => array($this, '_customerNameFilter')
        ));
        
        $this->addColumn('total_points', array(
            'header' => $helper->__('Total points'),
            'type' => 'number',
            'index' => 'total_points',
            'filter' => false,
            'sortable' => false,
            'default' => '0'
        ));

        $this->addColumn('amount', array(
            'header' => $helper->__('Prize amount'),
            'type' => 'number',
            'index' => 'amount',
            'filter_index' => 'sum(points)',
            'filter' => false,
            'sortable' => false,
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
            'renderer' => 'sariina_customerpoints/adminhtml_renderer_amount',
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
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $this->getCollection()->getSelect()->where(
            "customer_name like '%{$value}%'"
        );

        return $this;
    }

    public function getRowUrl($row) {
        return false;
    }

    public function getBackUrl() {
        return $this->getUrl('*/*/leaguesWinners');
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Retunrs grid URL
     * Related to `leaguesWinnersGridAction()` of `CustomerpointsController`
     * @return string current grid url
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/leaguesWinnersGrid', array('_current' => true));
    }
}