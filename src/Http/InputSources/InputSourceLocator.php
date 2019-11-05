<?php

namespace Mcustiel\Phiremock\Server\Http\InputSources;

use Mcustiel\Phiremock\Server\Config\InputSources;

class InputSourceLocator
{
    const INPUT_SOURCE_FACTORY_METHOD_MAP = [
        InputSources::BODY   => 'createBody',
        InputSources::URL    => 'createUrl',
        InputSources::HEADER => 'createHeader',
        InputSources::METHOD => 'createMethod',
    ];

    /** @var InputSourceFactory */
    private $factory;

    public function __construct(InputSourceFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $inputSourceIdentifier
     *
     * @throws \InvalidArgumentException
     *
     * @return InputSourceInterface
     */
    public function locate($inputSourceIdentifier)
    {
        if (InputSources::isValidInputSource($inputSourceIdentifier)) {
            return $this->factory->{self::INPUT_SOURCE_FACTORY_METHOD_MAP[$inputSourceIdentifier]}();
        }
        throw new \InvalidArgumentException(sprintf('Trying to get data from %s. Which is not a valid input source.', var_export($inputSourceIdentifier, true)));
    }
}
