## AS2 Symfony Bundle

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
In order to get the partner info, and keep the system as data-store agnostic as possible, it is up to the implementer to create the Partner Provider Service.  This service must implement the `TechData\AS2SecureBundle\Interfaces\PartnerProvider` interface and must be a public service (it is by default).

The ID of this service must be provided as a parameter.  (See below).

### Required Parameters
There are two required parameters which must be filled out.

-  `tech_data_as2_secure.factory.adapter.bin_location` - This is the real folder location where the `AS2Secure.jar` can be found.
- `tech_data_as2_secure.partner_provider.service_id` - This is the service ID of the partner provider service which must be supplied by the implementer, and must implement the `TechData\AS2SecureBundle\Interfaces\PartnerProvider` interface.