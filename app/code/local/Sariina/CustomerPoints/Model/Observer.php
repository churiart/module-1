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
class Sariina_CustomerPoints_Model_Observer
{
    protected $_isProcessed = false;
    
    /**
     * Adds to customer's points after order being saved
     * Triggers on `sales_order_save_after`
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function assignPoints(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        // We changed from on shipment
        // to when it is processing
        if ($this->_isProcessed === false) {
            if ($order->getPointsProcessed() == '0') {
                if ($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING ||
                    // This is when admin manually changes status
                    ($order->getState() == Mage_Sales_Model_Order::STATE_NEW && $order->getStatus() == 'pending')) {
                    $this->_iterateOverItemsAndAdd($order);
                    $order->setPointsProcessed(1);
                    $this->_isProcessed = true;
                    $order->save();
                }
            } else if ($order->getPointsProcessed() == '1') {
                if ($order->getState() == Mage_Sales_Model_Order::STATE_CANCELED) {
                    $this->_iterateOverItemsAndSubtract($order);
                    $order->setPointsProcessed(0);
                    $this->_isProcessed = true;
                    $order->save();
                }
            }
        }
        return $this;
    }

    protected function _calculatePoints($order) {
        $items = $order->getAllItems();
        $arrayOfPoints = [];
        foreach ($items as $item) {
            $productRating = str_replace('/', '.', $item->getProduct()->getAttributeText('product_rating'));
            $finalPrice = $item->getOriginalPrice() * $item->getQtyOrdered();
            $itemPoints = ( $productRating * $finalPrice ) / Sariina_CustomerPoints_Helper_Data::FACTOR;
            if ($itemPoints <> 0) {
                $arrayOfPoints[] = [
                    'value' => $itemPoints,
                    'name' => $item->getName(),
                    'price' => $finalPrice,
                    'option_id' => $item->getProduct()->getCategoryAttribute(),
                    'item' => $item
                ];
            }
        }
        return $arrayOfPoints;
    }

    protected function _iterateOverItemsAndAdd($order) {
        $this->_iterateOverItems($order, +1);
    }

    protected function _iterateOverItemsAndSubtract($order) {
        $this->_iterateOverItems($order, -1);
    }

    private function _iterateOverItems($order, $operator) {
        $itemsPoints = $this->_calculatePoints($order);
        foreach ($itemsPoints as $itemPoints) {
            $historyModel = $this->_savePoints($order, $itemPoints, $operator);
            Mage::helper('sariina_customerpoints')
                ->convertToPointables($historyModel, $optionId = $itemPoints['option_id']);
        }
    }

    protected function _savePoints($order, $itemPoints, $operator = 1) {
        $historyModel = Mage::getModel('sariina_customerpoints/history');

        $totalPoints = $historyModel->getHistoryTotalPoints($order->getCustomerId());
        $currentPoints = $operator * $itemPoints['value'];
        $currentDate = new Zend_Date(Mage::getModel('core/date')->gmtDate(time()));
        $description = $this->_getDescription($currentPoints, $order->getIncrementId(), $itemPoints['name']);
        
        $historyModel->setCreatedAt($currentDate);
        $historyModel->setFrom('Automatic');
        $historyModel->setCustomerId($order->getCustomerId());
        $historyModel->setDescription($description);
        $historyModel->setPoints($currentPoints);
        $historyModel->setItemId($itemPoints['item']->getItemId());
        $historyModel->setFinalPrice($itemPoints['price']);
        $historyModel->setTotalPoints($totalPoints + $currentPoints);
        $historyModel->save();
        return $historyModel;
    }

    protected function _getDescription($points, $orderId, $itemName)  {
        $keyword = 'افزایش';
        if ($points < 0) {
            $keyword = 'کسر';
        } 
        $description = "{$keyword} امتیاز بر روی سفارش {$orderId} و آیتم {$itemName}";
        return $description;
    }
}