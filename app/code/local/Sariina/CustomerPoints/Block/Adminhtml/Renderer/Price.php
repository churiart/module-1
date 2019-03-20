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

class Sariina_CustomerPoints_Block_Adminhtml_Renderer_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Price
{
    public function render(Varien_Object $row) {
        $currencyCode = $this->_getCurrencyCode($row);
        return Mage::app()->getLocale()
            ->currency($currencyCode)->toCurrency($row->getAmount());
    }
}