<?php
namespace AppBundle\Presenter;

use AppBundle\Entity\Product;
use AppBundle\Services\ThumbnailService;

class ThumbnailPresenter
{
    protected $product;

    protected $thumbnailService;

    public function __construct(Product $product, ThumbnailService $thumbnailService)
    {
        $this->product = $product;
        $this->thumbnailService = $thumbnailService;
    }

    public function getThumbnails()
    {
        if (!$this->product->getImage()) {
            return $this->thumbnailService->getThumbnails('uploads/no_image_found.png');
        }

        return $this->thumbnailService->getThumbnails(
                $this->product->getImage()->getFilePath()
            );
    }

    public function __call($method, $args)
    {
        if (method_exists($this->product, $method)) {
            return call_user_func_array(array($this->product, $method), $args);
        }
    }

    public function __isset($property)
    {
        return isset($this->product->$property);
    }

    public function __get($property)
    {
        if (property_exists($this->product, $property)) {
            return $this->product->$property;
        }

        return $this->{$property};
    }
}
