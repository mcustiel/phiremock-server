<?php
/**
 * This file is part of ExpectationPhiremock.
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

namespace Mcustiel\Phiremock\Server\Model;

use Mcustiel\Phiremock\Domain\Expectation;

interface ExpectationStorage
{
    public function addExpectation(Expectation $expectation): void;

    public function clearExpectations(): void;

    /** @return \Mcustiel\Phiremock\Domain\Expectation[] */
    public function listExpectations(): array;
}
