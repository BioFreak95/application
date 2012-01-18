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
 * @package     Tests
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Solrsearch_Model_SeriesTest extends ControllerTestCase {

    private $visibilities = array();

    public function testConstructWithInvalidSeriesId() {
        $this->setExpectedException('Solrsearch_Model_Exception');
        new Solrsearch_Model_Series(null);
    }

    public function testConstructWithUnknownSeriesId() {
        $this->setExpectedException('Solrsearch_Model_Exception');
        new Solrsearch_Model_Series(999);
    }

    public function testConstructWithInvisibleSeries() {
        $this->setExpectedException('Solrsearch_Model_Exception');
        new Solrsearch_Model_Series(3);
    }

    public function testConstructWithEmptyVisibleSeries() {
        $this->setExpectedException('Solrsearch_Model_Exception');
        new Solrsearch_Model_Series(4);
    }

    public function testConstructWithNonEmptyVisibleSeries() {
        $series = new Solrsearch_Model_Series(1);
        $this->assertNotNull($series);
        $seriesFramework = new Opus_Series(1);
        $this->assertEquals($seriesFramework->getId(), $series->getId());
        $this->assertEquals($seriesFramework->getTitle(), $series->getTitle());
        $this->assertEquals($seriesFramework->getInfobox(), $series->getInfobox());
    }

    public function testGetLogoFilename() {
        $series = new Solrsearch_Model_Series(1);
        $this->assertNotNull($series->getLogoFilename());
        $this->assertEquals('300_150.png', $series->getLogoFilename());
    }

    public function testGetLogoFilenameForNoLogoSeries() {
        $series = new Solrsearch_Model_Series(6);
        $this->assertNull($series->getLogoFilename());
    }

    public function testHasDisplayableSeries() {
        $this->assertTrue(Solrsearch_Model_Series::hasDisplayableSeries());

        $this->setAllSeriesToUnvisible();
        $this->assertFalse(Solrsearch_Model_Series::hasDisplayableSeries());

        $this->restoreVisiblitySettings();
        $this->assertTrue(Solrsearch_Model_Series::hasDisplayableSeries());
    }

    public function testGetVisibleNonEmptySeriesSortedBySortKey() {
        $this->assertTrue(count(Solrsearch_Model_Series::getVisibleNonEmptySeriesSortedBySortKey()) === 4);

        $this->setAllSeriesToUnvisible();
        $this->assertTrue(count(Solrsearch_Model_Series::getVisibleNonEmptySeriesSortedBySortKey()) === 0);

        $this->restoreVisiblitySettings();
        $this->assertTrue(count(Solrsearch_Model_Series::getVisibleNonEmptySeriesSortedBySortKey()) === 4);
    }

    private function setAllSeriesToUnvisible() {
        foreach (Opus_Series::getAll() as $seriesItem) {
            $this->visibilities[$seriesItem->getId()] = $seriesItem->getVisible();
            $seriesItem->setVisible(0);
            $seriesItem->store();
        }
    }

    private function restoreVisiblitySettings() {
        foreach (Opus_Series::getAll() as $seriesItem) {
            $seriesItem->setVisible($this->visibilities[$seriesItem->getId()]);
            $seriesItem->store();
        }
        
    }

}