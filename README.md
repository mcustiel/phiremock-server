# Phiremock Server

Phiremock is a mocker and stubber of HTTP services, it allows software developers to mock HTTP requests and setup responses to avoid calling real services during development, and is particulary useful during acceptance testing when expected http requests can be mocked and verified. Any HTTP service (i.e.: REST services) can be mocked and stubbed with Phiremock.

Phiremock is heavily inspired by [WireMock](http://wiremock.org/), but does not force you to have a java installation in your PHP development environment. The full functionality of Phiremock is detailed in the following list:
* Allows to mock http request based in method, headers, url, body content and form fields. 
* Allows to match expectations using several comparison functions. 
* REST interface to setup.
* Stateful and stateless mocking through scenarios.
* Network latency simulation.
* Priorizable expectations for cases in which more than one matches the request. If more than one expectation matches the request and no priorities were set, the first match is returned.
* Allows to verify the amount of times a request was done.
* Allows to load default expectations from json files in a directory tree.
* Proxy requests to another URL as they are received.
* Fill the response body using data from the request.
* Integration to codeception through [phiremock-codeception-extension](https://github.com/mcustiel/phiremock-codeception-extension) and [phiremock-codeception-module](https://github.com/mcustiel/phiremock-codeception-module).
* Client with nice API supporting all functionalities: [phiremock-client](https://github.com/mcustiel/phiremock-client)

[![Version](https://img.shields.io/packagist/v/mcustiel/phiremock-server)](https://packagist.org/packages/mcustiel/phiremock-server)
[![Build Status](https://scrutinizer-ci.com/g/mcustiel/phiremock-server/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mcustiel/phiremock-server/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mcustiel/phiremock-server/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mcustiel/phiremock-server/?branch=master)
[![Packagist Downloads](https://img.shields.io/packagist/dm/mcustiel/phiremock-server)](https://packagist.org/packages/mcustiel/phiremock-server)

## Installation

### Default installation through composer

```json
    "require-dev": {
        "mcustiel/phiremock-server": "^1.0",
        "guzzlehttp/guzzle": "^6.0"
    }
```
Phiremock Server requires guzzle client v6 to work. This dependency can be avoided and you can choose any psr18-compatible http client and overwrite Phiremock Server's factory to provide it.

### Phar
You can also download the standalone server as a phar from [here](https://github.com/mcustiel/phiremock-server/releases/download/v1.1.2/phiremock.phar).

## Running

Execute `./vendor/bin/phiremock`.

### Command line arguments

* **--ip|-i <interface>** - Network interface where to listen for http connections. Default: 0.0.0.0
* **--port|-p <port>** - Port where to listen for http connections. Default: 8086
* **--expectations-dir|-e <directory-name>** - Directory where to search for predefined static expectations. Default: [HOME_PATH]/.phiremock/expectations
* **--factory-class|f <fully-qualified-class-name>** - Factory class to use to create the objects needed by phiremock server. Default: Default internal factory class.
* **--certificate|t <path-to-certificate-file>** - Certificate file to enable phiremock to listen for secure connections (https).
* **--certificate-key|k <path-to-certificate-key-file>** - Path to the certificate key.
* **--cert-passphrase|s <pasphrase>** - Passphrase to use if the certificate is encrypted.
* **--debug|-d** - Flag to activate the debug mode.

**Note:** Static expectations saved in files are very useful if you use phiremock in your development environment. For testing, can be more useful to setup expectations on the fly. 

**Note:** When a certificate is added, phiremock-server will only listen for secure connections.

## Configuration
You can statically specify phiremock server's configuration in the .phiremock file. It looks as following:

```php
<?php return [
    'port'             => 8086,
    'ip'               => '0.0.0.0',
    'expectations-dir' => $_SERVER['HOME'] . '/.phiremock/expectations',
    'debug'            => false,
    'factory-class'    => '\\My\\Namespace\\FactoryClass',
    'certificate'      => null,
    'certificate-key'  => null,
    'certificate-passphrase' => null,
];
```

This file will be searched as following, the first one found is the one to use:
1. PROJECT_ROOT_DIR/.phiremock (if installed under /vendor)
2. PROJECT_ROOT_DIR/.phiremock.dist (if installed under /vendor)
3. PHIREMOCK_ROOT_DIR/.phiremock (if pulled standalone)
4. PHIREMOCK_ROOT_DIR/.phiremock.dist (if pulled standalone)
5. $CWD/.phiremock
5. $CWD/.phiremock.dist
6. $HOME/.phiremock/config
7. .phiremock (uses php's include path)
8. .phiremock.dist(uses php's include path)

**Note:** The command line arguments have higher priority over the options in the config file, so they will override them if provided.

### Overwriting the factory class

If guzzle client v6 is provided as a dependency no extra configuration is needed. If you want to use a different http client you need to provide it to phiremock server as a psr18-compatible client.
For instance, if you want to use guzzle client v7 you need to extend phiremock server's factory class:

```php
<?php
namespace My\Namespace;

use Mcustiel\Phiremock\Client\Factory;
use GuzzleHttp;
use Psr\Http\Client\ClientInterface;

class FactoryWithGuzzle7 extends Factory
{
    public function createRemoteConnection(): ClientInterface
    {
        return new GuzzleHttp\Client();
    }
}
```
Then provide the fully qualified class name to phiremock-server using the command line options or the configuration file.

**Note:** This will only work if phiremock is instaled through composer, since it will use the same vendor folder and autoloader as your project. Also if you pull phiremock repo and extend the composer.json file.
 
## How does it work?

Phiremock will allow you to create a stubbed version of some external service your application needs to communicate to. That can be used to avoid calling the real application during development or to setup responses to expected requests. To do this, you need to trick your application to request phiremock server when on development stage or testing stage.

### Setup your application's configuration

First of all you need to setup the config for the different environments for your application. For instance:

```json
    // config/production.json
    {
        "external_service": "https://service.example.com/v1/"
    }
```

```json
    // config/development.json
    {
        "external_service": "http://localhost:8080/example_service_dev/"
    }
```

```json
    // config/acceptance.json
    {
        "external_service": "http://localhost:8080/example_service_test/"
    }
```

### Configure expectations

Then, using phiremock's REST interface, expectations can be configured, specifying the response to send for a given request. A REST expectation resource for phiremock looks like this:

```json
{
    "version": "2",
    "scenarioName": null,
    "on": {
        "scenarioStateIs": null,
        "method": { "isSameString": "GET" },
        "url": { "matches": "~^/images/~"},
        "body": null,
        "headers" : null,
        "formData": null
    },
    "then": {
        "delayMillis": 100,
        "newScenarioState": null,
        "response": {
            "statusCode": 200,
            "body": "phiremock.base64:__BASE64_ENCODED_IMAGE__",
            "headers": { "Content-Type": "image/x-icon" }
        }
    },
    "priority": 0
}
```

The same format can be used in expectation files saved in the directory tree specified by the **--expectations-dir** argument of the CLI. For Phiremock Server to be able to load them, each file should have `.json` extension. For instance: `match-all-images.json` for the previous example.

## Features

### Create an expectation 
To create previous response from code the following should be used:

```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "on": {
        "method": { "isSameString": "GET" },
        "url": { "isEqualTo" : "/example_service/some/resource" },
    },
    "then": {
        "response": {
            "statusCode": 200,
            "body": "{\"id\": 1, \"description\": \"I am a resource\"}",
            "headers": {
                "Content-Type": "application/json"
            }
        }
    }
}
```

### Clear expectations
After a test runs, all previously configured expectations can be deleted so they don't affect the execution of the next test:

```
DELETE /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
```

### List all expectations
If you want, for some reason, list all created expectations. A convenient endpoint is provided:

```
GET /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host

```

### Verify amount of requests
To know how much times a request was sent to Phiremock Server, for instance to verify after a feature execution in a test, there is a helper method too:

```
POST /__phiremock/executions HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "request": {
        "method": "GET",
        "url": {
            "isEqualTo" : "/example_service/some/resource"
        }
    }
}
```

### Search executed requests
To search for the list of requests to which Phiremock Server responded:

```
PUT /__phiremock/executions HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "request": {
        "method": "GET",
        "url": {
            "isEqualTo" : "/example_service/some/resource"
        }
    }
}
```

### Reset requests log
To reset the requests counter to 0, Phiremock Server also provides an endpoint: 

```
DELETE /__phiremock/executions HTTP/1.1
Host: your.phiremock.host
```

### Reset Phiremock to its initial state
This call will clean the requests list, the scenarios, delete all configured expectations and reload the static ones defined in the expectations directory.

```
POST /__phiremock/reset HTTP/1.1
Host: your.phiremock.host
```

## Cool stuff

### Send binary body in response
Binary contents can be sent as a response body too by encoding it as base64 in the expectation json.

```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "on": {
        "method": { "isSameString": "GET" },
        "url": { "isEqualTo" : "/example_service/photo.jpg" },
    },
    "then": {
        "response": {
            "statusCode": 200,
            "body": "phiremock.base64:HERE_THE_BASE64_ENCODED_IMAGE",
            "headers": {
                "Content-Type": "image/jpeg"
            }
        }
    }
}
```

### Priorities
Phiremock accepts multiple expectations that can match the same request. If no priorities are set, it will match the first expectation created but, if you need to give high priority to some request, you can do it easily.

Suppose you have the next two expectations configured:

```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "on": {
        "method": { "isSameString": "GET" },
        "url": { "isEqualTo": "/example_service/some/resource"}
    },
    "then": {
        "response": {
            "statusCode": 200,
            "body": "<resource id=\"1\" description=\"I am a resource\"/>",
            "headers": [ "Content-Type": "text/xml" ]
        }
    }
}
```

```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "on": {
        "method": { "isSameString": "GET" },
        "url": { "isEqualTo": "/example_service/some/resource"},
        "headers": {
            "Accept": {"isEqualTo": "application/json"}
        }
    },
    "then": {
        "response": {
            "statusCode": 200,
            "body": "{\"id\": 1, \"description\": \"I am a resource\"}",
            "headers": [ "Content-Type": "application/json" ]
        }
    },
    "priority": 10    
}
```

In the previous example, both expectations will match a request with url equal to: `/example_service/some/resource` and method `GET`. But Phiremock will give higher priority to the one with the Accept header equal to `application/json`.
Default priority for an expectation is 0, the higher the number, the higher the priority.

### Stateful behaviour
If you want to simulate a behaviour of the service in which a response depends of a state that was set in a previous request. You can use scenarios to create a stateful behaviour.

```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "scenarioName": "saved",
    "on": {
        "scenarioStateIs": "Scenario.START",
        "method": { "isSameString": "POST" },
        "url": { "isEqualTo": "/example_service/some/resource"},
        "body": {"isEqualTo" : "{"\id": \"1\", \"name\" : \"resource\"}"},
        "headers": {
            "Accept": {"Content-Type": "application/json"}
        }
    },
    "then": {
        "newScenarioState": "RESOURCE_SAVED",
        "response": {
            "statusCode": 201,
            "body": "{"\id": \"1\", \"name\" : \"resource\"}",
            "headers": [ "Content-Type": "application/json" ]
        }
    }
}
```

```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "scenarioName": "saved",
    "on": {
        "scenarioStateIs": "RESOURCE_SAVED",
        "method": { "isSameString": "POST" },
        "url": { "isEqualTo": "/example_service/some/resource"},
        "body": {"isEqualTo" : "{"\id": \"1\", \"name\" : \"resource\"}"},
        "headers": {
            "Accept": {"Content-Type": "application/json"}
        }
    },
    "then": {
        "response": {
            "statusCode": 409,
            "body": "Resource with id = 1 was already created"
        }
    }
}
```

In this case, the first time that Phiremock Server receives a request matching the expectation, the first one will match and it will change the state of the `saved` scenario. From the second time the same request is executed, the second expectation will be matched.
If you want after the second call, to go back to the initial state just add `"newScenarioState": "Scenario.START"` to the `then` section.

To reset all scenarios to the initial state (Scenario.START) use this simple method from the client: 

```
DELETE /__phiremock/scenarios HTTP/1.1
Host: your.phiremock.host
```

To define a scenario state in any moment:

```php
PUT /__phiremock/scenarios HTTP/1.1
Host: your.phiremock.host

{
    "scenarioName": "saved",
    "scenarioState": "Scenario.START"
}
```

### Netwok latency simulation
If you want to test how your application behaves on, for instance, a timeout; you can make Phiremock to delay the response of your request as follows.

```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "on": {
        "method": { "isSameString": "GET" },
        "url": { "isEqualTo": "/example_service/some/resource"},
        "headers": {
            "Accept": {"isEqualTo": "application/json"}
        }
    },
    "then": {
        "delayMillis": 30000,
        "response": {
            "statusCode": 200
        }
    }
}
```
This will cause Phiremock Server to wait 30 seconds before sending the response.

### Proxy
It could be the case that a mock is not needed for certain call. For this specific case, Phiremock provides a proxy feature that will pass the received request unmodified to a configured URI and return the real response from it. It can be configured as folows:

```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "on": {
        "method": { "isSameString": "POST" },
        "url": { "isEqualTo": "/example_service/some/resource"},
        "headers": {
            "Accept": {"isEqualTo": "application/json"}
        }
    },
    "then": {
        "proxyTo": "http://your.real.service/some/path/script.php"
    }
}
```

In this case, Phiremock will POST `http://your.real.service/some/path/script.php` with the configured body and header and return it's response.

### Compare JSON objects
Phiremock supports comparing strict equality of json objects, in case it's used in the API.
The comparison is object-wise, so it does not matter that indentation or spacing is different.

```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "on": {
        "method": { "isSameString": "GET" },
        "body": { "isSameJsonObject": "{\"some\": \"json\", \"here\": [1, 2, 3]}"},
        "headers": {
            "Content-Type": {"isEqualTo": "application/json"}
        }
    },
    "then": {
        "response": {
            "statusCode": 201,
            "body": "{\"id\": 1}"
        }
    }
}
```

### JSON Path Conditions
Phiremock allows filtering requests by checking values in specific JSON paths in the request body. This is particularly useful when you need to match requests based on nested JSON structures:
```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json
{
    "version": "2",
    "on": {
        "method": { "isSameString": "POST" },
        "url": { "isEqualTo": "/api/users" },
        "jsonPath": {
            "user.address.zipCode": { "isEqualTo": "12345" },
            "user.phones.0.value": { "isEqualTo": "+1234567890" }
        }
    },
    "then": {
        "response": {
            "statusCode": 201,
            "body": "Created"
        }
    }
}
```
In this example, Phiremock will check if the request body contains a JSON object with path `user.address.zipCode` equal to `"12345"` and path `user.phones.0.value` equal to `"+1234567890"`. The `jsonPath` condition supports all the standard [matchers](#list-of-condition-matchers). You can use `jsonPath` together with other request conditions like method, url, headers etc. to create more specific matches. The path notation uses dot syntax to navigate through the JSON structure, including array access where numeric indices are specified directly in the path (e.g. `phones.0.value` to access the first element of the phones array).

### Generate response based in request data
It could happen that you want to make your response dependent on data you receive in your request. For this cases you can use regexp matching for request url and/or body, and access the subpatterns matches from your response body specification using `${body.matchIndex}` or `${url.matchIndex}` notation.

```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "on": {
        "method": { "isSameString": "GET" },
        "url": {"matches": "~^/example_service/(\w+)/?id=(\d+)~"}
        "body": { "matches": "~\{\"name\" : \"([^\"]+)\"\}~" },
        "headers": {
            "Content-Type": {"isEqualTo": "application/json"}
        }
    },
    "then": {
        "response": {
            "statusCode": 200,
            "body": "The resource is ${url.1}, the id is ${url.2} and the name is ${body.1}",
            "headers": {"X-id": "id is ${url.2}"}
        }
    }
}
```

Also retrieving data from multiple matches is supported:


```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "on": {
        "method": { "isSameString": "GET" },
        "url": {"matches": "~/peoples-brothers-list/json~"}
        "body": { "matches": "%\"name\"\s*:\s*\"([^\"]*)",\s*\"brothers\"\s*:\s*(\d+)%" },
        "headers": {
            "Content-Type": {"isEqualTo": "application/json"}
        }
    },
    "then": {
        "response": {
            "statusCode": 200,
            "body": "${body.1} has ${body.2} brothers, ${body.1.2} has ${body.2.2} brothers, ${body.1.3} has ${body.2.3} brothers"
        }
    }
}
```

This is also supported to generate the proxy url as shown in the following example:

```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "on": {
        "method": { "isSameString": "GET" },
        "url": {"matches": "~^/example_service/(\w+)~"}
        "headers": {
            "Content-Type": {"isEqualTo": "application/json"}
        }
    },
    "then": {
        "proxyTo": "https://some.other.service/path/${url.1}"
    }
}
```

### Conditions based in form data
For requests encoded with `application/x-www-form-urlencoded` and specifying this Content Type in the headers. Phiremock Server is capable of execute conditions on the values of the form fields.

```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "version": "2",
    "on": {
        "method": { "matches": "~POST|PUT~" },
        "url": {"isEqualTo": "/login-form-handler"}
        "formData": {
            "username": {"isEqualTo": "the_username"},
            "password": {"isEqualTo": "the_password"},
        }
    },
    "then": {
        "response": {
            "statusCode": 200,
            "body": "Login successful"
        }
    }
}
```

### Backwards compatibility
Phiremock Server still supports expectations in the format of Phiremock V1. This should make your migration from Phiremock v1 to Phiremock v1 (phiremock-server + phiremock-client) easier.

```json
{
    "scenarioName": "potato",
    "scenarioStateIs": "Scenario.START",
    "newScenarioState": "tomato",
    "request": {
        "method": "GET",
        "url": {
            "isEqualTo": "/some/thing"
        },
        "headers": {
            "Content-Type": {
                "isEqualTo": "text/plain"
            }
        }
    },
    "response": {
        "statusCode": 200,
        "body": "Hello world!",
        "headers": {
            "Content-Type": "text/plain"
        }
    },
    "priority": 1
}
```

## Appendix

### List of condition matchers:

* **contains:** Checks that the given section of the http request contains the specified string.
* **isEqualTo:** Checks that the given section of the http request is equal to the one specified, case sensitive.
* **isSameString:** Checks that the given section of the http request is equal to the one specified, case insensitive.
* **matches:** Checks that the given section of the http request matches the regular expression specified.
* **isSameJsonObject**: Checks that json received in the request is the same as a given JSON.

### See also

* Phiremock Client: https://github.com/mcustiel/phiremock-client
* Phiremock Codeception Extension: https://github.com/mcustiel/phiremock-codeception-extension
* Examples in tests: https://github.com/mcustiel/phiremock-server/tree/master/tests/acceptance

### Contributing:

Just submit a pull request. Don't forget to run tests and php-cs-fixer first, and write documentation.

### Thanks to:

* Denis Rudoi ([@drudoi](https://github.com/drudoi))
* Henrik Schmidt ([@mrIncompetent](https://github.com/mrIncompetent))
* Nils Gajsek ([@linslin](https://github.com/linslin))
* Florian Levis ([@Gounlaf](https://github.com/Gounlaf))

And everyone who submitted their Pull Requests.
