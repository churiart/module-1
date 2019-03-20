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
class Sariina_CustomerPoints_Model_History extends Mage_Core_Model_Abstract
{
    protected function _construct() {
        $this->_init('sariina_customerpoints/history');
    }

    /**
     * Points for an individual item
     * @param  int $itemId
     * @return int
     */
    public function getItemPoints($itemId) {
        if (!is_numeric($itemId)) {
            return 0;
        }

        $item = $this->load($itemId, 'item_id');
        if ($item) {
            return 1 * $item->getPoints();
        }

        return 0;
    }

    /**
     * Fetches last value of `total_points` of customer
     * @param  string|int $customerId
     * @return string
     */
    public function getHistoryTotalPoints($customerId) {
        $historyModel = Mage::getModel('sariina_customerpoints/history')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId);
        $historyModel->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(['total' => 'coalesce(total_points, 0)'])
            ->order('id desc');

        return $historyModel->getFirstItem()->getData('total');
    }

    public function getTotalRows($customerId = null) {
        $resource = Mage::getSingleton('core/resource');
        $historyTableName = $resource->getTableName('sariina_customerpoints/history');
        $query = [];
        $conditions = [];
        $query[] = "select count(*) from {$historyTableName}";
        if ($customerId) {
            $query[] = 'where customer_id = :id';
            $conditions['id'] = $customerId;
        }
        $totalRows = $resource->getConnection('read')
            ->query(implode(" ", $query), $conditions)
        ->fetchColumn(0);
        return $totalRows;
    }
}