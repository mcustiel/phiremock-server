<?php

namespace Mcustiel\Phiremock\Server\Http\InputSources;

use Mcustiel\Phiremock\Server\Utils\DataStructures\StringObjectArrayMap;

class InputSourceFactory
{
    /** @var StringObjectArrayMap */
    private $factoryCache;

    public function __construct()
    {
        $this->factoryCache = new StringObjectArrayMap();
    }

    public function createBody()
    {
        if (!$this->factoryCache->has('body')) {
            $this->factoryCache->set('body', new Body());
        }

        return $this->factoryCache->get('body');
    }

    public function createHeader()
    {
        if (!$this->factoryCache->has('header')) {
            $this->factoryCache->set('header', new Header());
        }

        return $this->factoryCache->get('header');
    }

    public function createMethod()
    {
        if (!$this->factoryCache->has('method')) {
            $this->factoryCache->set('method', new Method());
        }

        return $this->factoryCache->get('method');
    }

    public function createUrl()
    {
        if (!$this->factoryCache->has('url')) {
            $this->factoryCache->set('url', new UrlFromPath());
        }

        return $this->factoryCache->get('url');
    }
}
