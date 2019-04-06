<?php

namespace Mcustiel\Phiremock\Server\Http\Matchers;

use Mcustiel\Phiremock\Server\Factory\Factory;
use Mcustiel\Phiremock\Server\Utils\DataStructures\StringObjectArrayMap;

class MatcherFactory
{
    /** @var StringObjectArrayMap */
    private $factoryCache;
    /** @var Factory */
    private $factory;

    public function __construct(Factory $factory)
    {
        $this->factoryCache = new StringObjectArrayMap();
        $this->factory = $factory;
    }

    public function createCaseInsensitiveEquals()
    {
        if (!$this->factoryCache->has('caseInsensitiveEquals')) {
            $this->factoryCache->set('caseInsensitiveEquals', new CaseInsensitiveEquals());
        }

        return $this->factoryCache->get('caseInsensitiveEquals');
    }

    public function createEquals()
    {
        if (!$this->factoryCache->has('equals')) {
            $this->factoryCache->set('equals', new Equals());
        }

        return $this->factoryCache->get('equals');
    }

    public function createContains()
    {
        if (!$this->factoryCache->has('contains')) {
            $this->factoryCache->set('contains', new Contains());
        }

        return $this->factoryCache->get('contains');
    }

    public function createJsonObjectContains()
    {
        if (!$this->factoryCache->has('jsonObjectEquals')) {
            $this->factoryCache->set(
                'jsonObjectEquals',
                new JsonObjectsEquals($this->factory->createLogger())
            );
        }

        return $this->factoryCache->get('jsonObjectEquals');
    }

    public function createRegExp()
    {
        if (!$this->factoryCache->has('regexp')) {
            $this->factoryCache->set('regexp', new RegExp());
        }

        return $this->factoryCache->get('regexp');
    }
}
