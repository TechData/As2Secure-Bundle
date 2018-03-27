## AS2 Symfony Bundle

Installation
============

Add the bunde to your `composer.json` file:

```javascript
require: { 
    // ...
    "techdata/as2secure-bundle": "^0.1.0@dev"
}
```

Or install directly through composer with:

```shell
composer require techdata/as2secure-bundle dev-master
```

Register the bundle with your kernel:

```php
// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new TechData\AS2SecureBundle\TechDataAS2SecureBundle(),
    // ...
);
```

add to routing.yml
```yml
_tech_data:
    resource: "@TechDataAS2SecureBundle/Resources/config/routing.xml"
```

### Prior Art
The contents of this library are largely based on the work done by Sebastien Malot on the AS2Secure library.  The original can be found at [http://www.as2secure.com/](http://www.as2secure.com/ "www.as2secure.com").  In accordance with the license associated to that library, we continue to follow the LGPL license.

The current library is a heavily refactored and extended version of that original library.

### Enhancments
There have been several enhancements made to the library as part of the refactoring.  The most obvious is the use of event dispatching and dependency injection throughout the library.

### Events
There are 4 events which are raised against the global event dispatcher in Symfony.  They are outlined in `TechData\AS2SecureBundle\Interfaces\Events`.  They expose the event objects found in `TechData\AS2SecureBundle\Events`.

- `tech_data_as2_secure.event.log`
- `tech_data_as2_secure.event.error`
- `tech_data_as2_secure.event.message_received`
- `tech_data_as2_secure.event.message_sent`

### Sending Messages
To send a message you leverage the `tech_data_as2_secure.handler.as2` service, which implements `TechData\AS2SecureBundle\Interfaces\MessageSender` to call `sendMessage`.  An event is fired when the message is successfully sent.  If no exception is thrown, the message was successfully sent.

### Receiving Messages
There is an endpoint which is enabled an located at `/edi/as2/in` by default.  This can be overridden to put the route wherever you want.

### Partner Provider Service
For add the new provider un create the new class for implement TechData\AS2SecureBundle\Models\Partner\ParterInterface
And create the service wioth tag tech_data_as2_partner
#### PHP class
```php
<?php

namespace AppBundle\Partner;

use TechData\AS2SecureBundle\Models\Partner;
use TechData\AS2SecureBundle\Interfaces\PartnerInterface;

/**
 * Class myCompanyAS2Partner
 * @package AppBundle\Partner
 */
class myCompanyAS2Partner implements PartnerInterface
{
    private $root_dir;

    /**
     * AwsPartner constructor.
     * @param $root_dir
     */
    public function __construct($root_dir)
    {
        $this->root_dir = $root_dir;
    }

    /**
     * @return array
     */
    public function getData() : array
    {

        return [
            'is_local' => true,
            'name'     => 'mycompanyAS2',
            'id'       => $this->getId(),
            'email'    => 'info@mendelson.de',
            'comment'  => '',

            // security
            'sec_pkcs12'               => $this->root_dir.'/../demo/mycompanyAS2/key1.p12',
            'sec_pkcs12_password'      => 'test',

            'sec_signature_algorithm'  => Partner::SIGN_SHA1,
            'sec_encrypt_algorithm'    => Partner::CRYPT_3DES,

            'send_url'                 => 'http://loaclhost/edi/as2/in',

            // notification process
            'mdn_request'              => Partner::ACK_SYNC,
        ];
    }
    /**
     * @return string
     */
    public function getId(): string
    {
        return 'mycompanyAS2';
    }

}
```
####Service
```yml
    app.partner.mendelson:
        class: AppBundle\Partner\MendelsonPartner
        arguments: ['%kernel.root_dir%']
        tags:
            - { name: tech_data_as2_partner}
```

### Required Parameters
There are two required parameters which must be filled out.

-  `tech_data_as2_secure.factory.adapter.bin_location` - This is the real folder location where the `AS2Secure.jar` can be found.
