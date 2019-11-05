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

namespace Mcustiel\Phiremock\Server\Model\Implementation;

use Mcustiel\Phiremock\Domain\Options\ScenarioName;
use Mcustiel\Phiremock\Domain\Options\ScenarioState;
use Mcustiel\Phiremock\Domain\ScenarioStateInfo;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;

class ScenarioAutoStorage implements ScenarioStorage
{
    /** @var ScenarioStateInfo[] */
    private $scenarios;

    public function __construct()
    {
        $this->scenarios = [];
    }

    public function setScenarioState(ScenarioStateInfo $scenarioState): void
    {
        $this->scenarios[$scenarioState->getScenarioName()->asString()] = $scenarioState->getScenarioState();
    }

    public function getScenarioState(ScenarioName $name): ScenarioState
    {
        $nameString = $name->asString();
        if (!isset($this->scenarios[$nameString])) {
            $this->scenarios[$nameString] = new ScenarioState(self::INITIAL_SCENARIO);
        }

        return $this->scenarios[$nameString];
    }

    public function clearScenarios(): void
    {
        $this->scenarios = [];
    }
}
