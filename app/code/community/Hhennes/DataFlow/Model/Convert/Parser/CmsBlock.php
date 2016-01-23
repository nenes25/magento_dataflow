<?php

class Hhennes_DataFlow_Model_Convert_Parser_CmsBlock extends Hhennes_DataFlow_Model_Convert_Parser_Abstract {

    protected $_model = 'cms/block';
    protected $_createdAtField = 'creation_time';
    protected $_updatedAtField = 'update_time';
    protected $_stores = true;

    /**
     * Import des données Blocks Cms
     */
    public function parse() {
        parent::parse();
        return $this;
    }

    public function saveRow($importData) {

        $model = Mage::getModel('cms/block');
        $model->setData($importData);
        $model->setStores(explode(',', $importData['store_id']));

        //Gestion de la mise à jour
        $modelsExists = Mage::getModel('cms/block')->load($importData['identifier'], 'identifier');
        if ($modelsExists->getId()) {
            $model->setData('block_id', $modelsExists->getId());
        } else {
            $model->setData('block_id', NULL);
        }

        try {
            $model->save();
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
    }

    /**
     * Export des données Blocks Cms
     */
    public function unparse() {

        parent::unparse();
    }

}
