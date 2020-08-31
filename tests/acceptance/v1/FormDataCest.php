<?php

namespace Mcustiel\Phiremock\Server\Tests\V1;

use AcceptanceTester;

class FormDataCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function creationWithOneFormFieldUsingEqualToTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks one form field using isEqualTo');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'headers'  => ['Content-Type' => ['isEqualTo' => 'application/x-www-form-urlencoded']],
                    'formData' => ['name' => ['isEqualTo' => 'potato']],
                ],
                'response' => [
                    'statusCode' => 418,
                ],
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals($I->getPhiremockResponse(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":null,"body":null,"headers":{"Content-Type":{"isEqualTo":"application\/x-www-form-urlencoded"}},"formData":{"name":{"isEqualTo":"potato"}}},'
            . '"response":{"statusCode":418,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPOST('/it/does/not/matter', ['name' => 'potato']);
        $I->seeResponseCodeIs(418);
    }
}
