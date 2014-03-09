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

Documentation
-------------

- [ ] Add zf-apigility-documentation as a required dependency of zf-apigility
- [ ] Add a settings screen for zf-apigility-documentation
  - [ ] Capture route endpoint for docs
  - [ ] Allow specifying a ContentNegotiation selector for the route endpoint

Admin UI Improvements
---------------------

### Technical improvements

- [X] Integrate Bower into workflow
- [X] Integrate Grunt into workflow
- [X] Refactor application into one controller/directive/filter/service per
  file. (Although multiple related directives may be in the same file.)
- [X] Switch to ui-router
- [ ] Switch to angular-ui-bootstrap
- [X] Remove Hyperagent (in favor of either $http, $resource, or Restangular)
- [ ] Add unit tests
- [ ] Add end-to-end tests

### General

- [X] Have a consistent color scheme. 
  - [X] It should be consistent with the Zend, ZF2, and/or Apigility website
    color scheme. This should pull through to the panel titles, sidebars, table
    highlights, etc.

  - [X] Related: button colors need to be consistent throughout, and based on
    actions. Etay's suggestions:

    - [X] blue for "create" actions
    - [X] green for "save" or "update" actions
        - use consistent verbiage -- either "save" or "update", but not both
    - [X] make cancel buttons just plain text or white
    - [X] confirmation for destructive actions should be red

- [X] Consistency in form layouts. All forms should have the same layout, either
  left/right, or stacked. Buttons should always be in the same place (and to the
  right is preferred).

- [X] For the lists with collapsible panes:

  - [X] clicking _anywhere_ on the bar should do the collapse/expand action
  - [X] if the title is editable, use a double-click to activate that
  - [X] put the "remove" icon to the right, with an "edit" icon to its left (if
    editable); these icons should only be visible on _hover_.

- [X] Every section - db adapters, authentication, rest services, etc. - should have
  a _title_ in its main pane.

- [ ] Icons

  - [X] Consider using _icons_ instead of _text badges_ for different resources
  - [ ] have a glossary of these somewhere.

- [X] _Every_ resource should be addressable, to allow linking to them directly.

- [X] The topnav should have "Settings" and "APIs" items. The latter _may_ be okay
  as a dropdown with the various APIs available.

- [X] Consider adding breadcrumbs, to make it clear the relation/hierarchy of the
  current active item.

- [ ] Consider having search inside the tool, to allow surfacing the various
  resources quickly. This may require having tags/descriptions/etc. for each
  resource that can be queried.

- [ ] Check the modal dialogs to ensure they never fill more than 80% of the screen;
  the content in them should be scrollable.

- [ ] Make flash messages _positional_ - have them as close to the action as
  possible. Consider disabling the button that was clicked and/or changing to a
  "working" status (or spinner).

- [ ] Create a _real_ dashboard only after all features are complete. This should be
  an area that has documentation and/or links to docs, buttons for first
  actions, etc. Potentially, it could also list APIs, or list APIs missing
  services, etc.

- [ ] "Remove" dialogs
    - [X] "Remove" dialogs should be color-coded. Red, thicker border, potentially
      pink or faded red background, and red "remove" or confirm button.
    - [X] Remove word "successful" from flash message
    - [ ] Maybe move flash message to replace the item removed?

- [ ] Consider doing some browser and/or screen size detection, and popping up a
  modal dialog when suboptimal platforms are detected in order to warn the user.

### "Dashboard"

- [X] Breadcrumbs: "Settings"
- [X] Title: Settings
- [X] This should actually be titled "Settings" (or "Application-wide Settings"),
  and should not be the main dashboard.

#### DB Adapters

- [X] Breadcrumbs: "Settings -> DB Adapters"
- [X] Title: Database Adapters
- [X] Add a page title, and navigation breadcrumbs: "Settings -> DB Adapters"
- [X] Make this more wizard-like. When you choose to create a DB adapter, first
  select the adapter type you wish to create, and then expose the options
  necessary for that adapter type. (Do this like we did with validator and
  filter options.)
    - [X] Show on the right what fields are _required_, so the user has a visual
      cue.
- [X] Make all adapters addressable
    - [X] Make edit state for each adapter addressable
- [ ] Changes to collapse items:
    - [X] remove "DB Adapter" text label; instead, use an icon with a hover title.
    - [X] remove tabbed view
    - [X] "view" mode by default
    - [X] "edit" button in titlebar, visible only on hover, to toggle view mode to
      "edit"
    - [ ] Edit form should have a "cancel" button 
        - [X] to return to "view" mode
        - [ ] revert any changes. 
        - [X] Cancel button should just be text (think iOS7).
    - [X] Edit form: same style as REST/RPC service edit forms
    - [ ] "remove" dialog 
        - [X] should be color-coded. Red, thicker border, potentially
          pink or faded red background, and red "remove" or confirm button.
        - [X] Remove word "successful" from flash message
        - [ ] Maybe move flash message to replace the item removed?

#### Authentication

- [X] Breadcrumbs: "Settings -> Authentication"
- [X] Title: Authentication
- [X] Add a page title, and navigation breadcrumbs: "Settings -> Authentication"
- [X] Be explicit: "Select ONE of the authentication methods below for your
  application"
- [X] Remove the words "Setup" and "Authentication" from the buttons
    - Make sure they stack nicely when on narrower screens, or stack _always_
- [X] Make edit state addressable
- [ ] Details screen:
    - [X] Have "Edit | Remove" buttons on title bar, visible on hover
    - [X] Clicking "edit" switches to an edit form; otherwise a "view"
    - [ ] Edit form 
        - [X] should have a "cancel" button to return to "view" mode
        - [ ] revert any changes. 
        - [X] Cancel button should just be text (think iOS7).
    - [X] Edit form: same style as REST/RPC service edit forms
    - [ ] "remove" dialog 
        - [X] should be color-coded. Red, thicker border, potentially
          pink or faded red background, and red "remove" or confirm button.
        - [X] Remove word "successful" from flash message
        - [ ] Maybe move flash message to replace the item removed?

### APIs

- [X] Breadcrumbs: "APIs"
- [X] Title: APIs
- [ ] Create a landing page for APIs
    - [X] List all APIs, and link to them.
    - [ ] Potentially provide the API description for each, when present
    - [ ] Consider having badges:
        - [ ] Green or similar for APIs that appear to have complete information
        - [ ] Red for APIs that may need additional configuration to be complete
            - [ ] Hover dialog that would link to action items, such as
              documentation
    - [X] "Create API" goes on this page. It should be a form, and always present
      (no need to click a button to make the form appear).

#### API Screen

- [X] Breadcrumbs: "APIs -> {API name}"
- [X] Title: {API Name} API (latter in a different color; gray?)
- [ ] Display API description, if available. Otherwise, have dummy text. Double
  click of text allows you to edit, and displays "cancel" and "save" buttons.
- [ ] Versioning
    - [ ] Allow specifying version identifier
        - [ ] This may require some changes in the backend, as we have to do some
          normalization between version identifiers and namespace!
- [ ] Sidebar
  Organize so that the most important items are first
    - [X] Overview
    - [ ] REST Services
        - [ ] Add a green checkmark if we have any REST services
        - [ ] Add a red X if no services (of EITHER type) are defined
        - [ ] No badge if none defined, but RPC services are defined
    - [ ] RPC Services
        - [ ] Add a green checkmark if we have any RPC services
        - [ ] Add a red X if no services (of EITHER type) are defined
        - [ ] No badge if none defined, but REST services are defined
    - [X] Authorization
- [X] Link to each service; do not just list
    - [X] Each service individually
    - [X] Specific tab in the service
    - [X] View vs Edit state for the tab
- [ ] If no services:
    - [X] "You have not yet created any services; would you like to?" and do a modal
    - [ ] "Would you like to?" and do a modal drop down in place.
    - [ ] Red badge! (and, conversely, green badge or check mark if services exist!)

#### REST/RPC Services

- [X] Breadcrumbs: "APIs -> {API Name} -> (REST|RPC) Services"
- [X] Title: REST|RPC Services
- [ ] If no services:
    - [ ] "You have not yet created any services; would you like to?" and do a modal
      drop down in place.
- [ ] Allow addressing services. Addressing them will expand the specific collapse
  box and focus it.
    - [ ] Ideally, also allow addressing each tab inside the collapse box, and,
      potentially, whether or not the state is view or edit
- [X] Naming
    - [X] Consider allowing the ability to configure a _name_ for each service
    - [X] have a method for normalizing the name in the UI based on rules. Thus,
      `Status\V1\Rest\Status\Controller` becomes just `Status` in the UI.
- [ ] Collapse lists
    - [X] remove "(REST|RPC) Service" text label; instead, use icons with hover titles.
    - [X] "view" mode by default
    - [X] "edit" button in titlebar, visible only on hover, to toggle view mode to
      "edit" (far right)
    - [X] "remove" button in titlebar, visible only on hover, to display "remove"
      dialog (farthest right)
    - [X] Tabs within:
        - [X] View mode:
            - [X] Settings
            - [X] Fields
            - [X] Documentation
            - [X] Source Code
        - [X] Edit mode:
            - [X] Settings
            - [X] Fields
            - [X] Documentation
    - [ ] Edit forms 
        - [X] should have a "cancel" button to return to "view" mode
        - [ ] revert any changes. 
        - [X] Cancel button should just be text (think iOS7).
    - [ ] "remove" dialog 
        - [X] should be color-coded. Red, thicker border, potentially pink or faded
          red background, and red "remove" or confirm button.
        - [X] Remove word "successful" from flash message
        - [ ] Maybe move flash message to replace the item removed?
- [ ] View/Edit
    - [X] Have collapse sections of grouped settings
    - [ ] Potentially red X/green check to denote completeness of sections
    - [ ] Consider a "validate" button; i.e., validate that the settings will work
      prior to submitting
    - [ ] Put "plain text" descriptions next to each HTTP method (Andi's suggestion):
        - [ ] GET (read)
        - [ ] POST (create)
        - [ ] PATCH (partial update)
        - [ ] PUT (update/replace)
        - [ ] DELETE (remove)
- [ ] Fields
    - [X] Have a separate "view" mode, and make the display simpler for it
    - [X] single click to collapse/expand; double-click to edit field name
    - [X] "remove" button in titlebar, visible only on hover, to display "remove"
      dialog (farthest right)
    - [X] "add option" button should be blue (or whatever color we use for create),
      and only show on hover. Put to left of "remove".
    - [X] Use a standard "move" icon for the filters/validators (DONE)
    - [X] Consistent icon size, regardless of nesting
    - [X] Required:
        - [X] Remove "?" from "Required?"
        - [X] Make box smaller
        - [X] Use a toggle instead of a checkbox
    - [X] Options:
        - [X] Use toggles for boolean values
    - [ ] "Add" dialogs
        - [X] Frame all "Add" dialogs with a thicker line so as to draw attention to them.
        - [X] "Add ..." buttons should disappear while the form is available
        - [ ] "Add ..." forms should be removed once an item is created
    - [X] Make all "add" buttons display on hover over the specific panel only
- [ ] Source Code viewer
    - [ ] Check to ensure that the dialog never grows more than 80% of the screen
      height, and is internally scrollable.

#### Authorization

- [X] Breadcrumbs: "APIs -> {API Name} -> Authorization"
- [X] Title: Authorization
- [X] See note about normalized names under REST/RPC services; use those names in
  this table.
- [X] Move "Save" out of the table; perhaps rename to "Update" (but whatever is
  used, use it consistently)
- [ ] Add a "Cancel" button
    - [X] to the left of "Save"
    - [ ] revert any changes made.
- [X] Lighten colors on the top and right
- [ ] Change checkboxes
    - [X] Minimum: disable checkboxes for any methods that are not enabled for a
      given service.
    - [ ] Potentially allow toggling; clicking on a disabled one would also enable
      that method for the service.
        - column/row toggles should never do this, however!
