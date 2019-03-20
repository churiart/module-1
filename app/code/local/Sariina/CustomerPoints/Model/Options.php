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
class Sariina_CustomerPoints_Model_Options extends Mage_Core_Model_Abstract
{
    protected function _construct() {
        $this->_init('sariina_customerpoints/options');
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $percent = $this->getPercent();
        if ($percent > 100 || $percent < 0 || !is_numeric($percent)) {
            Mage::throwException(Mage::helper('sariina_customerpoints')->__('Please fix the percents.'));
        }

        $amount = $this->getAmount();
        if (!is_numeric($amount)) {
            Mage::throwException(Mage::helper('sariina_customerpoints')->__('Amounts should be digits.'));
        }

        return $this;
    }

    /**
     * An array of option_id => option_text pairs
     * @return array
     */
    public function getOptions() {
        $options = [];
        foreach ($this->getCollection() as $option) {
            $options[$option->getOptionId()] = $this->getOptionText($option->getOptionId());
        }
        return $options;
    }

    /**
     * Get option label
     * @param  int
     * @return string
     */
    public function getOptionText($optionId) {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'category_attribute');
        $optionText = $attribute->getSource()->getOptionText($optionId);
        return $optionText;
    }

}