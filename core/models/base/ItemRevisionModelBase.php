<?php
/*=========================================================================
 Midas Server
 Copyright Kitware SAS, 26 rue Louis Guérin, 69100 Villeurbanne, France.
 All rights reserved.
 For more information visit http://www.kitware.com/.

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

         http://www.apache.org/licenses/LICENSE-2.0.txt

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
=========================================================================*/

/** ItemRevisionModelBase */
abstract class ItemRevisionModelBase extends AppModel
{
    /** Constructor */
    public function __construct()
    {
        parent::__construct();
        $this->_name = 'itemrevision';
        $this->_daoName = 'ItemRevisionDao';
        $this->_key = 'itemrevision_id';

        $this->_components = array('Filter');

        $this->_mainData = array(
            'itemrevision_id' => array('type' => MIDAS_DATA),
            'item_id' => array('type' => MIDAS_DATA),
            'revision' => array('type' => MIDAS_DATA),
            'date' => array('type' => MIDAS_DATA),
            'changes' => array('type' => MIDAS_DATA),
            'user_id' => array('type' => MIDAS_DATA),
            'license_id' => array('type' => MIDAS_DATA),
            'uuid' => array('type' => MIDAS_DATA),
            'bitstreams' => array(
                'type' => MIDAS_ONE_TO_MANY,
                'model' => 'Bitstream',
                'parent_column' => 'itemrevision_id',
                'child_column' => 'itemrevision_id',
            ),
            'item' => array(
                'type' => MIDAS_MANY_TO_ONE,
                'model' => 'Item',
                'parent_column' => 'item_id',
                'child_column' => 'item_id',
            ),
            'user' => array(
                'type' => MIDAS_MANY_TO_ONE,
                'model' => 'User',
                'parent_column' => 'user_id',
                'child_column' => 'user_id',
            ),
            'license' => array(
                'type' => MIDAS_MANY_TO_ONE,
                'model' => 'License',
                'parent_column' => 'license_id',
                'child_column' => 'license_id',
            ),
        );
        $this->initialize(); // required
    }

    /** Get by UUID */
    abstract public function getByUuid($uuid);

    /** Get metadata */
    abstract public function getMetadata($revisiondao);

    /** Delete metadata */
    abstract public function deleteMetadata($revisiondao, $metadataId);

    /** Add a bitstream to a revision */
    public function addBitstream($itemRevisionDao, $bitstreamDao)
    {
        /** @var BitstreamModel $BitstreamModel */
        $BitstreamModel = MidasLoader::loadModel('Bitstream');

        /** @var ItemModel $ItemModel */
        $ItemModel = MidasLoader::loadModel('Item');

        $bitstreamDao->setItemrevisionId($itemRevisionDao->getItemrevisionId());

        // Save the bitstream
        $bitstreamDao->setDate(date('Y-m-d H:i:s'));
        $BitstreamModel->save($bitstreamDao);

        $item = $itemRevisionDao->getItem($bitstreamDao);
        $item->setSizebytes($this->getSize($itemRevisionDao));
        $item->setDateUpdate(date('Y-m-d H:i:s'));

        Zend_Registry::get('notifier')->notifyEvent('EVENT_CORE_CREATE_THUMBNAIL', array($item));
        $notifications = Zend_Registry::get('notifier')->getNotifications();

        $createThumb = false;
        if (!isset($notifications['EVENT_CORE_CREATE_THUMBNAIL']) || empty($notifications['EVENT_CORE_CREATE_THUMBNAIL'])) {
            $mime = $bitstreamDao->getMimetype();
            $tmpfile = $bitstreamDao->getPath();
            if (!file_exists($tmpfile)) {
                $tmpfile = $bitstreamDao->getFullPath();
            }
            // Creating temp image as a source image (original image).
            $createThumb = true;
            if (file_exists($tmpfile) && $mime == 'image/jpeg') {
                try {
                    $src = imagecreatefromjpeg($tmpfile);
                } catch (Exception $exc) {
                    $createThumb = false;
                }
            } elseif (file_exists($tmpfile) && $mime == 'image/png') {
                try {
                    $src = imagecreatefrompng($tmpfile);
                } catch (Exception $exc) {
                    $createThumb = false;
                }
            } elseif (file_exists($tmpfile) && $mime == 'image/gif') {
                try {
                    $src = imagecreatefromgif($tmpfile);
                } catch (Exception $exc) {
                    $createThumb = false;
                }
            } else {
                $createThumb = false;
            }

            if ($createThumb) {
                $tmpPath = UtilityComponent::getDataDirectory('thumbnail');
                if (!file_exists($tmpPath)) {
                    throw new Zend_Exception(
                        'Problem thumbnail path: '.UtilityComponent::getDataDirectory('thumbnail')
                    );
                }

                /** @var RandomComponent $randomComponent */
                $randomComponent = MidasLoader::loadComponent('Random');
                $destination = $tmpPath.'/'.$randomComponent->generateInt().'.jpg';
                while (file_exists($destination)) {
                    $destination = $tmpPath.'/'.$randomComponent->generateInt().'.jpg';
                }
                $pathThumbnail = $destination;

                list($x, $y) = getimagesize($tmpfile);  //--- get size of img ---
                $thumb = 100;  //--- max. size of thumb ---
                if ($x > $y) {
                    $tx = $thumb;  //--- landscape ---
                    $ty = round($thumb / $x * $y);
                } else {
                    $tx = round($thumb / $y * $x);  //--- portrait ---
                    $ty = $thumb;
                }

                $thb = imagecreatetruecolor($tx, $ty);  //--- create thumbnail ---
                imagecopyresampled($thb, $src, 0, 0, 0, 0, $tx, $ty, $x, $y);
                imagejpeg($thb, $pathThumbnail, 80);
                imagedestroy($thb);
                imagedestroy($src);
            }
        }

        if ($createThumb) {
            $ItemModel->replaceThumbnail($item, $pathThumbnail);
        }

        $ItemModel->save($item, true);
    }

    /** save */
    public function save($dao)
    {
        if (!isset($dao->uuid) || empty($dao->uuid)) {
            /** @var UuidComponent $uuidComponent */
            $uuidComponent = MidasLoader::loadComponent('Uuid');
            $dao->setUuid($uuidComponent->generate());
        }
        if (!isset($dao->date) || empty($dao->date)) {
            $dao->setDate(date('Y-m-d H:i:s'));
        }
        parent::save($dao);
    }

    /**
     * Return the latest bitstream from the given item revision.
     *
     * @param ItemRevisionDao $itemRevisionDao item revision DAO
     * @return BitstreamDao
     * @throws Zend_Exception
     */
    public function getLatestBitstream($itemRevisionDao)
    {
        $bitstreamDaos = $itemRevisionDao->getBitstreams();

        if (count($bitstreamDaos) === 0) {
            throw new Zend_Exception('Item revision does not contain any bitstreams');
        }

        return $bitstreamDaos[0];
    }
}
