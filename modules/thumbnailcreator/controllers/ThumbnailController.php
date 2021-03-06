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

/** This class is used to create thumbnails. */
class Thumbnailcreator_ThumbnailController extends Thumbnailcreator_AppController
{
    public $_models = array('Assetstore', 'Bitstream', 'Item');
    public $_moduleModels = array('Itemthumbnail');
    public $_moduleDaos = array('Itemthumbnail');

    /**
     * Creates a thumbnail.
     *
     * @param bitstreamId The bitstream to create the thumbnail from
     * @param itemId The item to set the thumbnail on
     * @param width (Optional) The width in pixels to resize to (aspect ratio will be preserved). Defaults to 575
     * @return array('thumbnail' => path to the thumbnail)
     * @throws Zend_Exception
     */
    public function createAction()
    {
        $itemId = $this->getParam('itemId');
        if (!isset($itemId)) {
            throw new Zend_Exception('itemId parameter required');
        }
        $bitstreamId = $this->getParam('bitstreamId');
        if (!isset($bitstreamId)) {
            throw new Zend_Exception('bitstreamId parameter required');
        }
        $width = $this->getParam('width');
        if (!isset($width)) {
            $width = 575;
        }
        $this->disableView();
        $this->disableLayout();

        $bitstream = $this->Bitstream->load($bitstreamId);
        $item = $this->Item->load($itemId);
        if (!$this->Item->policyCheck($item, $this->userSession->Dao, MIDAS_POLICY_WRITE)
        ) {
            echo JsonComponent::encode(array('status' => 'error', 'message' => 'Write permission required'));

            return;
        }

        /** @var Thumbnailcreator_ImagemagickComponent $imComponent */
        $imComponent = MidasLoader::loadComponent('Imagemagick', 'thumbnailcreator');

        $itemThumbnail = $this->Thumbnailcreator_Itemthumbnail->getByItemId($item->getKey());
        if (!$itemThumbnail) {
            $itemThumbnail = new Thumbnailcreator_ItemthumbnailDao();
            $itemThumbnail->setItemId($item->getKey());
        } else {
            $oldThumb = $this->Bitstream->load($itemThumbnail->getThumbnailId());
            $this->Bitstream->delete($oldThumb);
        }

        try {
            $thumbnail = $imComponent->createThumbnailFromPath(
                $bitstream->getName(),
                $bitstream->getFullPath(),
                (int) $width,
                0,
                false
            );
            if (!file_exists($thumbnail)) {
                echo JsonComponent::encode(
                    array('status' => 'error', 'message' => 'Could not create thumbnail from the bitstream')
                );

                return;
            }

            $thumb = $this->Bitstream->createThumbnail($this->Assetstore->getDefault(), $thumbnail);
            $itemThumbnail->setThumbnailId($thumb->getKey());
            $this->Thumbnailcreator_Itemthumbnail->save($itemThumbnail);

            echo JsonComponent::encode(
                array('status' => 'ok', 'message' => 'Thumbnail saved', 'itemthumbnail' => $itemThumbnail)
            );
        } catch (Exception $e) {
            echo JsonComponent::encode(array('status' => 'error', 'message' => 'Error: '.$e->getMessage()));
        }
    }

    /**
     * Call this to stream the large thumbnail for the item.  Should only be called if the item has a thumbnail;
     * otherwise the request produces no output.
     *
     * @param itemthumbnail The itemthumbnail_id to stream
     * @throws Zend_Exception
     */
    public function itemAction()
    {
        $itemthumbnailId = $this->getParam('itemthumbnail');
        if (!isset($itemthumbnailId)) {
            throw new Zend_Exception('Must pass an itemthumbnail parameter');
        }
        $itemthumbnail = $this->Thumbnailcreator_Itemthumbnail->load($itemthumbnailId);
        if (!$itemthumbnail) {
            throw new Zend_Exception('Invalid itemthumbnail parameter');
        }
        if (!$this->Item->policyCheck($itemthumbnail->getItem(), $this->userSession->Dao)
        ) {
            throw new Zend_Exception('Invalid policy');
        }
        $this->disableLayout();
        $this->disableView();
        if ($itemthumbnail->getThumbnailId() !== null) {
            $bitstream = $this->Bitstream->load($itemthumbnail->getThumbnailId());

            /** @var DownloadBitstreamComponent $downloadBitstreamComponent */
            $downloadBitstreamComponent = MidasLoader::loadComponent('DownloadBitstream');
            $downloadBitstreamComponent->download($bitstream);
        }
    }
}
