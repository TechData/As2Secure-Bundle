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

