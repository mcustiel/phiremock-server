<?php

namespace Helper;

class AcceptanceV2 extends \Codeception\Module
{
    public function getPhiremockRequest(array $request): array
    {
        if (isset($request['request']['method'])) {
            $request['request']['method'] = ['isSameString' => $request['request']['method']];
        }

        return array_merge(['version' => '2'], $request);
    }

    public function getPhiremockResponse(string $response): string
    {
        return preg_replace(
            ['~^(\[\{)~', '~("method"\:)"(GET|POST|PUT|DELETE|PATCH|HEAD|OPTIONS|FETCH)"~i'],
            ['$1"version":"2",', '$1{"isSameString":"$2"}'],
            $response
        );
    }
}
