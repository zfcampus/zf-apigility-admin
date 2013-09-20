TODO
====

RestEndpoint Model
------------------

- [X] trigger an event in fetch() that short-circuits if a listener returns a
  RestEndpointEntity, and return it if stopped().
- [X] Test and write delete() functionality

DB-Connected Model
------------------

- [X] Test createService() functionality
- [X] Test and write a listener for RestEndpointModel::fetch that will check for the
  ResourceClass inside the zf-apigility configuration. The event should pass
  both the discovered RestEndpointEntity as well as the application
  configuration. If db-connected configuration is found, create a
  DBConnectedRestEndpointEntity and return it.
- [X] Test and write updateService() functionality
- [X] Test and write delete() functionality

RestModel Resource
------------------

- [X] In create(), detect the type of RestEndpoint being sent based on features. If the data
  includes a `table_name`, pass it on to the DbConnectedRestEndpointModel.
- [X] In patch(), first fetch() the RestEndpoint, and based on the type, determine
  which model to pass it to.
- [X] Implement delete(), and do it similar to patch().
