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
class Sariina_CustomerPoints_Adminhtml_Sariina_CustomerpointsController extends Mage_Adminhtml_Controller_Action
{
    public function historyAction() {
        $this->_title($this->__('History of Points'));
        $this->loadLayout();
        $this->_setActiveMenu('sariina_customerpoints/history');
        $this->_addContent($this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_history'));
        $this->renderLayout();
    }

    /**
     * Starting to output grid block
     * This block extends `Mage_Adminhtml_Block_Widget_Grid`
     * @return void
     */
    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_history_grid')->toHtml()
        );
    }

    /*********************
    *    L E A G U E S   *
    **********************/    
    public function leaguesAction() {
        $this->_title($this->__('Leagues history'))->_title($this->__('Leagues'))->_title($this->__('Leagues history'));
        $this->loadLayout();
        $this->_setActiveMenu('sariina_customerpoints/leagues');
        $this->_addContent($this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_leagues'));
        $this->renderLayout();
    }

    public function leaguesWinnersAction() {
        $this->_title($this->__('Leagues winners'))->_title($this->__('Leagues'))->_title($this->__('Leagues winners'));
        $this->loadLayout();
        $this->_setActiveMenu('sariina_customerpoints/leagues');
        $this->_addContent($this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_leagues_winners'));
        $this->renderLayout();
    }

    public function leagueTotalpointsAction() {
        $this->_title($this->__("Total points of customers in league"))->_title($this->__('Leagues'))->_title($this->__('Total points of customers in current league'));
        $this->loadLayout();
        $this->_setActiveMenu('sariina_customerpoints/leagues_totalpoints');
        $this->_addContent($this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_leagues_totalpoints'));
        $this->renderLayout();
    }

    /**
     * Starting to output grid block
     * This block extends `Mage_Adminhtml_Block_Widget_Grid`
     * @return void
     */
    public function leaguesGridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_leagues_grid')->toHtml()
        );
    }

    /**
     * Starting to output grid block
     * This block extends `Mage_Adminhtml_Block_Widget_Grid`
     * @return void
     */
    public function leaguesWinnersGridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_leagues_winners_grid')->toHtml()
        );
    }

    /**
     * Starting to output grid block
     * This block extends `Mage_Adminhtml_Block_Widget_Grid`
     * @return void
     */
    public function leagueTotalpointsGridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_leagues_totalpoints_grid')->toHtml()
        );
    }

    public function leaguesTotalsellsAction() {
        $this->_title($this->__("Total sells by category"))->_title($this->__('Leagues'))->_title($this->__('Total income of leagues by category'));
        $this->loadLayout();
        $this->_setActiveMenu('sariina_customerpoints/leagues_totalsells');
        $this->_addContent($this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_leagues_totalsells'));
        $this->renderLayout();
    }

    /**
     * Starting to output grid block
     * This block extends `Mage_Adminhtml_Block_Widget_Grid`
     * @return void
     */
    public function leaguesTotalsellsGridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_leagues_totalsells_grid')->toHtml()
        );
    }


    /**********************
    * C A T E G O R I E S *
    **********************/ 
    public function categoriesTotalpointsAction() {
        $this->_title($this->__("Total points of customers in categories"))->_title($this->__('Categories'))->_title($this->__('Total points of customers in categories'));
        $this->loadLayout();
        $this->_setActiveMenu('sariina_customerpoints/categories_totalpoints');
        $this->_addContent($this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_categories_totalpoints'));
        $this->renderLayout();
    }

    /**
     * Export csv action
     * This is being fired through `addExportType()` in prvodied grid
     * @return void
     */
    public function categoriesExportCsvAction() {
        $shamsiDate = Mage::helper('shamsi/date')->toJalali(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');
        $fileName =  $shamsiDate . '-categories.csv';
        $content = $this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_categories_totalpoints_grid');
        $this->_prepareDownloadResponse($fileName, $content->getCsvFile());
    }

    /**
     * [description]
     * @return [type]
     */
    public function categoriesAction() {
        $this->loadLayout();
        $this->_setActiveMenu('sariina_customerpoints/history');
        $this->_title($this->__('Edit default amounts'));
        $this->_addBreadcrumb(Mage::helper('sariina_customerpoints')->__('Edit defaults'), Mage::helper('sariina_customerpoints')->__('Categories'));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_categories_edit'))
            ->_addLeft($this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_categories_edit_tabs'));
        $this->renderLayout();
    }

    public function saveCategoriesAction() {
        if ($this->getRequest()->getPost()) {
            $form = $this->getRequest()->getParams();
            foreach($form['data'] as $optionId => $values) {
                if (!is_int($optionId)) {
                    continue;
                }
                try {
                    $model = Mage::getModel('sariina_customerpoints/options');
                    $row = clone $model;
                    $row->load($optionId, 'option_id');
                    if ($row->getId()) {
                        $row->setPercent($values['percent']);
                        $row->setAmount($values['amount']);
                        $row->save();
                    } else {
                        $model->setPercent($values['percent']);
                        $model->setAmount($values['amount']);
                        $model->setOptionId($optionId);
                        $model->save();
                    }
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $this->_redirect('*/*/categories');
                    return;
                }
            }
            Mage::getSingleton('adminhtml/session')
                ->addSuccess(
                    Mage::helper('sariina_customerpoints')->__('Values were successfully saved.')
                );
        }
        $this->_redirect('*/*/categories');
        return;
    }

    /**
     * Starting to output grid block
     * This block extends `Mage_Adminhtml_Block_Widget_Grid`
     * @return void
     */
    public function categoriesTotalpointsGridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_categories_totalpoints_grid')->toHtml()
        );
    }

    /**
     * Export csv action
     * This is being fired through `addExportType()` in prvodied grid
     * @return void
     */
    public function winnersExportCsvAction() {
        $shamsiDate = Mage::helper('shamsi/date')->toJalali(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');
        $fileName =  $shamsiDate . '-winners.csv';
        $content = $this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_leagues_winners_grid');
        $this->_prepareDownloadResponse($fileName, $content->getCsvFile());
    }

    public function addAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $this->loadLayout();
        $this->_setActiveMenu('sariina_customerpoints/history');
        $this->_addBreadcrumb(Mage::helper('sariina_customerpoints')->__('Customers points'), Mage::helper('sariina_customerpoints')->__('History'));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_history_edit'))
            ->_addLeft($this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_history_edit_tabs'));
        $this->renderLayout();
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $historyModel = Mage::getModel('sariina_customerpoints/history');
            $optionId = $this->getRequest()->getParam('option_id');
            // default values is 0
            if ($optionId == 0) {
                $optionId = null;
            }
            $historyModel->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                $currentDatetime = Mage::getModel('core/date')->gmtDate();
                $historyModel->setCreatedAt($currentDatetime);
                $historyModel->setFrom(Mage::helper('sariina_customerpoints')->getFrom());
                $totalPoints = $historyModel->getHistoryTotalPoints($historyModel->getCustomerId());
                $historyModel->setTotalPoints($totalPoints + $historyModel->getPoints());
                $historyModel->save();
                // This adds a row to pointables table
                Mage::helper('sariina_customerpoints')->convertToPointables($historyModel, $optionId);
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(
                        Mage::helper('sariina_customerpoints')->__('Item was successfully saved')
                    );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                $this->_redirect('*/*/history');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/add');
                return;
            }
        }

        Mage::getSingleton('adminhtml/session')
            ->addError(
                Mage::helper('sariina_customerpoints')
                    ->__('Unable to find item to save')
            );

        $this->_redirect('*/*/history');
    }

    /**
     * Export csv action
     * This is being fired through `addExportType()` in prvodied grid
     * @return void
     */
    public function exportCsvAction() {
        $shamsiDate = Mage::helper('shamsi/date')->toJalali(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');
        $fileName =  $shamsiDate . '-league.csv';
        $content = $this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_leagues_totalpoints_grid');
        $this->_prepareDownloadResponse($fileName, $content->getCsvFile());
    }

    /**
     * Export csv action
     * This is being fired through `addExportType()` in prvodied grid
     * @return void
     */
    public function sellsExportCsvAction() {
        $shamsiDate = Mage::helper('shamsi/date')->toJalali(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');
        $fileName =  $shamsiDate . '-income.csv';
        $content = $this->getLayout()->createBlock('sariina_customerpoints/adminhtml_customerpoints_leagues_totalsells_grid');
        $this->_prepareDownloadResponse($fileName, $content->getCsvFile());
    }
}