<?php

class Sariina_CustomerPoints_Block_Adminhtml_Customerpoints_Leagues_Totalsells_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('sariina_customerpoints_leagues_totalsells_grid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection() {
        $collection = Mage::getModel("sariina_customerpoints/view")->getCollection();
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns([
                'amount' => 'sum(final_price)',
                'option_id',
                'league_id'
            ])
            ->where('option_id is not null')
            ->group(['option_id', 'league_id']);

        $this->setCollection($collection);
        $this->addExportType('*/*/sellsExportCsv', Mage::helper('sariina_customerpoints')->__('CSV'));
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->unsetChild('reset_filter_button');
    }
 
    protected function _prepareColumns() {
        $helper = Mage::helper('sariina_customerpoints');
        $store = $this->_getStore();

        $this->addColumn('league_id', array(
            'header' => $helper->__('League name'),
            'index'  => 'league_id',
            'type' => 'options',
            'width'  => '35px',
            'options' => Mage::getModel('sariina_customerpoints/leagues')->getLeagues(),
        ));

        $this->addColumn('option_id', array(
            'header' => $helper->__('Category name'),
            'index'  => 'option_id',
            'type' => 'options',
            'width'  => '50px',
            'options' => Mage::getModel('sariina_customerpoints/options')->getOptions(),
        ));

        $this->addColumn('amount', array(
            'header' => $helper->__('Total income'),
            'type' => 'number',
            'index' => 'amount',
            'filter' => false,
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
            'renderer' => 'sariina_customerpoints/adminhtml_renderer_price',
        ));
  
        return parent::_prepareColumns();
    }


    public function getRowUrl($row) {
        return false;
    }

    public function getBackUrl() {
        return $this->getUrl('*/*/leaguestotalsells');
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Retunrs grid URL
     * Related to `leaguesTotalsellsGridAction()` of `CustomerpointsController`
     * @return string current grid url
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/leaguestotalsellsgrid', array('_current' => true));
    }
}