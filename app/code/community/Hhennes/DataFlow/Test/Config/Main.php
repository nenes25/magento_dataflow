<?php

/**
 *
 * Fichier de base pour tester la bonne configuration d'un module
 *
 */
class Hhennes_DataFlow_Test_Config_Main extends EcomDev_PHPUnit_Test_Case_Config
{
    /**
     * Paramètres de la classe pour tester automatiquement que le fichier de configuration respecte certaines normes
     * Permets de génériser la création de ce fichier de test pour l'ensemble des modules
     */
    protected $_codePool       = 'community';
    protected $_currentVersion = '0.1.0';
    protected $_useResource    = true;
    protected $_nodeName       = 'hhennes_dataflow'; //Nom utilisé pour les noeud ( models / helpers/ blocks )

    /**
     * Test que le module est actif
     */

    public function testModuleIsActive()
    {
        $this->assertModuleIsActive();
    }
    

    /**
     * Tests globals sur le module
     */
    public function testModuleGlobal()
    {
        //CodePool
        $this->assertModuleCodePool($this->_codePool);

        //Version du module
        $this->assertModuleVersion($this->_currentVersion);
    }
    
     /**
     * Vérification des conditions de setup du module
     */
    public function testSetupResources()
    {
        if ($this->_useResource) {
            $this->assertSetupResourceDefined();
            //Ce tests ne fonctionne pas avec la structure "data" au lieu de sql
            //Il faut voir pour écrire un test supplémentaire
            #$this->assertSetupResourceExists();
        }
    }

    /**
     * Vérification des alias de la classe
     * ( Models/ ResourceModel / Helpers / Blocks )
     */
    public function testClassesAlias()
    {
        //Models
        $this->assertModelAlias($this->_nodeName.'/resource_setup', 'Hhennes_DataFlow_Model_Resource_Setup');
        $this->assertModelAlias($this->_nodeName.'/convert_parser_abstract', 'Hhennes_DataFlow_Model_Convert_Parser_Abstract');
        $this->assertModelAlias($this->_nodeName.'/convert_parser_CmsBlock', 'Hhennes_DataFlow_Model_Convert_Parser_CmsBlock');
        $this->assertModelAlias($this->_nodeName.'/convert_parser_CmsPage', 'Hhennes_DataFlow_Model_Convert_Parser_CmsPage');
        $this->assertModelAlias($this->_nodeName.'/convert_parser_CustomerReviews', 'Hhennes_DataFlow_Model_Convert_Parser_CustomerReviews');
        $this->assertModelAlias($this->_nodeName.'/convert_parser_emails', 'Hhennes_DataFlow_Model_Convert_Parser_Emails');
        $this->assertModelAlias($this->_nodeName.'/convert_parser_LinkedProducts', 'Hhennes_DataFlow_Model_Convert_Parser_LinkedProducts');
        
        //Helpers
        $this->assertHelperAlias($this->_nodeName, 'Hhennes_DataFlow_Helper_Data');
    }

}
