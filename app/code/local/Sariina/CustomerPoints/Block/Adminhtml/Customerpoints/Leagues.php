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
class Sariina_CustomerPoints_Block_Adminhtml_Customerpoints_Leagues extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct() {
        $this->_controller = 'adminhtml_customerpoints_leagues';
        $this->_blockGroup = 'sariina_customerpoints';
        $this->_headerText = Mage::helper('sariina_customerpoints')->__('Leagues history');
        parent::__construct();
        $this->_removeButton('add');
    }
}