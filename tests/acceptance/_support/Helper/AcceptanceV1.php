<?php

/**
 * This file is part of phiremock-codeception-extension.
 *
 * phiremock-codeception-extension is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * phiremock-codeception-extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phiremock-codeception-extension.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Helper;

class AcceptanceV1 extends \Codeception\Module
{
    public function writeDebugMessage(string $message): void
    {
        $this->debug($message);
    }

    public function getPhiremockRequest(array $request): array
    {
        unset($request['request']['jsonPath']);
        return $request;
    }

    public function getPhiremockResponse(string $jsonResponse): string
    {
        $parsedExpectations = json_decode($jsonResponse, true);
        if (json_last_error() !== \JSON_ERROR_NONE) {
            return $jsonResponse;
        }

        foreach ($parsedExpectations as &$parsedExpectation) {
            unset($parsedExpectation['request']['jsonPath']);
        }

        return json_encode($parsedExpectations);
    }
}
