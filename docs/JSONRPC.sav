# Use JSON RPC MCP tool to setup account

Start with the http client preparation:

```php
//prepare the http client from the jsonrpc pack
$httpClient = new \JsonRPC\HttpClient(
    'https://sais.wip/tools', //you wanna use your own URL here
);

//add curl proxy option (useful for debugging or local development)
$httpClient->addOption(
    CURLOPT_PROXY,
    'http://127.0.0.1:7080'
);

$client = new \JsonRPC\Client('https://sais.wip/tools', false, $httpClient);
```

List all tool available methods:

```php
//call for tools list
$result = $client->execute('tools/list');
```

Call the account setup method (AccountSetup model is defined in the package) :

```php
$arguments = (array) new AccountSetup('userRootName', 1400); // Adjust the parameters as needed , cast to array for JSON RPC

$result = $client->execute('tools/call', [
    'name' => 'create_account', // The name of the tool method to call (reference on the tool list)
    'arguments' => $arguments,
]);

```

# Adding Models : 

Each model needs OpenApi validation attributes and Symfony validation attributes.
```php
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
#[OA\Schema(required: ['root', 'approx'])]
class AccountSetup
{
....

```

Fields validation can be done with Symfony validation attributes:

```php
#[Assert\NotBlank]
#[Assert\Length(min: 3, max: 50)]
#[OA\Property(description: 'The root code that prefixes the file storage', type: 'string', maxLength: 50, minLength: 3, nullable: false)]
public string $root,