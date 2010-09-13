<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @category   Application
 * @package    Module_Webapi
 * @author     Thoralf Klein <thoralf.klein@zib.de>
 * @copyright  Copyright (c) 2010, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * Methods for handling collections.
 */
class Webapi_Model_Collection extends Webapi_Model_Response {

    /**
     * Returns informations about a specific collection.
     *
     * @param mixed $collectionId Requested collection id.
     * @return string
     */
    public function get($collectionId) {

        $collectionId = (int) $collectionId;
        $xml = $this->_xml;

        try {
            $collection = new Opus_Collection($collectionId);
            $xml = $collection->toXml();
        }
        catch (Opus_Model_Exception $e) {
            $this->setError('An error occurs during getting informations. Error reason: ' . $e->getMessage(), 404);
        }

        return $xml->saveXML();
    }

    /**
     * Updates a collection.
     *
     * @param  array $data Request parameter(s).
     * @return void
     */
    public function update($data) {
        $xml = $this->_xml;

        try {
            foreach (array('role', 'key', 'title') AS $key) {
                if (!array_key_exists($key, $data)) {
                    throw new Exception("Missing input parameter: $key");
                }
            }

            $role = Opus_CollectionRole::fetchByName($data['role']);
            if (is_null($role)) {
                throw new Exception("CollectionRole does not exist.");
            }

            $collections = Opus_Collection::fetchCollectionsByRoleNumber($role->getId(), $data['key']);
            if (empty($collections) || is_null($collections[0])) {
                throw new Exception("Collection does not exist.");
            }
            $collection = $collections[0];

            $collection->setName($data['title']);
            $collection->store();

            $collection = new Opus_Collection($collection->getId());
            $xml = $collection->toXml();
        }
        catch (Opus_Model_Exception $e) {
            $this->setError('An error occurs during getting informations. Error reason: ' . $e->getMessage(), 404);
        }
        catch (Exception $e) {
            $this->setError('Unknown error occured. Error reason: ' . $e->getMessage(), 500);
        }

        return $xml->saveXML();
    }

}
?>