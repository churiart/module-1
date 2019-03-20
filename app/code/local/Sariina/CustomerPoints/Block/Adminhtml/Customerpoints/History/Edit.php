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
class Sariina_CustomerPoints_Block_Adminhtml_Customerpoints_History_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct() {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'sariina_customerpoints';
        $this->_controller = 'adminhtml_customerpoints_history';
        $this->_updateButton('save', 'label', Mage::helper('sariina_customerpoints')->__('Save Points'));
    }

    public function getHeaderText() {
        if (Mage::registry('customerpoints_history_data') && Mage::registry('customerpoints_history_data')->getId()) {
            return Mage::helper('sariina_customerpoints')->__("Edit '%s'", $this->htmlEscape('History #'.Mage::registry('customerpoints_history_data')->getId()));
        } else {
            return Mage::helper('sariina_customerpoints')->__('Add Points');
        }
    }

    public function getBackUrl() {
        return $this->getUrl('*/*/history');
    }
}