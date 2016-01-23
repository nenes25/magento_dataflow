<?php

/**
 * Import des produits apparentés
 * Import des ventes incitatives
 * Import des ventes croisées
 */
class Hhennes_DataFlow_Model_Convert_Parser_LinkedProducts extends Hhennes_DataFlow_Model_Convert_Parser_Abstract {
        
    /**
     * Import des données
     * @param type $importData
     */
    public function saveRow($importData) {

        //En fonction du nom de la colonne  ( upsell_1 | related_1 | crossel_1 ) on détermine quel import doit être exécuté
        if (array_key_exists('related_1', $importData)){
            $mode = 'related';
            $method = 'setRelatedLinkData';
        }
        else if (array_key_exists('crossel_1', $importData)) {
            $mode = 'crossel';
            $method = 'setCrossSellLinkData';
        }
        else {
            $mode = 'upsell';
            $method = 'setUpSellLinkData';
        }

        //Chargement du produit 
        $productId = Mage::getModel('catalog/product')->getIdBySku($importData['sku']);

        if (!$productId)
            return false;

        $product = Mage::getModel('catalog/product')->load($productId);
        $complementaryProducts = array();
        
        $i = 1;
        foreach ($importData as $key => $data ) {
            
            if (preg_match('#^' . $mode . '#', $key) && $data != '') {
                
                $complementaryProductId = Mage::getModel('catalog/product')->getIdBySku($data);
                if ( !$complementaryProductId ) {
                    //echo 'Produit '.$data.'n\'existe pas';
                    continue;
                }
                
                $complementaryProducts[$complementaryProductId] = array('position' => $i);
                $i++;
            }
        }
        $product->{$method}($complementaryProducts);
        $product->save();
    }
    
    /**
     * Export des données
     */
    public function unparse() {

        $productId = $this->getVar('product_id'); //Filtrage par produit
        $exportType = $this->getVar('type'); //Mode d'export

        $products = Mage::getModel('catalog/product')->getCollection();

        if ($productId)
            $products->addFieldToFilter('entity_id', $productId);

        foreach ($products as $product) {
            
            $product->load($product->getEntityId());

            if ($exportType == 'related') //Produit lié
                $complementaryProducts = $product->getRelatedProducts();
            else if ($exportType == 'crossel')
                $complementaryProducts = $product->getCrossSellProducts();
            else {
                $complementaryProducts = $product->getUpSellProducts();
                $exportType = 'upsell';
            }


            if (sizeof($complementaryProducts)) {
                $rowData = array();
                $rowData['sku'] = $product->getSku();
                $rowData['product_name'] = $product->getName(); //Clé volontairement changée pour ne pas être importée
                $i = 1;
                foreach ($complementaryProducts as $complementaryProduct) {
                    $rowData[$exportType . '_' . $i] = $complementaryProduct->getSku();
                    $i++;
                }

                $batchExport = $this->getBatchExportModel()
                        ->setId(null)
                        ->setBatchId($this->getBatchModel()->getId())
                        ->setBatchData($rowData)
                        ->setStatus(1)
                        ->save();
            }
        }
    }
    
}