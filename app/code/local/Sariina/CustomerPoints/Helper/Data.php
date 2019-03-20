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
class Sariina_CustomerPoints_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PERCENTS = [35, 25, 12, 7, 6, 5, 4, 3, 2, 1];
    const FACTOR = 10000;
    /**
     * Prepares customers information to be used in html <select>
     * @return array
     */
    public function getCustomersArray() {
        $values = Mage::getSingleton('admin/session')->getFastCustomers();
        if (!$values) {
            $customers = Mage::getResourceModel('customer/customer_collection')
                ->addNameToSelect();
            $values = [];
            foreach ($customers as $customer) {
                $values[] = [
                    'value' => $customer->getId(),
                    'label' => $customer->getId().' - '.$customer->getName(),
                ];
            }
            Mage::getSingleton('admin/session')->setFastCustomers($values);
        }
        return $values;
    }

    /**
     * Gets current user name of whom is giving points
     * @return string
     */
    public function getFrom() {
        return Mage::getSingleton('admin/session')->getUser()->getUsername();
    }


    public function convertToPointables($historyModel, $optionId = null) {
        $normalType = true;

        if ($this->_isLeagueActive()) {
            $normalType = false;
            $this->_toPointables(
                $historyModel,
                $leagueType = 'League',
                $this->_getCurrentActiveLeagueId(),
                $optionId 
            );
        }

        // We are using a flag as we may want to have different types of leagues running
        if ($normalType === true) {
            $this->_toPointables($historyModel, $leagueType = 'Normal', $leagueId = 0, $optionId = null);
        }
    }

    /**
     * Adds a row to pointables
     * @param  object $historyModel
     * @return bool
     */
    protected function _toPointables($historyModel, $leagueType, $leagueId, $optionId = null) {
        $previousTotalPoints = $this->_claculatePreviousTotalPoints($historyModel, $leagueType, $leagueId);
        $currentPoints = $historyModel->getPoints();
        // Initializing pointables
        $pointableModel = Mage::getModel('sariina_customerpoints/pointables');
        $pointableModel->setPointId($historyModel->getId());
        $pointableModel->setTotalPoints($currentPoints + $previousTotalPoints);
        $pointableModel->setPointableId($leagueId);
        $pointableModel->setPointableType($leagueType);
        if (is_numeric($optionId)) {
            $pointableModel->setOptionId($optionId);
        } else {
            // by default, option_id is set to null
        }
        $pointableModel->save();
        return $pointableModel;
    }

    protected function _claculatePreviousTotalPoints($historyModel, $type, $id) {
        $customerId = $historyModel->getCustomerId();
        $pointableModel = Mage::getModel('sariina_customerpoints/pointables');
        $totalPoints = $pointableModel->getTotalPoints($customerId, $type, $id);
        return $totalPoints;
    }

    protected function _isLeagueActive() {
        return Mage::getSingleton('sariina_customerpoints/leagues')->isLeagueActive();
    }

    protected function _getCurrentActiveLeagueId() {
        return Mage::getSingleton('sariina_customerpoints/leagues')->getCurrentActiveLeagueId();
    }

    public function getIncomeOfLeague($leagueId, $categoryId) {
        $view = Mage::getModel('sariina_customerpoints/view')->getCollection();
        $view->addFieldToFilter('league_id', $leagueId)->addFieldToFilter('option_id', $categoryId);
        $view->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(['amount' => 'sum(final_price)']);
        return (int) $view->getFirstItem()->getAmount();
    }

    public function getCustomerPrizeAmount($leagueId, $categoryId, $percentage) {
        $option = Mage::getModel('sariina_customerpoints/options')
            ->getCollection()
            ->addFieldToFilter('option_id', $categoryId)
            ->getFirstItem();

        if (!$option->getId()) {
            return false;
        }

        $totalIncome = $this->getIncomeOfLeague($leagueId, $categoryId);
        if ($totalIncome <= 0) {
            return false;
        }

        $dedicatedPrice = ($option->getPercent() / 100) * $totalIncome;
        $calculateBy = $dedicatedPrice;
        // Use default amount if $dedicatedPrice is not enough
        if ($dedicatedPrice <= $option->getAmount()) {
            $calculateBy = $option->getAmount();
        }

        return (int) (($percentage / 100) * $calculateBy);
    }

    public function getTopTen($leagueId, $optionId) {
        $collection = Mage::getModel('sariina_customerpoints/view')
            ->getCollection()
            ->addFieldToFilter('option_id', $optionId)
            ->addFieldToFilter('league_id', $leagueId);
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('customer_id')
            ->group('customer_id')
            ->order('sum(points) desc')
            ->limit(10);
        return $collection;
    }
}