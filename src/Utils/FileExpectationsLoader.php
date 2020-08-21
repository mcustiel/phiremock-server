<?php
/**
 * This file is part of Phiremock.
 *
 * Phiremock is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Phiremock is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Phiremock.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mcustiel\Phiremock\Server\Utils;

use Mcustiel\Phiremock\Common\Utils\ArrayToExpectationConverterLocator;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Utils\Traits\ExpectationValidator;
use Psr\Log\LoggerInterface;

class FileExpectationsLoader
{
    use ExpectationValidator;

    /** @var \Mcustiel\Phiremock\Common\Utils\ArrayToExpectationConverterLocator */
    private $converterLocator;
    /** @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage */
    private $storage;
    /** @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage */
    private $backup;
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(
        ArrayToExpectationConverterLocator $converterLocator,
        ExpectationStorage $storage,
        ExpectationStorage $backup,
        LoggerInterface $logger
    ) {
        $this->converterLocator = $converterLocator;
        $this->storage = $storage;
        $this->backup = $backup;
        $this->logger = $logger;
    }

    /** @throws \Exception */
    public function loadExpectationFromFile(string $fileName): void
    {
        $this->logger->debug("Loading expectation file $fileName");
        $content = file_get_contents($fileName);
        $data = @json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception(json_last_error_msg());
        }
        $expectation = $this->converterLocator->locate($data)->convert($data);
        $this->validateExpectationOrThrowException($expectation, $this->logger);

        $this->logger->debug('Parsed expectation: ' . var_export($expectation, true));
        $this->storage->addExpectation($expectation);
        // As we have no API to modify expectation, parsed the same object could be used for backup.
        // On futher changes when $expectation modifications are possible something like deep-copy
        // should be used to clone expectation.
        $this->backup->addExpectation($expectation);
    }

    public function loadExpectationsFromDirectory(string $directory): void
    {
        $this->logger->info("Loading expectations from directory $directory");
        $iterator = new \RecursiveDirectoryIterator(
            $directory,
            \RecursiveDirectoryIterator::FOLLOW_SYMLINKS
        );

        $iterator = new \RecursiveIteratorIterator($iterator);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $filePath = $fileInfo->getRealPath();
                if (preg_match('/\.json$/i', $filePath)) {
                    $this->loadExpectationFromFile($filePath);
                }
            }
        }
    }
}
