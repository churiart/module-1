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
class Sariina_CustomerPoints_Block_Adminhtml_Customerpoints_History_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout() {
        parent::_prepareLayout();
    }
    
    protected function _prepareForm() {
        $helper = Mage::helper('sariina_customerpoints');
        $form = new Varien_Data_Form();
        $this->setForm($form);
      
        $fieldset = $form->addFieldset('history_form', array('legend' => $helper->__('Points')));

        $fieldset->addField('customer_id', 'select', array(
            'label'     => $helper->__('Customer'),
            'name'      => 'customer_id',
            'values'    => $helper->getCustomersArray(),
            'required'  => true,
        ));

        $fieldset->addField('option_id', 'select', array(
            'label'     => $helper->__('Category id'),
            'name'      => 'option_id',
            // First value is empty
            'values'    => [''] + Mage::getModel('sariina_customerpoints/options')->getOptions(),
            'note'  => $helper->__('You could select a category for adding or subtracting points from. This relates to current active league.'),
            'required'  => false,
        ));

        $fieldset->addField('points', 'text', array(
            'label' => $helper->__('Points'),
            'name' => 'points',
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'label' => $helper->__('Description'),
            'name' => 'description',
            'require' => false,
        ));

        if (Mage::getSingleton('adminhtml/session')->getCustomerpointsHistoryData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCustomerpointsHistoryData());
            Mage::getSingleton('adminhtml/session')->setCustomerpointsHistoryData(null);
        } elseif (Mage::registry('customerpoints_history_data')) {
            $form->setValues(Mage::registry('customerpoints_history_data')->getData());
        }

        return parent::_prepareForm();
    }
}