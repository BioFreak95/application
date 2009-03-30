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
 * @package     Module_Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: Opus3FileImport.php 2163 2009-03-12 15:35:16Z marahrens $
 */

class Opus3SignatureImport 
{
    /**
     * Holds the path to the fulltexts in Opus3
     *
     * @var string  Defaults to null.
     */
    protected $_path = null;
    
    /**
     * Holds the path to the fulltexts in Opus3 for this certain ID
     *
     * @var string  Defaults to null.
     */
    protected $_tmpPath = null;
    
    protected $_magicPath;
    

    /**
     * Do some initialization on startup of every action
     *
     * @param string $fulltextPath Path to the Opus3-fulltexts
     * @return void
     */
    public function __construct($fulltextPath, $magicPath)
    {
        // Initialize member variables.
        $this->_path = $fulltextPath;
        $this->_magicPath = $magicPath;
    }
    
    /**
     * Loads an old Opus ID
     *
     * @param Opus_Document $object Opus-Document for that the files should be registered
     * @return void
     */
    public function loadFiles($object)
    {
    	$opusId = $object->getIdentifierOpus3()->getValue();
    	
        // Search the ID-directory in fulltext tree
        $this->searchDir($this->_path, $opusId);
        #echo "Found Files for $opusId in $this->_tmpPath";
        $files = $this->getFiles($this->_tmpPath);
        
        if (true === is_array($object->getLanguage()))
        {
    	    $lang = $object->getLanguage(0);
        }
        else
        {
    	    $lang = $object->getLanguage();
        }
        
        if (count($files) > 0) {
            foreach ($files as $filename) {
                $finfo = new finfo(FILEINFO_MIME, $this->_magicPath);
                $mimeType = $finfo->file($filename);
            
                $filenameArray = split('\.', $filename);
                $suffix = $filenameArray[(count($filenameArray)-1)];
            
                // if you got it, build a Opus_File-Object
                $file = $object->addFile();
                $file->setLabel(basename($filename));
                $file->setFileType($suffix);
                $file->setPathName(basename($filename));
                $file->setMimeType($mimeType);
                $file->setTempFile($filename);
			    $file->setDocumentId($object->getId());
			    $file->setLanguage($lang);
            }
        }

        // return all files in an array
        return $object;
    }
    
    private function getFiles($from) 
    {
        if(! is_dir($from))
            return false;
     
        $files = array();
     
        if( $dh = opendir($from))
        {
            while( false !== ($file = readdir($dh)))
            {
                // Skip '.' and '..' and '.svn' (that should not exist, but if it does...) and .asc files (they shouldnt be here also)
                if( $file == '.' || $file == '..' || $file === '.svn' || ereg("\.asc$", $file) !== false)
                    continue;
                $path = $from . '/' . $file;
                if( is_dir($path) )
                    $files += $this->getFiles($path);
                else {
                	// Ignore files in the main directory, OPUS3 stores in subdirectories only
                	if ($from !== $this->_tmpPath) {
                        $files[] = $path;
                	}
                }
            }
            closedir($dh);
        }
        return $files;
    }
    
    private function searchDir($from, $search)
    {
        if(! is_dir($from))
            return false;
     
        if( $dh = opendir($from))
        {
            while( false !== ($file = readdir($dh)))
            {
                // Skip '.' and '..'
                if( $file == '.' || $file == '..')
                    continue;
                $path = $from . '/' . $file;
                if ($file === $search) {
                	$this->_tmpPath = $path;
                	return true;
                }
                else if( is_dir($path) ) {
                    $this->searchDir($path, $search);
                }
            }
            closedir($dh);
        }
        return false;
    }
}