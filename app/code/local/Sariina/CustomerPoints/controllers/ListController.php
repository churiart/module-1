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
class Sariina_CustomerPoints_ListController extends Mage_Core_Controller_Front_Action {
    /**
     * Index method that corresponds to URL
     * @return void
     */
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}