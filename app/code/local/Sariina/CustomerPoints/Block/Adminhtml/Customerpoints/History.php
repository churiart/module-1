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
class Sariina_CustomerPoints_Block_Adminhtml_Customerpoints_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct() {
        $this->_controller = 'adminhtml_customerpoints_history';
        $this->_blockGroup = 'sariina_customerpoints';
        $this->_headerText = Mage::helper('sariina_customerpoints')->__('History of Points');
        $this->_addButtonLabel = Mage::helper('sariina_customerpoints')->__('Add Point');
        parent::__construct();
    }

    public function getCreateUrl() {
        return $this->getUrl('*/*/add');
    }
}