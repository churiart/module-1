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
class Sariina_CustomerPoints_Model_Pointables extends Mage_Core_Model_Abstract
{
    protected function _construct() {
        $this->_init('sariina_customerpoints/pointables');
    }

    public function getTotalPoints($customerId, $pointableType, $pointableId = 0) {
        $resource = Mage::getSingleton('core/resource');
        $pointablesTableName = $resource->getTableName('sariina_customerpoints/pointables');
        $historyTableName = $resource->getTableName('sariina_customerpoints/history');
        $totalPoints = $resource->getConnection('read')
            ->query(
                "select coalesce(
                    (select coalesce(p.total_points, 0) as total
                        from `{$pointablesTableName}` p
                            straight_join `{$historyTableName}` h
                            on p.point_id = h.id
                            and h.customer_id = :customerId
                            where p.pointable_type = :type
                                and p.pointable_id = :id
                                and p.deleted_at is null
                        order by p.id desc limit 1)
                    , 0)", [
                'customerId' => $customerId,
                'type' => $pointableType,
                'id' => $pointableId
            ])
        ->fetchColumn(0);
        return $totalPoints;
    }

    public function getTotalPointsInSubleague($customerId, $optionId, $pointableId) {
        $resource = Mage::getSingleton('core/resource');
        $pointablesTableName = $resource->getTableName('sariina_customerpoints/pointables');
        $historyTableName = $resource->getTableName('sariina_customerpoints/history');
        $totalPoints = $resource->getConnection('read')
            ->query(
                "select coalesce(
                    (select coalesce(sum(h.points), 0) as total
                        from `{$pointablesTableName}` p
                            straight_join `{$historyTableName}` h
                            on p.point_id = h.id
                            and h.customer_id = :customerId
                            where p.pointable_type = 'League'
                                and p.pointable_id = :id
                                and option_id = :option_id
                                and p.deleted_at is null
                        order by p.id desc limit 1)
                    , 0)", [
                'customerId' => $customerId,
                'id' => $pointableId,
                'option_id' => $optionId
            ])
        ->fetchColumn(0);
        return $totalPoints;
    }
}