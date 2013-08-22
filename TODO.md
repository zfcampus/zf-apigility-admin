TODO
====

Code-Connected tasks
--------------------

### RPC

- Service name (Required)
  
  Will be used to name the route and the controller.

- Route (Required; has default)

  The route to match. By default, it would be a normalized version of the service name.
  Route name will match the service name. The dialog should not allow submitting
  if another route with that name exists.

- HTTP methods allowed (Required; has default)

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

  The route name will be the normalized module name, a hyphen, and the service
  name: `social-status`, `developer_garden-update`, etc.

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

### REST

- Resource name (Required)

  Will be used to name the route, resource, and controller.

- Identifier name (Required; has default)

  Will be used in the route and with HAL configuration. Defaults to normalized
  resource name (lowercase, underscore-separated) + `_id`.

- Route (Required; has default)

  The route to match. Defaults to `/normalized_resource_name[/:normalized_resource_name_id]`.

  Route name will match resource name. The dialog should not allow submitting if
  another route with that name exists.

- HTTP methods allowed for Collections (Required; has default)

  Defaults to GET and POST.

- HTTP methods allowed for Resource (Required; has default)

  Defaults to GET, PUT, PATCH, and DELETE.

- Collection name (Optional; has default)

  Defaults to normalized resource name (lowercase, underscore-separated)

- Collection query whitelist (Optional)

  Allow text inputs for providing the names of query parameters that will be
  passed on to the resource when dealing with a collection. Examples: "sort",
  "filter", etc.

- Page size (Optional; has default)

  Number of results to return per page if pagination is enabled in the
  collection; defaults to 25.

- Page size parameter (Optional; has default)

  The query parameter that specifies the current page of a paginated collection;
  defaults to "page".

- Content negotiation (Optional; has default)

  Capture the content-negotiation we will allow for creating a representation.
  Should have a drop-down for already defined selectors. E.g.: "Json",
  "HalJson".

  The default view model negotiation selector will be `HalJson`.

  Additionally, allow defining "accept" and "content-type" whitelists; this
  would be a radio to enable each feature, with text input to capture each
  mediatype the user wants to add to the whitelist.

#### What to generate

- Route configuration.

  A Segment route. The controller matched would be the generated controller,
  and the action would be `camelCasedService`.

  The route name will be the normalized module name, a hyphen, and the
  normalized resource name: `social-status`, `developer_garden-status_update`,
  etc.

- A `ZF\Rest\AbstractResource` implementation.

  This should be named in the module's namespace, and after the resource name;
  e.g., if the resource name is "Status" and the module "Social", the class name
  would be `Social\StatusResource`. It would extend `AbstractResource` and copy
  the method body implementation from the abstract class to ensure we have
  something that works immediately.

- A POPO object describing the resource.

  This would be an empty object named after the resource; e.g., if the resource
  name is "Status" and the module "Social", the class name would be
  `Social\Status`. It would have an empty body.

- A POPO object describing the collection and extending the paginator.

  This would be an empty object named after the resource, with a "Collection"
  suffix; e.g., if the resource name is "Status" and the module "Social", the
  class name would be `Social\StatusCollection`. It will extend
  `Zend\Paginator\Paginator`. The body will be empty.

- `zf-rest` configuration

  A controller/configuration pair, with all the configuration options we
  collect:

  ```php
  'zf-rest' => array(
      'Api\Controller\Status' => array(
          'listener'                   => 'Api\StatusResource',
          'route_name'                 => 'api-status`,
          'identifier_name'            => 'status_id',
          'collection_name'            => 'status',
          'collection_http_options'    => array('GET', 'POST'),
          'collection_query_whitelist' => array('sort'),
          'resource_http_options'      => array('GET', 'PUT', 'PATCH', 'DELETE'),
          'page_size'                  => 25,
          'page_size_param'            => 'page',
      ),
  ),
  ```

- `zf-content-negotiation` configuration

  Create a `controllers` key/value pair of controller service name => selector.

  Create `accept-whitelist` and/or `content-type-whitelist` key/value pairs of
  controller service name => array of mediatypes

- `zf-hal` configuration (potentially)

  To specify `metadata_map` entries for both the resource and collection
  objects. Still TBD.
