<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Application
 * @package     Module_Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Builds the fist page of an upload form for one file
 *
 */
class Publish_Form_PublishingFirst extends Zend_Form {

    public $config;
    public $session;
    public $disable;

    public function __construct($disable = null, $options = null) {
        if (isset($disable))
            $this->disable = $disable;

        parent::__construct($options);
    }

    /**
     * First publishing form of two forms
     * Here: Doctype + Upload-File
     *
     * @return void
     */
    public function init() {

        $this->session = new Zend_Session_Namespace('Publish');

        $this->config = Zend_Registry::get('Zend_Config');

        //create and add document type
        $doctypes = $this->_createDocumentTypeField();
        $this->addElement($doctypes);

        //create and add file upload
        $fileupload = $this->_createFileuploadField();
        $this->addElement($fileupload);

        //create and add bibliographie
        $bibliographie = $this->_createBibliographyField();
        if ($bibliographie !== null)
            $this->addElement($bibliographie);

        //create and add add-button
        $addAnotherFile = $this->createElement('submit', 'addAnotherFile');
        $addAnotherFile->setLabel('addAnotherFile');
        $this->addElement($addAnotherFile);

        //create and add send-button
        $submit = $this->createElement('submit', 'send');
        $submit->setLabel('Send');
        $this->addElement($submit);

        $this->setAttrib('enctype', Zend_Form::ENCTYPE_MULTIPART);
    }

    /**
     * Method shows the field for document types by looking in config file
     * shows selection: >1 Options
     * shows text field: =1 Option
     * 
     * @return <Zend_Element> 
     */
    private function _createDocumentTypeField() {
        $documentTypes = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');
        //todo: order ba name for german translation
        //Select with different document types given by the used function
        $listOptions = $documentTypes->getDocumentTypes();

        asort($listOptions);

        $doctypes = $this->createElement('select', 'documentType');
        $doctypes->setLabel('selecttype')
                ->setMultiOptions(array_merge(array('' => 'choose_valid_doctype'), $listOptions));

        if ($this->disable === true) {
            $doctypes->setAttrib('disabled', true)
                    ->setRequired(false);
        }
        else
            $doctypes->setRequired(true);

        return $doctypes;
    }

    /**
     * Method shows the fields for file uploads by looking in config file
     * @return <Zend_Element> 
     */
    private function _createFileuploadField() {
        // get path to store files
        $tempPath = $this->config->path->workspace->temp;
        if (true === empty($tempPath))
            $tempPath = '../workspace/tmp/';

        // get allowed filetypes
        $filetypes = $this->config->publish->filetypes->allowed;
        if (true === empty($filetypes))
            $filetypes = 'pdf,txt,html,htm';

        //get allowed file size
        $maxFileSize = (int) $this->config->publish->maxfilesize;
        if (true === empty($maxFileSize)) {
            $maxFileSize = 1024000; //1MB
        }
        $this->session->maxFileSize = $maxFileSize;

        //get the initial number of file fields, toto: aus der config holen
//        $number_of_files = (int) $this->config->form->first->numberoffiles;
//        if (true === empty($number_of_files))
//            $number_of_files = 1;

        // Upload-fields required to enter second stage
        $requireUpload = $this->config->form->first->requireupload;
        if (true === empty($requireUpload))
            $requireUpload = 0;

        //file upload field(s)
        $fileupload = new Zend_Form_Element_File('fileupload');
        $validate = new Zend_Validate_File_Upload();
        $messages = array(Zend_Validate_File_Upload::FORM_SIZE => 'publish_validation_error_person_invalid');
        $validate->setMessages($messages);

        $fileupload->setLabel('fileupload')
                ->setDestination($tempPath)
                ->addValidator('Size', false, $maxFileSize)     // limit to value given in application.ini
                ->setMaxFileSize($maxFileSize)
                ->addValidator('Extension', false, $filetypes)  // allowed filetypes by extension                
                ->setValueDisabled(true)
                ->setAttrib('enctype', 'multipart/form-data');        
        
//        if ($number_of_files > 1) {
//            $fileupload->setMultiFile($number_of_files)
//                    ->setDescription('publish_controller_index_fileupload');
//            if (1 === $requireUpload)
//                $fileupload->addValidator('Count', false, array('min' => 1, 'max' => $number_of_files));
//            else
//                $fileupload->addValidator('Count', false, array('min' => 0, 'max' => $number_of_files));
//        }

        if (1 === $requireUpload)
            $fileupload->setRequired(true);
        else
            $fileupload->setRequired(false);

        return $fileupload;
    }

    /**
     * Method shows bibliography field by looking in config file
     * @return <Zend_Element>
     */
    private function _createBibliographyField() {
        //show Bibliographie?
        $this->session->bibliographie = 0;
        $bib = $this->config->form->first->bibliographie;
        if (true === empty($bib)) {
            $bib = 0;
            $this->session->bibliographie = 0;
        }

        $bibliographie = null;

        if ($bib == 1) {
            $this->session->bibliographie = 1;
            $bibliographie = $this->createElement('checkbox', 'bibliographie');
            $bibliographie->setLabel('bibliographie');
            if ($this->disable === true)
                $bibliographie->setAttrib('disabled', true);
        }

        return $bibliographie;
    }

    /**
     *
     * @param <type> $elementName
     * @return string
     */
    public function getElementAttributes($elementName) {
        $elementAttributes = array();
        $element = $this->getElement($elementName);
        $elementAttributes['value'] = $element->getValue();
        $elementAttributes['label'] = $element->getLabel();
        $elementAttributes['error'] = $element->getMessages();
        $elementAttributes['id'] = $element->getId();
        $elementAttributes['type'] = $element->getType();
        $elementAttributes['desc'] = $element->getDescription();
        $elementAttributes['hint'] = 'hint_' . $elementName;
        $elementAttributes['disabled'] = $element->getAttrib('disabled');

        if ($element->getType() === 'Zend_Form_Element_Checkbox') {
            $elementAttributes['value'] = $element->getCheckedValue();
        }

        if ($element->getType() === 'Zend_Form_Element_Select') {
            $elementAttributes["options"] = $element->getMultiOptions(); //array
        }

        if ($element->isRequired())
            $elementAttributes["req"] = "required";
        else
            $elementAttributes["req"] = "optional";

        return $elementAttributes;
    }

}
