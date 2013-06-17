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
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unterformular fuer Subjects im Metadaten-Formular.
 * 
 * Dieses Formular enthaelt die Unterformulare fuer die verschiedenen Schlagwort-Typen und ist dafuer verantwortlich
 * das Feld "Subject" im Dokument zu aktualisieren.
 * 
 * TODO Umgang mit alten Schlagwörtern mit unbekanntem Typ (siehe auch OPUSVIER-2604)
 */
class Admin_Form_DocumentSubjects extends Admin_Form_DocumentSection {
    
    /**
     * Initialisiert Formular und fuegt Unterformulare fuer Schlagworttypen hinzu.
     */
    public function init() {
        parent::init();
        
        $this->addSubForm(new Admin_Form_DocumentSubjectType('swd'), 'Swd');
        $this->addSubForm(new Admin_Form_DocumentSubjectType('psyndex'), 'Psyndex');
        $this->addSubForm(new Admin_Form_DocumentSubjectType('uncontrolled'), 'Uncontrolled');
        // TODO Unterformular fuer unbekannte Typen hinzufügen?
    }
    
    /**
     * Sammelt Schlagwoerter von Unterformularen ein und aktualisiert Dokument.
     * @param Opus_Document $document
     */
    public function updateModel($document) {
        $subforms = $this->getSubForms();
        
        $subjects = array();
        
        foreach ($subforms as $subform) {
            $subjectsWithType = $subform->getSubFormModels(); // TODO use generic method
            $subjects = array_merge($subjects, $subjectsWithType);
        }
        
        $document->setSubject($subjects);
    }
    
}