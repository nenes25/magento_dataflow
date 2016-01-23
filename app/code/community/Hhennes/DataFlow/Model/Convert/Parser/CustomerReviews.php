<?php
class Hhennes_DataFlow_Model_Convert_Parser_CustomerReviews extends Mage_Eav_Model_Convert_Parser_Abstract {
    
    /**
     * Champs à ignorer dans l'import
     * @var array 
     */
    protected $_ignoreFields = array('review_id','detail_id');
    
    /**
     * Mode d'import des commentaires (Insert || update )
     * (A gérer mano pour l'instant)
     * @var string
     */
    protected $_importMode = 'insert';
    
    /**
     * Parsing des données pour l'import
     */
    public function parse()
    {                
        $batchModel = Mage::getSingleton('dataflow/batch');
        /* @var $batchModel Mage_Dataflow_Model_Batch */

        $batchImportModel = $batchModel->getBatchImportModel();
        $importIds = $batchImportModel->getIdCollection();

        foreach ($importIds as $importId) {
            //print '<pre>'.memory_get_usage().'</pre>';
            $batchImportModel->load($importId);
            $importData = $batchImportModel->getBatchData();

            $this->saveRow($importData);
        }
    }
    
    /**
     * Sauvegarde de chaque ligne
     * @param type $importData
     */
    public function saveRow($importData) {

        $review = Mage::getModel('review/review');
        $rating = array();

        if ($this->_importMode == 'update')
            $review->load($importData['review_id']);


        foreach ($importData as $field => $value) {

            //On exclus les champs ignorés
            if (!in_array($field, $this->_ignoreFields)) {

                //Gestion des notes (@ToDo : Gérer plusieurs notes
                if (preg_match('#_[0-9]$#', $field)) {
                    $rating[preg_replace('#_[0-9]$#', '', $field)] = $value;
                    continue;
                }

                if ($value != '')
                    $review->setData($field, $value);
            }
        }

        //Associations des magasins
        $review->setStores(array($importData['store_id']));
        
        try {
            
            $review->save();

            if (sizeof($rating)) {
                Mage::getModel('rating/rating')
                        ->setRatingId($rating['rating_id'])
                        ->setReviewId($review->getId())
                        ->setCustomerId($rating['customer_id'])
                        ->addOptionVote($rating['option_id'], $rating['entity_pk_value']);
            }
            
            //Changement de la date de création (Par défaut elle est écrasée par la date du jour dans la fonction _beforeSave() du modèle )
            $review->setCreatedAt($importData['created_at']);
            $review->save();
            
            //Agrégation des données            
            $review->aggregate();
            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    
    /**
     * Export des données dans le csv
     * @return \Hhennes_DataFlow_Model_Convert_Parser_CustomerReviews
     */
    public function unparse() {
        
        //Si on veut filtrer par produit
        $productId = $this->getVar('product_id');
        
        //Si on veut récupérer tous les commentaires postérieurs à une date
        $reviewDate = $this->getVar('created_after');

        //Récupération des commentaires
        $reviews = Mage::getModel('review/review')->getCollection();
        
        //Si on veut filtrer par produit
        if ( $productId )
            $reviews->addFieldToFilter('entity_pk_value',$productId);
        
        if ( $reviewDate )
            $reviews->addFieldToFilter('created_at',array('gt' => $reviewDate));

        foreach ($reviews as $review) {

            $review->load($review->getEntityId());

            //Récupération des notes du produit (Normalement 1 seul critère)
            $ratings = Mage::getModel('rating/rating_option_vote')->getCollection()->addFieldToFilter('review_id', $review->getReviewId());
            if (sizeof($ratings)) {
                $i = 0;
                foreach ($ratings as $rating) {
                    foreach ($rating->getData() as $key => $value)
                        $review->setData($key . '_' . $i, $value);
                  $i++;
                }
            }
            

            $batchExport = $this->getBatchExportModel()
                    ->setId(null)
                    ->setBatchId($this->getBatchModel()->getId())
                    ->setBatchData($review->getData())
                    ->setStatus(1)
                    ->save();
        }


        return $this;
    }
    
    
}
