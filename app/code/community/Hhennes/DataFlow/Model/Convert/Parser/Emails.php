<?php
class Hhennes_DataFlow_Model_Convert_Parser_Emails extends Hhennes_DataFlow_Model_Convert_Parser_Abstract {
    
    protected $_model ='core/email_template';
   
    protected $_identifier ='template_id';
    
    protected $_tableName = 'mag_core_email_template';
    protected $_createdAtField = 'added_at';
    protected $_updatedAtField = 'modified_at';
    
    public function parse() {
        parent::parse();
        return $this;
    }
    
    /**
     * On défini le modèle et on appelle la fonction de la classe parente
     */
    public function unparse(){
        
        parent::unparse();
    }
    
}
