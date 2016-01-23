<?php

/**
 * Description of Setup
 *
 * @author herve
 */
class Hhennes_DataFlow_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup {

    protected $_profiles = array();

    /**
     * Installation init
     * @ToDO Optimiser ce point
     */
    public function installDataFlowProfiles() {

        $this->_profiles = array(
            'cms_pages' => array(
                'name' => 'Cms Page',
                'model' => 'CmsPage',
                'fileName' => 'cms_pages'
            ),
            'cms_block' => array(
                'name' => 'Cms Block',
                'model' => 'CmsBlock',
                'fileName' => 'cms_blocks'
            ),
            'customers_reviews' => array(
                'name' => 'Customer Reviews',
                'model' => 'CustomerReviews',
                'fileName' => 'customers_reviews'
            ),
            'emails' => array(
                'name' => 'Linked Products',
                'model' => 'LinkedProducts',
                'fileName' => 'emails'
            ),
        );

        $this->_installProfiles();
    }

    /**
     * Fonction interne d'installation
     */
    protected function _installProfiles() {

        if (sizeof($this->_profiles)) {
            
            foreach ($this->_profiles as $profile) {

                //Création du profil d'export
                $profileExport = Mage::getModel('dataflow/profile');
                $profileExport->setName($profile['name'].' Export');

                $exportXml = '<action type="dataflow/convert_adapter_io" method="load">
                                <var name="type">file</var>
                                <var name="path">var/import</var>
                                <var name="filename"><![CDATA[{$filename}.csv]]></var>
                                <var name="format"><![CDATA[csv]]></var>
                                </action>
                                <action type="dataflow/convert_parser_csv" method="parse">
                                <var name="delimiter"><![CDATA[;]]></var>
                                <var name="enclose"><![CDATA["]]></var>
                                <var name="fieldnames">true</var>
                                <var name="number_of_records">1</var>
                                <var name="decimal_separator"><![CDATA[.]]></var>
                                <var name="adapter">Hhennes_dataflow/convert_parser_{$model}</var>
                                <var name="method">parse</var>
                                </action>';
                
                $exportXmlProfile = str_replace(array('{$model}','{$filename}'),array($profile['model'],$profile['fileName']),$exportXml);
                $profileExport->setActionsXml($exportXmlProfile);
                
                try {
                    $profileExport->save();
                } catch (Exception $ex) {
                    Mage::logException($ex);
                }
                

                //Création du profil d'import
                $profileImport = Mage::getModel('dataflow/profile');
                $profileImport->setName($profile['name'] . ' Import');

                $importXml = '<action type="hhennes_dataflow/convert_parser_{$model}" method="unparse">
                            </action>
                            <action type="dataflow/convert_mapper_column" method="map">
                            </action>
                            <action type="dataflow/convert_parser_csv" method="unparse">
                            <var name="delimiter"><![CDATA[;]]></var>
                            <var name="enclose"><![CDATA["]]></var>
                            <var name="fieldnames">true</var>
                            </action>
                            <action type="dataflow/convert_adapter_io" method="save">
                            <var name="type">file</var>
                            <var name="path">var/export</var>
                            <var name="filename"><![CDATA[{$filename}_import.csv]]></var>
                            </action>';
                
                $importXmlProfile = str_replace(array('{$model}','{$filename}'),array($profile['model'],$profile['fileName']),$importXml);
                $profileImport->setActionsXml($importXmlProfile);
                
                try {
                    $profileImport->save();
                } catch (Exception $ex) {
                    Mage::logException($ex);
                }
            }
        }
    }

}
