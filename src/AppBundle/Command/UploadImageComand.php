<?php

namespace AppBundle\Command;

use AppBundle\Entity\Product;
use AppBundle\Services\ThumbnailService;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UploadImageComand implements BaseCommand
{
    protected $product;

    protected $filesDirectory;

    public function __construct(Product $product, $filesDirectory)
    {
        $this->product = $product;
        $this->filesDirectory = $filesDirectory;
    }

    /**
     * Handles the creating of an image
     * 
     * @throws NotFoundHttpException
     */
    public function handle()
    {
        if (!$this->product->getImage()) {
            throw new NotFoundHttpException('No image found');
        }

        $uploadedFile = $this->product->getImage()->getFilePath();
        if($uploadedFile instanceof UploadedFile) {
            $fileName = $this->generateUniqueFileName().'.'.$uploadedFile->guessExtension();
            $uploadedFile->move($this->filesDirectory, $fileName);
            $this->product->getImage()->setFilePath(new File('uploads/'.$fileName));
            $this->product->getImage()->setName($this->product->getImage()->getName());
            (new ThumbnailService())->processThumbnails($fileName);
        }
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}
