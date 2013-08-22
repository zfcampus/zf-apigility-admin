TODO
====

Code-Connected tasks
--------------------

### RPC

- Service name (Required)
  
  Will be used to name the route and the controller.

- Route (Required)

  The route to match. By default, it would be a normalized version of the service name.
  Route name will match the service name. The dialog should not allow submitting
  if another route with that name exists.

- HTTP methods allowed (Optional)

  What HTTP methods will this endpoint allow/handle? (**Note**: at this time,
  we have no way to handle this!)

  Default to GET. Allow multiple methods to be checked.

- Content negotiation (Optional)

  Capture the content-negotiation we will allow for creating a representation.
  Should have a drop-down for already defined selectors. E.g.: "Json",
  "HalJson".

  Additionally, allow defining "accept" and "content-type" whitelists; this
  would be a radio to enable each feature, with text input to capture each
  mediatype the user wants to add to the whitelist.

#### What to generate

- A controller class with a method for processing the incoming request.

  For now, this could extend the `AbstractActionController`, with the method
  `serviceNameAction`, a camelCased version of the service name.

- Controller invokable configuration.

  Define a controller invokable entry for the controller service name pointing to
  the new controller class.

- Route configuration.

  A Segment route. The controller matched would be the generated controller,
  and the action would be `camelCasedService`.

- `zf-rpc` configuration.

  For now, just a controller service name/method pair:

  ```php
  'zf-rpc' => array(
      'Api\Controller\Foo' => array(
          'http_methods' => array('GET', 'PATCH'),
          'route_name'   => 'foo',
      ),
  ),
  ```

- `zf-content-negotiation` configuration

  Create a `controllers` key/value pair of controller service name => selector.

  Create `accept-whitelist` and/or `content-type-whitelist` key/value pairs of
  controller service name => array of mediatypes
