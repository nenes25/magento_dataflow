<?php
/**
 * Description of Abstract
 *
 * @author herve
 */
abstract class Hhennes_DataFlow_Model_Convert_Parser_Abstract extends Mage_Dataflow_Model_Convert_Parser_Abstract {
    
        
    /** Modèle géré dans l'import */
    protected $_model;
    
    /** Nom de la table */
    protected $_tableName = 'main_table';
    
    /** Champ Date de création */
    protected $_createdAtField;
    
    /** Champ Date de modification */
    protected $_updatedAtField;
    
    /** Modèle dépend des stores */
    protected $_stores = false;

    public function parse() {

        $batchModel = Mage::getSingleton('dataflow/batch');

        $batchImportModel = $batchModel->getBatchImportModel();
        $importIds = $batchImportModel->getIdCollection();

        foreach ($importIds as $importId) {
            $batchImportModel->load($importId);
            $importData = $batchImportModel->getBatchData();
            $this->saveRow($importData);
        }
    }

    /**
     * Sauvegarde de la ligne
     * @param array $importData
     */
    public function saveRow($importData) {

        $model = Mage::getModel($this->_model);
        $model->setData($importData);
        //$model->setId(); //@ToDO Permettre via un flag de définir si on ajoute ou on update

        try {
            $model->save();
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        if ($model->getCreatedAt() && array_key_exists('created_at', $importData)) {
            try {
                $model->setCreatedAt($importData['created_at']);
                $model->save();
            } catch (Exception $e) {
                
            }
        }
        //@ToDo : Gérer les imports par Store
    }

    /**
     * Export des données
     */
    public function unparse() {
        
        //Si on veut filtrer par date de modification
        $afterDate = $this->getVar('created_after');

        //Chargement des modèles à exporter
        $models = Mage::getModel($this->_model)->getCollection();
        
        //Filtrage par date ( Fait via une requête car plus modulaire que les conditions des collections )
        if ( $afterDate && ( $this->_createdAtField != '' || $this->_updatedAtField != '' ) ) {
            
            $sqlCond = array();
            if ( $this->_createdAtField !='')
                $sqlCond[] = " ".$this->_tableName.".".$this->_createdAtField." > '".$afterDate."'";
            if ( $this->_updatedAtField !='' )
                $sqlCond[] = " ".$this->_tableName.".".$this->_updatedAtField." > '".$afterDate."'";
            
            //Création de la condition de requête
            $sqlString = implode(' OR ',$sqlCond);
            
            //Application de la requête
            $models->getSelect()->where($sqlString);         
        }
        
        foreach ($models as $model) {

            $model->load($model->getId());
            
            //Si le modèle à des stores ( on le convertis en string séparée par des virgules)
            if ( $this->_stores) {
                $model->setStoreId(implode(',',$model->getStoreId()) );
            }
            
            $batchExport = $this->getBatchExportModel()
                    ->setId(null)
                    ->setBatchId($this->getBatchModel()->getId())
                    ->setBatchData($model->getData())
                    ->setStatus(1)
                    ->save();
        }

        return $this;
    }
}
