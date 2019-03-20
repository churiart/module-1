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
class Sariina_CustomerPoints_Block_Adminhtml_Customerpoints_History_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('history_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('sariina_customerpoints')->__('Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('sariina_customerpoints')->__('Information'),
          'title'     => Mage::helper('sariina_customerpoints')->__('Information'),
          'content'   => $this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_history_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
