<?php

class Hhennes_DataFlow_Model_Convert_Parser_CmsPage extends Hhennes_DataFlow_Model_Convert_Parser_Abstract {

    protected $_model = 'cms/page';
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

        $model = Mage::getModel('cms/page');
        $model->setData($importData);

        if ($importData['store_id'] != '') {
            $model->setStores(explode(',', $importData['store_id']));
        } else {
            $model->setStores(array(1));
        }

        //Gestion de la mise à jour
        $modelsExists = Mage::getModel('cms/page')->load($importData['identifier'], 'identifier');
        if ($modelsExists->getId()) {
            $model->setData('page_id', $modelsExists->getId());
        } else {
            $model->setData('page_id', NULL);
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

?>
