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
require_once Mage::getBaseDir('lib') . '/Sariina/shamsi/jdatetime.class.php';

class Sariina_CustomerPoints_Model_Cron
{
    protected $_resource = null;
    protected $_shamsiDate = null;

    public function __construct() {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_shamsiDate = Mage::helper('shamsi/date');
    }

    /**
     * Trigger a new league
     * @return void
     */
    public function startLeague() {
        if ($this->_isNewLeagueReady()) {
            $this->_zeroPoints();
            $this->_processWinners();
            $this->_addNextLeague();
        }
    }

    /**
      * Soft deletes customer points that are outside of a program
      * That means the type is `Normal`
      * @return void
      */ 
    protected function _zeroPoints() {
        $tablename = $this->_resource->getTableName('sariina_customerpoints/pointables');
        $this->_resource->getConnection('write')
            ->query("update `{$tablename}` set deleted_at = :datetime where pointable_type = :type and deleted_at is null", [
                'datetime' => $this->_getCurrentDateTime(),
                // `Normal` means outside of a program
                'type' => 'Normal'
            ]);
    }

    protected function _getCurrentDateTime() {
        return Mage::getModel('core/date')->date('Y-m-d H:i:s');
    }

    protected function _getCurrentGmtDateTime() {
        return Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');
    }

    protected function _getCurrentDate() {
        return Mage::getModel('core/date')->date('Y-m-d');
    }

    /**
     * Check if time is about to start a new league
     * @return boolean
     */
    protected function _isNewLeagueReady()  {
        // Only execute if we are within the first our of the first day of the month
        if ($this->_isFirstHourOfMonth()) {
            $tablename = $this->_resource->getTableName('sariina_customerpoints/leagues');
            // Retrieving offset
            // $getGmtOffsetInMinutes = (int) Mage::getModel('core/date')->getGmtOffset('minutes');
            $existsBefore = $this->_resource->getConnection('read')
                ->query("select 1 from `{$tablename}` where date(created_at) = '{$this->_getCurrentDate()}'")
                ->fetchColumn(0);
            if ((bool) $existsBefore === false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add a new row to corresponding table
     */
    protected function _addNextLeague() {
        $leagueId = 1;
        $newLeague = Mage::getModel('sariina_customerpoints/leagues');

        // Increase league id if exists
        if ((int) $this->_getLastLeagueId() > 0) {
            $leagueId += $this->_getLastLeagueId();
        }
        // We should disable previous active league
        $this->_deactivateActiveLeagues();
        // Trying to save new league
        $newLeague->setLeagueId($leagueId);
        $newLeague->setStartDate($this->_getCurrentDateTime());
        $newLeague->setDateName($this->_getGeneratedDateName());
        $newLeague->setFinishDate($this->_getFinishDate());
        $newLeague->setCreatedAt($this->_getCurrentDateTime());
        $newLeague->setIsActive(1);
        $newLeague->save();
    }

    /**
     * Calculate end of the month date
     * @return string
     */
    protected function _getFinishDate() {
        return $this->_shamsiDate->toGregorian(
            $this->_shamsiDate->toJalali($this->_getCurrentDateTime(), 'Y-m-t'),
            'Y-m-d'
        );
    }

    /**
     * Get last league id if exists
     * @return int
     */
    protected function _getLastLeagueId() {
        $collection = Mage::getModel('sariina_customerpoints/leagues')
            ->getCollection();
        $collection->getSelect()->order('id DESC')->limit(1);
        return $collection->getFirstItem()->getLeagueId();
    }

    /**
     * Check for the very first hour of the month
     * @return boolean
     */
    protected function _isFirstHourOfMonth() {
        // Jalali cares about GMT
        $currentTime = $this->_getCurrentGmtDateTime();
        $currentDayOfMonth = $this->_shamsiDate->toJalali($currentTime, 'd');
        // If it is the first day of month
        if ($currentDayOfMonth == '01') {
            $currentHour = $this->_shamsiDate->toJalali($currentTime, 'H');
            // If it is the first hour
            if ($currentHour == '00') {
                return true;
            }
        }
        return false;
    }

    /**
     * Deactivate all active leagues
     * @return void
     */
    protected function _deactivateActiveLeagues() {
        $tablename = $this->_resource->getTableName('sariina_customerpoints/leagues');
        $this->_resource->getConnection('write')
            ->query("update `{$tablename}` set is_active = 0 where is_active = 1");
    }

    /**
     * Build a string for current league name
     * format is in '\p{L}+ \d{4}'
     * @return string
     */
    protected function _getGeneratedDateName() {
        $jDate = new jDateTime(null, null, 'Asia/Tehran');
        list($year, $month, $day) = explode('-', $this->_getCurrentDate());
        // $ourDate[0] = $year
        // $ourDate[1] = $month
        // $ourDate[2] = $day
        $ourDate = $jDate->toJalali($year, $month, $day);
        $months = [
            'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
        ];
        $monthName = $months[$ourDate[1] - 1];
        // `month year`
        return $monthName . ' ' . $ourDate[0];
    }

    protected function _getDateNameOfExistingLeague($leagueId) {
        $leagues = Mage::getModel('sariina_customerpoints/leagues')->getCollection()
            ->addFieldToFilter('league_id', $leagueId);
        return $leagues->getFirstItem()->getDateName();
    }

    public function _processWinners() {
        $leagueId  = Mage::getModel('sariina_customerpoints/leagues')->getCurrentActiveLeagueId();
        // We should have an active league
        if (!is_numeric($leagueId)) {
            return null;
        }

        $optionsIds = array_keys(Mage::getModel('sariina_customerpoints/options')->getOptions());
        $leagueBlock = Mage::app()->getLayout()->getBlockSingleton('sariina_customerpoints/league');
        $helper = Mage::helper('sariina_customerpoints');
        if (count($optionsIds) > 0) {
            foreach ($optionsIds as $optionId) {
                $collection = $helper->getTopTen($leagueId, $optionId);
                $collection->load();
                $i = 0;
                foreach ($collection as $row) {
                    $rankNumber = $leagueBlock->getUserRankInSubLeague($row->getCustomerId(), $optionId);
                    if (!is_numeric($rankNumber)) {
                        continue;
                    }
                    $percentage = $helper::PERCENTS[$rankNumber - 1];
                    // Calculate each customer prize amount
                    $amount = $helper->getCustomerPrizeAmount(
                        $leagueId,
                        $optionId,
                        // $i++ plays an important role here
                        // This returns each customer prize amount by priority
                        // $percent = $helper::PERCENTS[$i++]
                        /* We changed above logic */
                        $percentage
                    );
                    // If amount is valid
                    if (is_numeric($amount)) {
                        // Add it both to winners and wallet history tables
                        $this->_processWinner([
                            'customer_id' => $row->getCustomerId(),
                            'league_id' => $leagueId,
                            'option_id' => $optionId,
                            'amount' => $amount,
                            'percent' => $percentage
                        ]);
                    }
                }
            }
        }
    }

    protected function _addToWallet($data) {
        $wallet = Mage::getModel('sariina_wallet/wallet_history');
        $optionText = Mage::getSingleton('sariina_customerpoints/options')
            ->getOptionText($data['option_id']);
        $wallet->setCustomerId($data['customer_id']);
        $wallet->setAmount($data['amount']);
        $wallet->setReason($wallet::REASON_LEAGUE_PRIZE);
        $wallet->setDescription("{$data['percent']} درصد مبلغ جایزه در لیگ {$this->_getDateNameOfExistingLeague($data['league_id'])} بر روی دسته بندی {$optionText}");
        $wallet->setWho('customer');
        return $wallet;
    }

    protected function _addToWinners($data) {
        $winner = Mage::getModel('sariina_customerpoints/winners');
        $winner->setOptionId($data['option_id']);
        $winner->setLeagueId($data['league_id']);
        $winner->setAmount($data['amount']);
        $winner->setCustomerId($data['customer_id']);
        return $winner;
    }

    protected function _processWinner(array $data) {
        $transaction = Mage::getModel('core/resource_transaction');
        $wallet = $this->_addToWallet($data);
        $winner = $this->_addToWinners($data);
        $transaction->addObject($winner);
        $transaction->addObject($wallet);
        try {
            $transaction->save();
        } catch (\Exception $e) {
            //
        }
    }
}