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
class Sariina_CustomerPoints_Block_Customer_List extends Mage_Core_Block_Template 
{
    public function getCustomerAllPoints($customer) {
        if (!$customer instanceof Mage_Customer_Model_Customer) {
            return false;
        }
        $collection = Mage::getModel("sariina_customerpoints/history")
            ->getCollection()
            ->addFieldToFilter('customer_id', $customer->getId())
            ->setOrder('id', 'desc');
        return $collection;
    }
}