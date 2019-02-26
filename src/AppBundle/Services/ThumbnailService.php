<?php

namespace AppBundle\Services;

class ThumbnailService {

    protected $imageSource;

    protected $imageDestination;

    protected $desiredHeight;

    protected $desiredWidth;

    public function __construct()
    {
        $this->imageDestination =  'uploads/thumbnails/';
    }

    /**
     * Create thumbnail for a given image
     * 
     * @param type $fileName
     * @param type $desiredWidth
     */
    public function processThumbnail($fileName, $desiredWidth)
    {
        $this->desiredWidth = $desiredWidth;
        $sourceImage = imagecreatefromjpeg($this->imageSource);

        $imageDimention = $this->getDimensionsOfImage($sourceImage);

        $this->desiredHeight = $this->calculateHeightOfThumbnail($imageDimention);

        $resizedImage = $this->createNewResizedImage();

        imagecopyresampled(
            $resizedImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $this->desiredWidth,
            $this->desiredHeight,
            $imageDimention['width'],
            $imageDimention['height']
        );

        imagejpeg($resizedImage, $this->imageDestination.$fileName);
    }

    /**
     * Get dimensions of image 
     * 
     * @param type $sourceImage
     * @return type
     */
    public function getDimensionsOfImage($sourceImage)
    {
        return [
            'width' => imagesx($sourceImage),
            'height' => imagesy($sourceImage),
        ];
    }

    /**
     * Calculate the height of the thumbnail by given the desired width
     * 
     * @param type $imageDimention
     * @return type
     */
    public function calculateHeightOfThumbnail($imageDimention)
    {
        return floor($imageDimention['height'] * ($this->desiredWidth / $imageDimention['width']));
    }

    /**
     * Create the new resized image
     * 
     * @return type
     */
    public function createNewResizedImage()
    {
        return imagecreatetruecolor($this->desiredWidth, $this->desiredHeight);
    }

    /**
     * Create multiple thumbnails
     * 
     * @param type $imageToProcess
     * @param array $sizes
     */
    public function processThumbnails($imageToProcess, array $sizes = [200, 500, 700]) 
    {
        $this->imageSource = 'uploads/'.$imageToProcess;
        $imageInfo = pathinfo($this->imageSource);
        foreach ($sizes as $size) {
            $newImageName = $imageInfo['filename'].'_'.$size.'px.'.$imageInfo['extension'];
            $this->processThumbnail($newImageName, $size);
        }
    }

    /**
     * Get all the thumbnails for image by image path
     * 
     * @param type $filePath
     * @return string
     */
    public function getThumbnails($filePath)
    {
        $allThumb = scandir('uploads/thumbnails');
        $imageInfo = pathinfo($filePath);
        $sizes = ['small' => 200, 'medium' => 500, 'large' => 700];
        $thumbnails = [];
        foreach ($sizes as $text => $size) {
            $imageName = $imageInfo['filename'].'_'.$size.'px.'.$imageInfo['extension'];
            $key = array_search($imageName, $allThumb, true);
            $thumbnails[$text] = 'uploads/thumbnails/'.$allThumb[$key];
        }

        return $thumbnails;
    }
}
