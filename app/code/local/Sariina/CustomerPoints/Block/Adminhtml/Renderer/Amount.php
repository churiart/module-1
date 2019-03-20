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

class Sariina_CustomerPoints_Block_Adminhtml_Renderer_Amount extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Price
{
    public static $rowNumber = 0;

    public function render(Varien_Object $row) {
        if (!is_numeric($row->getOptionId())) {
            return '0';
        }

        $currencyCode = $this->_getCurrencyCode($row);
        $winner = Mage::getModel('sariina_customerpoints/winners')->getCollection()
            ->addFieldToFilter('league_id', $row->getLeagueId())
            ->addFieldToFilter('option_id', $row->getOptionId())
            ->addFieldToFilter('customer_id', $row->getCustomerId())
            ->getFirstItem();

        if ($winner->getId()) {
            return Mage::app()->getLocale()
                ->currency($currencyCode)->toCurrency($winner->getAmount());
        }

        $helper = Mage::helper('sariina_customerpoints');
        $leagueBlock = Mage::app()->getLayout()->getBlockSingleton('sariina_customerpoints/league');
        $rankNumber = $leagueBlock->getUserRankInSubLeague($row->getCustomerId(), $row->getOptionId());
        $percentage = $helper::PERCENTS[$rankNumber - 1];
        $customerPrize = $helper->getCustomerPrizeAmount(
                $row->getLeagueId(),
                $row->getOptionId(),
                $percentage
            );

        return Mage::app()->getLocale()
            ->currency($currencyCode)->toCurrency($customerPrize);
    }
}