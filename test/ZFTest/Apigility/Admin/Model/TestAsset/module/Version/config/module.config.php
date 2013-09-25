<?php
return array (
  'router' => 
  array (
    'routes' => 
    array (
      'zend-con.rest.session' => 
      array (
        'type' => 'Segment',
        'options' => 
        array (
          'route' => '/api/session[/:session_id]',
          'defaults' => 
          array (
            'controller' => 'ZendCon\\Rest\\Session\\Controller',
          ),
        ),
      ),
      'zend-con.rest.speaker' => 
      array (
        'type' => 'Segment',
        'options' => 
        array (
          'route' => '/api/speaker[/:speaker_id]',
          'defaults' => 
          array (
            'controller' => 'ZendCon\\Rest\\Speaker\\Controller',
          ),
        ),
      ),
    ),
  ),
  'zf-rest' => 
  array (
    'ZendCon\\Rest\\Session\\Controller' => 
    array (
      'listener' => 'ZendCon\\Rest\\Session\\SessionResource',
      'route_name' => 'zend-con.rest.session',
      'identifier_name' => 'session_id',
      'collection_name' => 'session',
      'resource_http_methods' => 
      array (
        0 => 'GET',
      ),
      'collection_http_methods' => 
      array (
        0 => 'GET',
      ),
      'collection_query_whitelist' => 
      array (
      ),
      'page_size' => '10',
      'page_size_param' => NULL,
      'entity_class' => 'ZendCon\\Rest\\Session\\SessionEntity',
      'collection_class' => 'ZendCon\\Rest\\Session\\SessionCollection',
    ),
    'ZendCon\\Rest\\Speaker\\Controller' => 
    array (
      'listener' => 'ZendCon\\Rest\\Speaker\\SpeakerResource',
      'route_name' => 'zend-con.rest.speaker',
      'identifier_name' => 'speaker_id',
      'collection_name' => 'speaker',
      'resource_http_methods' => 
      array (
        0 => 'GET',
      ),
      'collection_http_methods' => 
      array (
        0 => 'GET',
      ),
      'collection_query_whitelist' => 
      array (
      ),
      'page_size' => 25,
      'page_size_param' => NULL,
      'entity_class' => 'ZendCon\\Rest\\Speaker\\SpeakerEntity',
      'collection_class' => 'ZendCon\\Rest\\Speaker\\SpeakerCollection',
    ),
  ),
  'zf-content-negotiation' => 
  array (
    'controllers' => 
    array (
      'ZendCon\\Rest\\Session\\Controller' => 'HalJson',
      'ZendCon\\Rest\\Speaker\\Controller' => 'HalJson',
    ),
    'accept-whitelist' => 
    array (
      'ZendCon\\Rest\\Session\\Controller' => 
      array (
        0 => 'application/json',
        1 => 'application/*+json',
      ),
      'ZendCon\\Rest\\Speaker\\Controller' => 
      array (
        0 => 'application/json',
        1 => 'application/*+json',
      ),
    ),
    'content-type-whitelist' => 
    array (
      'ZendCon\\Rest\\Session\\Controller' => 
      array (
        0 => 'application/json',
      ),
      'ZendCon\\Rest\\Speaker\\Controller' => 
      array (
        0 => 'application/json',
      ),
    ),
  ),
  'zf-hal' => 
  array (
    'metadata_map' => 
    array (
      'ZendCon\\Rest\\Session\\SessionEntity' => 
      array (
        'identifier_name' => 'session_id',
        'route_name' => 'zend-con.rest.session',
        'hydrator' => 'ArraySerializable',
      ),
      'ZendCon\\Rest\\Session\\SessionCollection' => 
      array (
        'identifier_name' => 'session_id',
        'route_name' => 'zend-con.rest.session',
        'is_collection' => true,
      ),
      'ZendCon\\Rest\\Speaker\\SpeakerEntity' => 
      array (
        'identifier_name' => 'speaker_id',
        'route_name' => 'zend-con.rest.speaker',
        'hydrator' => 'ObjectProperty',
      ),
      'ZendCon\\Rest\\Speaker\\SpeakerCollection' => 
      array (
        'identifier_name' => 'speaker_id',
        'route_name' => 'zend-con.rest.speaker',
        'is_collection' => true,
      ),
    ),
  ),
  'zf-apigility' => 
  array (
    'db-connected' => 
    array (
      'ZendCon\\Rest\\Session\\SessionResource' => 
      array (
        'adapter_name' => 'Db\\ZendCon',
        'table_name' => 'session',
        'hydrator_name' => 'ArraySerializable',
        'controller_service_name' => 'ZendCon\\Rest\\Session\\Controller',
        'table_service' => 'ZendCon\\Rest\\Session\\SessionResource\\Table',
      ),
    ),
  ),
  'service_manager' => 
  array (
        'factories' => array(
            'ZendCon\Speaker\Model' => 'ZendCon\Rest\Speaker\SpeakerModelFactory',
            'ZendCon\Rest\Speaker\SpeakerResource' => 'ZendCon\Rest\Speaker\SpeakerResourceFactory',
        ),
  ),
);
