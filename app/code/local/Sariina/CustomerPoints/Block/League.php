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
class Sariina_CustomerPoints_Block_League extends Mage_Core_Block_Template
{
    protected $_leagues = null;
    public $customer = null;

    public function __construct() {
        parent::__construct();
        $this->_leagues = Mage::getModel('sariina_customerpoints/leagues');
        $this->customer = Mage::getSingleton('customer/session')->getCustomer();
    }

    public function getAllCustomersInLeague() {
        $collection = Mage::getModel('sariina_customerpoints/view')->getCollection();
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->where('league_id = ?', [$this->_leagues->getCurrentActiveLeagueId()])
            ->columns([
                'city' => new Zend_Db_Expr('(select c.value from customer_address_entity_varchar c where c.attribute_id = 26 and c.entity_id = (select ca.entity_id from customer_address_entity ca where ca.parent_id = main_table.customer_id order by ca.entity_id desc limit 1))'),
                'total_points' => 'sum(points)',
                'customer_id',
                'customer_name',
                'created_at' => 'max(created_at)'
            ])
            ->group(['customer_id', 'customer_name'])
            ->order('sum(points) desc');
        return $collection;
    }

    public function getTopEleven() {
        $collection = $this->getAllCustomersInLeague();
        $collection->getSelect()->limit(11);
        return $collection;
    }

    /**
     * Return nickname else customer id
     * @var $customerId int
     * @return string
     */
    public function getDisplayName($customerId) {
        if (is_numeric($customerId)) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            if ($customer->getNickname()) {
                return $customer->getNickname();
            }
        }
        return $customerId;
    }

    public function getCurrentUserPoints() {
        $pointablesModel = Mage::getModel('sariina_customerpoints/pointables');
        return 1 * $pointablesModel->getTotalPoints(
            $this->customer->getId(),
            'League',
            $this->_leagues->getCurrentActiveLeagueId()
        );
    }

    public function getFinishDate() {
        $collection = $this->_leagues->getCollection();
        $collection->getSelect()->order('id desc');
        $collection->addFieldToFilter('is_active', '1');
        return $collection->getFirstItem()->getFinishDate();
    }

    public function getCurrentUserCity() {
        $customerAddressId = $this->customer->getDefaultBilling();
        if ($customerAddressId) {
            $address = Mage::getModel('customer/address')->load($customerAddressId);
            return $address->getCity();
        }
        return '';
    }

    public function getCurrentUserRank($dummyId = 0) {
        return $this->getUserRank($this->customer->getId());
    }

    public function getUserRank($customerId) {
        $collection = $this->getAllCustomersInLeague();
        $queryString = $collection->getSelect()
            // ->reset(Zend_Db_Select::LIMIT_COUNT)
            ->__toString();

        // This may be better than iterating over collection
        $query = Mage::getSingleton('core/resource')
            ->getConnection('read')
            ->query($queryString);

        // I could do this in SQL but thought it would be better
        // to split the job with PHP
        // We may even find a better way later
        $isInLeague = false;
        foreach ($query->getIterator() as $number => $row) {
            if(isset($row['customer_id'])) {
                $rank = $number + 1;
                if ($row['customer_id'] == $customerId) {
                    $isInLeague = true;
                    break;
                }
            }
        }

        // Currently it returns `∞` if current customer didn't involved in leage
        // But we could return $rank + 1 instead which shows a number that is
        // one unit higher that the number of all customers in leage
        return $isInLeague ? $rank : '∞'; // $rank + 1;
    }

    public function getCustomerTotalPrize($customer = null, $format = true) {
        if (is_null($customer)) {
            $customer = $this->customer;
        }
        $options = Mage::getModel('sariina_customerpoints/options')->getOptions();
        $amount = 0;
        foreach ($options as $optionId => $optionText) {
            $rankNumber = $this->getUserRankInSubLeague($customer->getId(), $optionId);
            if (is_numeric($rankNumber) && $rankNumber <= 10) {
                $percentage = Sariina_CustomerPoints_Helper_Data::PERCENTS[$rankNumber - 1];
                $amount += $this->getUserPrizeAmountInSubLeague($optionId, $percentage, $format = false);
            }
        }
        if ($format === false) {
            return (int) $amount;
        }
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        return $this->toFarsi(Mage::app()->getLocale()
            ->currency($currencyCode)->toCurrency((int) $amount));
    }

    public function getPointsTable($optionId) {
        $customers = $this->getTopElevenInSubLeague($optionId);
        if ($customers->count() > 0) {
            $table = <<< HTML
<table class="league-table">
    <thead>
        <tr>
            <th width="11%">رتبه</th>
            <th width="30%">شناسه مشتری</th>
            <th width="16%">شهر</th>
            <th width="13%">امتیاز</th>
            <th width="30%">جایزه</th>
        </tr>
    </thead>
<tbody>
HTML;
            foreach ($customers as $key => $customer) {
                // Fetch customer data
                $fetchPoints = $this->toFarsi(
                    $this->getCurrentUserPointsInSubLeague($customer->getCustomerId(), $optionId)
                );
                $fetchNickname = $this->toFarsi($this->getDisplayName($customer->getCustomerId()));
                $fetchCity = $customer->getCity();
                // Default values
                $showRank = $key + 1;
                $percentage = Sariina_CustomerPoints_Helper_Data::PERCENTS[$showRank - 1];
                $showPrize = $this->getUserPrizeAmountInSubLeague($optionId, $percentage);
                $showClass = '';
                $showCity = $showNickName = $showPoints = '---';
                // If in top eleven
                if ($showRank < 11) {
                    $showNickName = $fetchNickname;
                    $showCity = $fetchCity;
                }
                // If it is the 11th customer
                else if ($showRank == 11) {
                    $showPoints = $fetchPoints;
                }
                // If it is current user show all
                if ($this->customer->getId() == $customer->getCustomerId()) {
                    $showNickName = $fetchNickname;
                    $showCity = $fetchCity;
                    $showPoints = $fetchPoints;
                    $showClass = ' class="youarehere"';
                }

                if (! (bool) $customer->getSpecialCustomer()) {
                    $table .= <<< HTML
<tr{$showClass}>
    <td>{$showRank}</td>
    <td>{$showNickName}</td>
    <td>{$showCity}</td>
    <td>{$showPoints}</td>
    <td>{$showPrize}</td>
</tr>
HTML;
                }
            }
            $table .= <<< HTML
            </tbody>
        </table>
HTML;
        }
        $table .= $this->_currentUserLeagueInfoBlock($customers, $optionId);
        return $table;
    }

    public function isCustomerInCollection($collection, $customer) {
        $customerId = $customer;
        if (is_object($customer)) {
            $customerId = $customer->getId();
        }

        $customerIds = array_column($collection->toArray()['items'], 'customer_id');
        return in_array($customerId, $customerIds);
    }

    protected function _currentUserLeagueInfoBlock($collection, $optionId) {
        // If customer isn't in top list show table at bottom
        if (!$this->isCustomerInCollection($collection, $this->customer)) { 
            $html = <<< HTML
<div class="league-table-gap">
    <p>.</p>
    <p>.</p>
    <p>.</p>
</div>
<table class="league-table">
    <tbody>
        <tr class="youarehere">
            <td width="11%">{$this->toFarsi($this->getUserRankInSubLeague($this->customer->getId(), $optionId))}</td>
            <td width="30%">{$this->toFarsi($this->getDisplayName($this->customer->getId()))}</td>
            <td width="13%">{$this->getCurrentUserCity()}</td>
            <td width="16%">{$this->toFarsi($this->getCurrentUserPointsInSubLeague($this->customer->getId(), $optionId))}</td>
            <td width="30%">---</td>
        </tr>
    </tbody>
</table>
HTML;
        }
        return $html;
    }

    public function getTopElevenInSubLeague($optionId) {
        $collection = $this->getAllCustomersInSubLeague($optionId);
        $collection->getSelect()->limit(11);
        return $collection;
    }

    public function getAllCustomersInSubLeague($optionId) {
        $collection = $this->getAllCustomersInLeague();
        $collection->addFieldToFilter('option_id', $optionId);
        return $collection;
    }

    public function getUserPrizeAmountInSubLeague($optionId, $percentage, $format = true) {
        $helper = Mage::helper('sariina_customerpoints');
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $customerPrize = $helper->getCustomerPrizeAmount(
            $this->_leagues->getCurrentActiveLeagueId(),
            $optionId,
            $percentage
        );
        if ($format === false) {
            return (int) $customerPrize;
        }
        return $this->toFarsi(Mage::app()->getLocale()
            ->currency($currencyCode)->toCurrency((int) $customerPrize));
    }

    public function getUserRankInSubLeague($customerId, $optionId) {
        $collection = $this->getAllCustomersInSubLeague($optionId);
        $queryString = $collection->getSelect()
            // ->reset(Zend_Db_Select::LIMIT_COUNT)
            ->__toString();

        // This may be better than iterating over collection
        $query = Mage::getSingleton('core/resource')
            ->getConnection('read')
            ->query($queryString);

        // I could do this in SQL but thought it would be better
        // to split the job with PHP
        // We may even find a better way later
        $isInLeague = false;
        foreach ($query->getIterator() as $number => $row) {
            if(isset($row['customer_id'])) {
                $rank = $number + 1;
                if ($row['customer_id'] == $customerId) {
                    $isInLeague = true;
                    break;
                }
            }
        }

        // Currently it returns `∞` if current customer didn't involved in leage
        // But we could return $rank + 1 instead which shows a number that is
        // one unit higher that the number of all customers in leage
        return $isInLeague ? $rank : '∞'; // $rank + 1;
    }

    public function getCurrentUserPointsInSubLeague($customerId, $optionId) {
        $pointablesModel = Mage::getModel('sariina_customerpoints/pointables');
        return 1 * $pointablesModel->getTotalPointsInSubleague(
            $customerId,
            $optionId,
            $this->_leagues->getCurrentActiveLeagueId()
        );
    }

    public function toFarsi($str) {
        return str_replace(range(0, 9), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], $str);
    }
}