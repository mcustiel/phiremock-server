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

namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Common\Utils\ExpectationToArrayConverter;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;

class ListExpectationsAction implements ActionInterface
{
    /** @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage */
    private $storage;
    /** @var ExpectationToArrayConverter */
    private $converter;

    /**
     * @param ExpectationStorage $storage
     */
    public function __construct(ExpectationStorage $storage, ExpectationToArrayConverter $converter)
    {
        $this->storage = $storage;
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData, $argument = null)
    {
        $list = [];
        foreach ($this->storage->listExpectations() as $expectation) {
            $list[] = $this->converter->convert($expectation);
        }
        $jsonList = json_encode($list);
        $transactionData->setResponse(
            $transactionData->getResponse()
            ->withBody(new StringStream($jsonList))
            ->withHeader('Content-type', 'application/json')
        );
    }
}
