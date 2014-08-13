angular.module('templates-main', ['html/api/authorization.html', 'html/api/documentation-method-edit.html', 'html/api/documentation-method-view.html', 'html/api/index-sidebar.html', 'html/api/index.html', 'html/api/input-edit.html', 'html/api/input-filter-edit.html', 'html/api/input-filter-view.html', 'html/api/input-filter/filter-new.html', 'html/api/input-view.html', 'html/api/overview.html', 'html/api/rest-services/documentation-edit-collection-pane.html', 'html/api/rest-services/documentation-edit-entity-pane.html', 'html/api/rest-services/documentation-edit.html', 'html/api/rest-services/documentation-view-collection-pane.html', 'html/api/rest-services/documentation-view-entity-pane.html', 'html/api/rest-services/documentation-view.html', 'html/api/rest-services/edit.html', 'html/api/rest-services/index.html', 'html/api/rest-services/new.html', 'html/api/rest-services/remove.html', 'html/api/rest-services/settings-edit.html', 'html/api/rest-services/settings-view.html', 'html/api/rest-services/source-code.html', 'html/api/rest-services/view.html', 'html/api/rpc-services/documentation-edit.html', 'html/api/rpc-services/documentation-view.html', 'html/api/rpc-services/edit.html', 'html/api/rpc-services/index.html', 'html/api/rpc-services/new.html', 'html/api/rpc-services/remove.html', 'html/api/rpc-services/settings-edit.html', 'html/api/rpc-services/settings-view.html', 'html/api/rpc-services/source-code.html', 'html/api/rpc-services/view.html', 'html/api/sidebar.html', 'html/breadcrumbs.html', 'html/content.html', 'html/dashboard-sidebar.html', 'html/directives/ag-edit-inplace.html', 'html/empty-content.html', 'html/empty.html', 'html/index.html', 'html/modals/cache-check.html', 'html/modals/create-api-form.html', 'html/modals/fs-perms.html', 'html/modals/help-content-negotiation.html', 'html/modals/help-input-filter.html', 'html/modals/source-code.html', 'html/settings/authentication/http-basic-edit.html', 'html/settings/authentication/http-basic-view.html', 'html/settings/authentication/http-basic.html', 'html/settings/authentication/http-digest-edit.html', 'html/settings/authentication/http-digest-view.html', 'html/settings/authentication/http-digest.html', 'html/settings/authentication/index.html', 'html/settings/authentication/new-http-basic.html', 'html/settings/authentication/new-http-digest.html', 'html/settings/authentication/new-oauth2.html', 'html/settings/authentication/oauth2-edit.html', 'html/settings/authentication/oauth2-view.html', 'html/settings/authentication/oauth2.html', 'html/settings/authentication/remove.html', 'html/settings/content-negotiation/edit.html', 'html/settings/content-negotiation/index.html', 'html/settings/content-negotiation/new-selector-form.html', 'html/settings/content-negotiation/remove.html', 'html/settings/content-negotiation/view.html', 'html/settings/dashboard.html', 'html/settings/db-adapters/edit.html', 'html/settings/db-adapters/index.html', 'html/settings/db-adapters/new-adapter-form.html', 'html/settings/db-adapters/remove.html', 'html/settings/db-adapters/view.html', 'html/settings/sidebar.html', 'html/view-navigation.html']);

angular.module("html/api/authorization.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/authorization.html",
    "<div class=\"panel panel-warning\" ng-hide=\"authentication.configured\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">No Authentication configured!</h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel-body\">\n" +
    "        <p>\n" +
    "            You do not have authentication configured at this time. Marking services\n" +
    "            as requiring authorization will make them inaccessible.\n" +
    "        </p>\n" +
    "\n" +
    "        <p>\n" +
    "            You can configure authentication on the <a\n" +
    "            ui-sref=\"ag.settings.authentication\">authentication\n" +
    "            screen</a>.\n" +
    "        </p>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "<p>\n" +
    "    A <input type=\"checkbox\" checked> check means authentication is\n" +
    "    <strong>required</strong> for the given combination of service\n" +
    "    and HTTP method.\n" +
    "</p>\n" +
    "\n" +
    "<form ag-form>\n" +
    "<table class=\"table table-bordered table-striped table-responsive\">\n" +
    "    <thead>\n" +
    "        <tr class=\"success\">\n" +
    "            <th>Service Name</th>\n" +
    "            <th class=\"text-center\">GET    <br><input type=\"checkbox\" ng-click=\"updateColumn($event, 'GET')\"    ng-disabled=\"!editable\"></th>\n" +
    "            <th class=\"text-center\">POST   <br><input type=\"checkbox\" ng-click=\"updateColumn($event, 'POST')\"   ng-disabled=\"!editable\"></th>\n" +
    "            <th class=\"text-center\">PATCH  <br><input type=\"checkbox\" ng-click=\"updateColumn($event, 'PATCH')\"  ng-disabled=\"!editable\"></th>\n" +
    "            <th class=\"text-center\">PUT    <br><input type=\"checkbox\" ng-click=\"updateColumn($event, 'PUT')\"    ng-disabled=\"!editable\"></th>\n" +
    "            <th class=\"text-center\">DELETE <br><input type=\"checkbox\" ng-click=\"updateColumn($event, 'DELETE')\" ng-disabled=\"!editable\"></th>\n" +
    "            <th class=\"text-center\">&nbsp;</th>\n" +
    "        </tr>\n" +
    "    </thead>\n" +
    "\n" +
    "    <tbody>\n" +
    "        <tr ng-repeat=\"(name, data) in apiAuthorizations\">\n" +
    "            <td>{{name | servicename}} {{name | servicetype}}</td>\n" +
    "            <td class=\"text-center\"><input type=\"checkbox\" ng-model=\"apiAuthorizations[name].GET\" ng-disabled=\"!isEditable(name, 'GET')\"></td>\n" +
    "            <td class=\"text-center\"><input type=\"checkbox\" ng-model=\"apiAuthorizations[name].POST\" ng-disabled=\"!isEditable(name, 'POST')\"></td>\n" +
    "            <td class=\"text-center\"><input type=\"checkbox\" ng-model=\"apiAuthorizations[name].PATCH\" ng-disabled=\"!isEditable(name, 'PATCH')\"></td>\n" +
    "            <td class=\"text-center\"><input type=\"checkbox\" ng-model=\"apiAuthorizations[name].PUT\" ng-disabled=\"!isEditable(name, 'PUT')\"></td>\n" +
    "            <td class=\"text-center\"><input type=\"checkbox\" ng-model=\"apiAuthorizations[name].DELETE\" ng-disabled=\"!isEditable(name, 'DELETE')\"></td>\n" +
    "            <td class=\"text-center success\"><input type=\"checkbox\" ng-click=\"updateRow($event, name)\" ng-disabled=\"!editable\"></td>\n" +
    "        </tr>\n" +
    "    </tbody>\n" +
    "</table>\n" +
    "\n" +
    "<div class=\"btn-group pull-right\">\n" +
    "    <!-- todo Create \"cancel\"/\"reset\" functionality -->\n" +
    "    <button class=\"btn btn-sm btn-default\" type=\"button\" ng-disabled=\"!editable\">Cancel</button>\n" +
    "    <button class=\"btn btn-sm btn-success\" type=\"button\" ng-click=\"saveAuthorization()\" ng-disabled=\"!editable\">Save</button>\n" +
    "</div>\n" +
    "</form>\n" +
    "\n" +
    "<div class=\"clearfix\"></div>\n" +
    "");
}]);

angular.module("html/api/documentation-method-edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/documentation-method-edit.html",
    "<div class=\"form-group\">\n" +
    "    <label class=\"control-label\">Description</label>\n" +
    "    <div class=\"controls\">\n" +
    "        <textarea \n" +
    "            placeholder=\"Insert the {{method}} description here\"\n" +
    "            class=\"form-control input-xlarge\" \n" +
    "            required=\"\"\n" +
    "            rows=\"3\"\n" +
    "            ng-model=\"methodData.description\"></textarea>\n" +
    "        <p class=\"help-block\">The description of the {{method}} HTTP method</p>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"form-group\" ng-hide=\"method == 'GET'\">\n" +
    "    <label class=\"control-label\">Request Body</label>\n" +
    "\n" +
    "    <button type=\"button\" ng-click=\"generate(methodData, method, 'request')\" class=\"btn btn-default btn-xs pull-right\">\n" +
    "        <i class=\"glyphicon glyphicon-refresh\"></i>\n" +
    "        generate from configuration\n" +
    "    </button>\n" +
    "\n" +
    "    <div class=\"controls\">\n" +
    "        <textarea placeholder=\"Insert the request specification\" class=\"form-control input-xlarge\" required=\"\" rows=\"10\" ng-model=\"methodData.request\"></textarea>\n" +
    "        <p class=\"help-block\">The HTTP request specification</p>\n" +
    "    </div>\n" +
    "</div>\n" +
    "<div class=\"clearfix\"></div>\n" +
    "\n" +
    "<div class=\"form-group\">\n" +
    "    <label class=\"control-label\">Response Body</label>\n" +
    "\n" +
    "    <button type=\"button\" ng-click=\"generate(methodData, method, 'response', restPart)\" class=\"btn btn-default btn-xs pull-right\">\n" +
    "        <i class=\"glyphicon glyphicon-refresh\"></i>\n" +
    "        generate from configuration\n" +
    "    </button>\n" +
    "\n" +
    "    <div class=\"controls\">\n" +
    "        <textarea placeholder=\"Insert the response specification\" class=\"form-control input-xlarge\" required=\"\" rows=\"10\" ng-model=\"methodData.response\"></textarea>\n" +
    "        <p class=\"help-block\">The HTTP response specification</p>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"clearfix\"></div>\n" +
    "");
}]);

angular.module("html/api/documentation-method-view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/documentation-method-view.html",
    "<div ng-show=\"requiresAuthorization(method, restPart)\">\n" +
    "    <button class=\"btn btn-sm btn-danger\">Authorization Required</button>\n" +
    "</div>\n" +
    "\n" +
    "<div ng-show=\"methodData.description\">\n" +
    "    <div ng-bind-html=\"methodData.description\"></div>\n" +
    "</div>\n" +
    "\n" +
    "<div ng-hide=\"methodData.description\" class=\"text-muted\">\n" +
    "    No description available.\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"panel panel-default\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">Request</h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel-body\">\n" +
    "        <div class=\"control-group\" ng-show=\"service.accept_whitelist || service.content_type_whitelist\">\n" +
    "            <label class=\"control-label\">Headers</label>\n" +
    "            <table class=\"table table-striped\">\n" +
    "                <tr ng-show=\"service.accept_whitelist\">\n" +
    "                    <td width=\"30%\"><strong>Accept</strong></td>\n" +
    "                    <td>{{ service.accept_whitelist.join(', ') }}</td>\n" +
    "                </tr>\n" +
    "                <tr ng-show=\"method != 'GET' && service.content_type_whitelist\">\n" +
    "                    <td width=\"30%\"><strong>Content-Type</strong></td>\n" +
    "                    <td>{{ service.content_type_whitelist.join(', ') }}</td>\n" +
    "                </tr>\n" +
    "            </table>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"control-group\" ng-hide=\"method == 'GET'\">\n" +
    "            <label class=\"control-label\">Body</label>\n" +
    "            <div ng-show=\"methodData.request\"><pre ng-bind-html=\"methodData.request\"></pre></div>\n" +
    "            <div ng-hide=\"methodData.request\" class=\"text-muted\">No request body available.</div>\n" +
    "        </div>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"panel panel-default\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">Response</h4>\n" +
    "    </div>\n" +
    "\n" +
    "\n" +
    "    <div class=\"panel-body\">\n" +
    "        <div ng-show=\"method!= 'GET' && service.content_type_whitelist\" class=\"control-group\">\n" +
    "            <label class=\"control-label\">Headers</label>\n" +
    "            <table class=\"table table-striped\">\n" +
    "                <tr>\n" +
    "                    <td width=\"30%\"><strong>Content-Type</strong></td>\n" +
    "                    <td>{{ service.content_type_whitelist.join(', ') }}</td>\n" +
    "                </tr>\n" +
    "            </table>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"control-group\">\n" +
    "            <label class=\"control-label\">Response Body</label>\n" +
    "            <div ng-show=\"methodData.response\"><pre ng-bind-html=\"methodData.response\"></pre></div>\n" +
    "            <div ng-hide=\"methodData.response\" class=\"text-muted\">No response body available.</div>\n" +
    "        </div>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/index-sidebar.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/index-sidebar.html",
    "<button class=\"btn btn-sm btn-primary\"\n" +
    "  title=\"Create New API\"\n" +
    "  ng-click=\"dialog()\">\n" +
    "  <i class=\"glyphicon glyphicon-plus\"></i> Create New API\n" +
    "</button>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/index.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/index.html",
    "<ul class=\"list-group\">\n" +
    "  <li ng-repeat=\"api in apiList\" class=\"list-group-item\">\n" +
    "    <h4><a ui-sref=\".version(api)\">{{ api.apiName }}</a></h4>\n" +
    "  </li>\n" +
    "</ul>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/input-edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/input-edit.html",
    "<div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "  <label class=\"control-label\">Description</label>\n" +
    "  <textarea class=\"form-control input-xlarge\" ng-model=\"input.description\"></textarea>\n" +
    "</div></div>\n" +
    "\n" +
    "<div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <label class=\"control-label\">Will this field be a file upload?</label>\n" +
    "    <toggle-switch model=\"input.file_upload\" class=\"small pull-right\" on-label=\"Yes\" off-label=\"No\"></toggle-switch>\n" +
    "</div></div>\n" +
    "\n" +
    "<div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <label class=\"control-label\">Required</label>\n" +
    "    <toggle-switch model=\"input.required\" class=\"small pull-right\" on-label=\"Yes\" off-label=\"No\"></toggle-switch>\n" +
    "</div></div>\n" +
    "\n" +
    "<div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <label class=\"control-label\">Allow Empty</label>\n" +
    "    <toggle-switch model=\"input.allow_empty\" class=\"small pull-right\" on-label=\"Yes\" off-label=\"No\"></toggle-switch>\n" +
    "</div></div>\n" +
    "\n" +
    "<div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <label class=\"control-label\">Continue if Empty</label>\n" +
    "    <toggle-switch model=\"input.continue_if_empty\" class=\"small pull-right\" on-label=\"Yes\" off-label=\"No\"></toggle-switch>\n" +
    "</div></div>\n" +
    "\n" +
    "<div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <label class=\"control-label\">Validation Failure Message</label>\n" +
    "    <textarea class=\"form-control input-xlarge\" ng-model=\"input.error_message\"></textarea>\n" +
    "    <p class=\"help-block\">Error message to display if field does not validate</p>\n" +
    "</div></div>\n" +
    "\n" +
    "<ag-collapse class=\"panel-warning\">\n" +
    "    <collapse-header>\n" +
    "        <h4 class=\"panel-title\">\n" +
    "            <i class=\"glyphicon glyphicon-filter\"></i>\n" +
    "            Filters\n" +
    "\n" +
    "            <button type=\"button\" class=\"btn btn-sm btn-primary pull-right\" \n" +
    "                collapse-button\n" +
    "                ng-hide=\"input.showNewFilterForm\"\n" +
    "                ng-click=\"input.showNewFilterForm = true\">\n" +
    "                Add Filter\n" +
    "            </button>\n" +
    "        </h4>\n" +
    "\n" +
    "        <div class=\"clearfix\"></div>\n" +
    "    </collapse-header>\n" +
    "    \n" +
    "    <collapse-body>\n" +
    "        <div ui-sortable=\"{handle: '.filter-handle'}\" ng-model=\"input.filters\">\n" +
    "            <ag-collapse class=\"panel-blank\" ng-repeat=\"filter in input.filters\">\n" +
    "                <collapse-header>\n" +
    "                    <h4 class=\"panel-title\">\n" +
    "                        <i class=\"glyphicon glyphicon-resize-vertical filter-handle\"></i>\n" +
    "\n" +
    "                        {{filter.name}}\n" +
    "\n" +
    "                        <div class=\"btn-group pull-right\">\n" +
    "                            <button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Add Option\"\n" +
    "                                collapse-button\n" +
    "                                ng-hide=\"filter.showNewOptionForm\"\n" +
    "                                ng-click=\"filter.showNewOptionForm = true\">Add Option</button>\n" +
    "                            <button type=\"button\" class=\"btn btn-sm btn-danger\" \n" +
    "                                collapse-button\n" +
    "                                ng-click=\"removeFilter(input, $index)\">\n" +
    "                                <i class=\"glyphicon glyphicon-trash\"></i>\n" +
    "                            </button>\n" +
    "                        </div>\n" +
    "                    </h4>\n" +
    "                    <div class=\"clearfix\"></div>\n" +
    "                </collapse-header>\n" +
    "            \n" +
    "                <collapse-body class=\"list-group in\">\n" +
    "                    <li \n" +
    "                        class=\"list-group-item\"\n" +
    "                        ag-hover\n" +
    "                        ng-repeat=\"(optionName, optionValue) in filter.options\">\n" +
    "                        <div class=\"form-group form-horizontal\">\n" +
    "                            <label class=\"control-label col-sm-4\">{{optionName}}:</label>\n" +
    "\n" +
    "                            <div class=\"col-sm-7\">\n" +
    "                                <p class=\"form-control-static input-xlarge\"\n" +
    "                                    ng-show=\"filterOptions[filter.name][optionName] == 'bool'\"\n" +
    "                                    ng-class=\"{true: 'text-info', false: 'text-muted'}[optionValue]\">\n" +
    "                                    {{optionValue && 'Yes' || 'No'}}\n" +
    "                                </p>\n" +
    "                                <p \n" +
    "                                    class=\"form-control-static input-xlarge\"\n" +
    "                                    ng-show=\"filterOptions[filter.name][optionName] != 'bool'\">\n" +
    "                                    {{ optionValue }}\n" +
    "                                </p>\n" +
    "                            </div>\n" +
    "\n" +
    "                            <div class=\"col-sm-1\">\n" +
    "                                <button \n" +
    "                                    type=\"button\" class=\"btn btn-sm btn-danger pull-right\"\n" +
    "                                    ag-hover-target\n" +
    "                                    ng-click=\"removeOption(filter.options, optionName)\">\n" +
    "                                    <i class=\"glyphicon glyphicon-trash\"></i></button>\n" +
    "                            </div>\n" +
    "                        </div>\n" +
    "                        <div class=\"clearfix\"></div>\n" +
    "                    </li>\n" +
    "\n" +
    "                    <li class=\"list-group-item\" ng-show=\"filter.showNewOptionForm\">\n" +
    "                        <div class=\"form-group\">\n" +
    "                            <select class=\"input-xlarge\" ui-select2=\"{placeholder: 'Select Option', width: '300px' }\" type=\"text\" ng-model=\"filter._newOptionName\">\n" +
    "                                <option ng-repeat=\"(key, value) in filterOptions[filter.name]\" value=\"{{key}}\">{{key}}</option>\n" +
    "                            </select>\n" +
    "\n" +
    "                            <br />\n" +
    "\n" +
    "                            <button \n" +
    "                                type=\"button\" class=\"btn btn-sm\"\n" +
    "                                ng-show=\"filter._newOptionName && (filterOptions[filter.name][filter._newOptionName] == 'bool')\"\n" +
    "                                ng-class=\"{true: 'btn-success', false: 'btn-default'}[filter._newOptionValue]\"\n" +
    "                                ng-click=\"filter._newOptionValue = !filter._newOptionValue\">{{filter._newOptionValue && 'On' || 'Off'}}</button>\n" +
    "                            <input \n" +
    "                                type=\"text\" class=\"form-control input-xlarge\" \n" +
    "                                ng-show=\"filter._newOptionName && (filterOptions[filter.name][filter._newOptionName] != 'bool')\"\n" +
    "                                ng-model=\"filter._newOptionValue\">\n" +
    "                        </div>\n" +
    "\n" +
    "                        <div class=\"btn-group ag-new-input pull-right\">\n" +
    "                            <button type=\"button\" class=\"btn btn-sm btn-default\" \n" +
    "                                ng-click=\"filter.showNewOptionForm = null\">Cancel</button>\n" +
    "                            <button type=\"button\" class=\"btn btn-sm btn-primary\"\n" +
    "                                ng-click=\"addFilterOption(filter)\">Add Option</button>\n" +
    "                        </div>\n" +
    "\n" +
    "                        <div class=\"clearfix\"></div>\n" +
    "                    </li>\n" +
    "                </collapse-body>\n" +
    "            </ag-collapse>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"panel panel-blank\" ng-show=\"input.showNewFilterForm\">\n" +
    "            <div class=\"panel-body\">\n" +
    "                <select class=\"input-xlarge\" ui-select2=\"{placeholder: 'Select Filter', width: '300px' }\" type=\"text\" ng-model=\"input._newFilterName\">\n" +
    "                    <option ng-repeat=\"(key, value) in filterOptions\" value=\"{{key}}\">{{key}}</option>\n" +
    "                </select>\n" +
    "\n" +
    "                <div class=\"btn-group pull-right\">\n" +
    "                    <button type=\"button\" class=\"btn btn-sm btn-default\"\n" +
    "                        ng-click=\"input.showNewFilterForm = false\">Cancel</button>\n" +
    "                    <button type=\"button\" class=\"btn btn-sm btn-primary\"\n" +
    "                        ng-click=\"addFilter(input)\">Add Filter</button>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "    </collapse-body>\n" +
    "</ag-collapse>\n" +
    "\n" +
    "<ag-collapse class=\"panel-warning\">\n" +
    "    <collapse-header>\n" +
    "        <h4 class=\"panel-title\">\n" +
    "            <i class=\"glyphicon glyphicon-ok\"></i>\n" +
    "            Validators\n" +
    "\n" +
    "            <button type=\"button\" class=\"btn btn-sm btn-primary pull-right\"\n" +
    "                collapse-button\n" +
    "                ng-hide=\"input.showNewValidatorForm\"\n" +
    "                ng-click=\"input.showNewValidatorForm = true\">\n" +
    "                Add Validator\n" +
    "            </button>\n" +
    "        </h4>\n" +
    "\n" +
    "        <div class=\"clearfix\"></div>\n" +
    "    </collapse-header>\n" +
    "\n" +
    "    <collapse-body>\n" +
    "        <div ui-sortable=\"{handle: '.validator-handle'}\" ng-model=\"input.validators\">\n" +
    "            <ag-collapse class=\"panel panel-blank\" ng-repeat=\"validator in input.validators\">\n" +
    "                <collapse-header>\n" +
    "                    <h4 class=\"panel-title\">\n" +
    "                        <i class=\"glyphicon glyphicon-resize-vertical validator-handle\"></i>\n" +
    "\n" +
    "                        {{validator.name}}\n" +
    "\n" +
    "                        <div class=\"btn-group pull-right\">\n" +
    "                            <button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Add Option\"\n" +
    "                                collapse-button\n" +
    "                                ng-hide=\"validator.showNewOptionForm\"\n" +
    "                                ng-click=\"validator.showNewOptionForm = true\">Add Option</button>\n" +
    "                            <button type=\"button\" class=\"btn btn-sm btn-danger\" \n" +
    "                                collapse-button\n" +
    "                                ng-click=\"removeValidator(input, $index)\">\n" +
    "                                <i class=\"glyphicon glyphicon-trash\"></i>\n" +
    "                            </button>\n" +
    "                        </div>\n" +
    "                    </h4>\n" +
    "                    <div class=\"clearfix\"></div>\n" +
    "                </collapse-header>\n" +
    "            \n" +
    "                <collapse-body class=\"list-group in\">\n" +
    "                    <li \n" +
    "                        class=\"list-group-item\" \n" +
    "                        ag-hover\n" +
    "                        ng-repeat=\"(optionName, optionValue) in validator.options\">\n" +
    "                        <div class=\"form-group form-horizontal\">\n" +
    "                            <label class=\"control-label col-sm-4\">{{optionName}}:</label>\n" +
    "\n" +
    "                            <div class=\"col-sm-7\">\n" +
    "                                <p class=\"form-control-static input-xlarge\"\n" +
    "                                    ng-show=\"validatorOptions[validator.name][optionName] == 'bool'\"\n" +
    "                                    ng-class=\"{true: 'text-info', false: 'text-muted'}[optionValue]\">\n" +
    "                                    {{optionValue && 'Yes' || 'No'}}\n" +
    "                                </p>\n" +
    "                                <p \n" +
    "                                    class=\"form-control-static input-xlarge\"\n" +
    "                                    ng-show=\"validatorOptions[validator.name][optionName] != 'bool'\">\n" +
    "                                    {{ optionValue }}\n" +
    "                                </p>\n" +
    "                            </div>\n" +
    "\n" +
    "                            <div class=\"col-sm-1\">\n" +
    "                                <button \n" +
    "                                    type=\"button\" class=\"btn btn-sm btn-danger pull-right\"\n" +
    "                                    ag-hover-target\n" +
    "                                    ng-click=\"removeOption(validator.options, optionName)\">\n" +
    "                                    <i class=\"glyphicon glyphicon-trash\"></i></button>\n" +
    "                            </div>\n" +
    "                        </div>\n" +
    "                        <div class=\"clearfix\"></div>\n" +
    "                    </li>\n" +
    "\n" +
    "                    <li class=\"list-group-item\" ng-show=\"validator.showNewOptionForm\">\n" +
    "                        <div class=\"form-group\">\n" +
    "                            <select class=\"input-xlarge\" ui-select2=\"{placeholder: 'Select Option', width: '300px' }\" type=\"text\" ng-model=\"validator._newOptionName\">\n" +
    "                                <option ng-repeat=\"(key, value) in validatorOptions[validator.name]\" value=\"{{key}}\">{{key}}</option>\n" +
    "                            </select>\n" +
    "\n" +
    "                            <br />\n" +
    "\n" +
    "                            <toggle-switch \n" +
    "                                ng-show=\"validator._newOptionName && (validatorOptions[validator.name][validator._newOptionName] == 'bool')\"\n" +
    "                                model=\"validator._newOptionValue\"\n" +
    "                                on-label=\"Yes\"\n" +
    "                                off-label=\"No\"\n" +
    "                                class=\"small\"></toggle-switch>\n" +
    "                            <input \n" +
    "                                type=\"text\" class=\"form-control input-xlarge\" \n" +
    "                                ng-show=\"validator._newOptionName && (validatorOptions[validator.name][validator._newOptionName] != 'bool')\"\n" +
    "                                ng-model=\"validator._newOptionValue\">\n" +
    "                        </div>\n" +
    "\n" +
    "                        <div class=\"btn-group ag-new-input pull-right\">\n" +
    "                            <button type=\"button\" class=\"btn btn-sm btn-default\" \n" +
    "                                ng-click=\"validator.showNewOptionForm = null\">Cancel</button>\n" +
    "                            <button type=\"button\" class=\"btn btn-sm btn-primary\"\n" +
    "                                ng-click=\"addValidatorOption(validator)\">Add Option</button>\n" +
    "                        </div>\n" +
    "\n" +
    "                        <div class=\"clearfix\"></div>\n" +
    "                    </li>\n" +
    "                </collapse-body>\n" +
    "            </ag-collapse>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"panel panel-blank\" ng-show=\"input.showNewValidatorForm\">\n" +
    "            <div class=\"panel-body\">\n" +
    "                <select class=\"input-xlarge\" ui-select2=\"{placeholder: 'Select Validator', width: '300px' }\" type=\"text\" ng-model=\"input._newValidatorName\">\n" +
    "                    <option ng-repeat=\"(key, value) in validatorOptions\" value=\"{{key}}\">{{key}}</option>\n" +
    "                </select>\n" +
    "\n" +
    "                <div class=\"btn-group pull-right\">\n" +
    "                    <button type=\"button\" class=\"btn btn-sm btn-default\"\n" +
    "                        ng-click=\"input.showNewValidatorForm = false\">Cancel</button>\n" +
    "                    <button type=\"button\" class=\"btn btn-sm btn-primary\"\n" +
    "                        ng-click=\"addValidator(input)\">Add Validator</button>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "    </collapse-body>\n" +
    "</ag-collapse>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/input-filter-edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/input-filter-edit.html",
    "<div ng-controller=\"ApiServiceInputController\">\n" +
    "    <div class=\"ag-new-input\">\n" +
    "        <button class=\"btn btn-info pull-left\" title=\"Help\"\n" +
    "            ng-click=\"help()\"\n" +
    "            data-toggle=\"modal\" data-target=\"#inputHelp\">\n" +
    "            <i class=\"glyphicon glyphicon-info-sign\"></i>\n" +
    "        </button>\n" +
    "\n" +
    "        <form class=\"form-inline pull-right\">\n" +
    "            <div class=\"form-group\">\n" +
    "                <input\n" +
    "                    type=\"text\" class=\"form-control input-xlarge\" placeholder=\"Field name\"\n" +
    "                    ag-on-enter=\"addInput()\"\n" +
    "                    ng-model=\"newInput\">\n" +
    "            </div>\n" +
    "            <button type=\"button\" ng-click=\"addInput()\" class=\"btn btn-sm btn-primary\">Create New Field</button>\n" +
    "        </form>\n" +
    "    </div>\n" +
    "    <div class=\"ag-new-input clearfix\"></div>\n" +
    "\n" +
    "<form ag-form>\n" +
    "    <div class=\"ag panel-group tooltip-api\">\n" +
    "        <ag-collapse \n" +
    "            class=\"panel-success\"\n" +
    "            ng-repeat=\"input in service.input_filter\">\n" +
    "            <collapse-header>\n" +
    "\n" +
    "                <h4 class=\"panel-title\">\n" +
    "                    <i class=\"glyphicon glyphicon-tasks\"></i>\n" +
    "\n" +
    "                    <ag-edit-inplace name=\"input.name\" validate=\"validateInputName(input.name, input)\"></ag-edit-inplace>\n" +
    "\n" +
    "                    <button \n" +
    "                        type=\"button\" class=\"btn btn-sm btn-danger pull-right\" title=\"Remove field\"\n" +
    "                        collapse-button\n" +
    "                        ng-click=\"removeInput($index)\">\n" +
    "                        <i class=\"glyphicon glyphicon-trash\"></i>\n" +
    "                    </button>\n" +
    "                </h4>\n" +
    "\n" +
    "                <div class=\"clearfix\"></div>\n" +
    "            </collapse-header>\n" +
    "\n" +
    "            <collapse-body>\n" +
    "                <div class=\"panel-body\">\n" +
    "                    <ng-include src=\"'html/api/input-edit.html'\" onload=\"index = $index; input = input\"></ng-include>\n" +
    "                </div>\n" +
    "            </collapse-body>\n" +
    "        </ag-collapse>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"ag-new-input btn-group pull-right\">\n" +
    "        <!-- todo Make the \"cancel\" action revert  -->\n" +
    "        <button type=\"button\" class=\"btn btn-sm btn-default\"\n" +
    "            ag-cancel-edit>Cancel</button>\n" +
    "        <button type=\"submit\" class=\"btn btn-sm btn-success\" ng-click=\"saveInput()\">Save Changes</button>\n" +
    "    </div>\n" +
    "</form>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/input-filter-view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/input-filter-view.html",
    "<div class=\"ag panel-group tooltip-api\" ng-controller=\"ApiServiceInputController\">\n" +
    "    <div class=\"panel panel-blank\" ng-show=\"service.input_filter.length === 0\"><div class=\"panel-body\">\n" +
    "        <p>\n" +
    "            No fields have been configured.\n" +
    "        </p>\n" +
    "    </div></div>\n" +
    "\n" +
    "    <ag-collapse \n" +
    "        class=\"panel-default\"\n" +
    "        ng-repeat=\"input in service.input_filter\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">\n" +
    "                <i class=\"glyphicon glyphicon-tasks\"></i>\n" +
    "                {{ input.name }}\n" +
    "            </h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body>\n" +
    "            <div class=\"panel-body\">\n" +
    "                <ng-include src=\"'html/api/input-view.html'\" onload=\"index = $index; input = input\"></ng-include>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/input-filter/filter-new.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/input-filter/filter-new.html",
    "<div class=\"panel-body\">\n" +
    "    <select class=\"input-xlarge\" ui-select2=\"{placeholder: 'Select Filter', width: '300px' }\" type=\"text\" ng-model=\"input._newFilterName\">\n" +
    "        <option ng-repeat=\"(key, value) in filterOptions\" value=\"{{key}}\">{{key}}</option>\n" +
    "    </select>\n" +
    "\n" +
    "    <div class=\"btn-group pull-right\">\n" +
    "        <button \n" +
    "            type=\"button\" class=\"btn btn-sm btn-default\"\n" +
    "            collapse-flag flags=\"{newFilterFormVisible: false}\">Cancel</button>\n" +
    "        <button type=\"button\" class=\"btn btn-sm btn-primary\"\n" +
    "            ng-click=\"addFilter(input)\">Add Filter</button>\n" +
    "    </div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/api/input-view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/input-view.html",
    "<div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "  <label class=\"control-label\">Description</label>\n" +
    "  <p class=\"text-muted\">{{ input.description }}</p>\n" +
    "</div></div>\n" +
    "\n" +
    "<div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <span class=\"form-control-static input-xlarge pull-right\" ng-class=\"{true: 'text-info', false: 'text-muted'}[input.file_upload]\">\n" +
    "        <strong>{{input.file_upload && 'Yes' || 'No'}}</strong>\n" +
    "    </span>\n" +
    "\n" +
    "    <label class=\"control-label\">Is this field a file upload?</label>\n" +
    "</div></div>\n" +
    "\n" +
    "<div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <span class=\"form-control-static input-xlarge pull-right\" ng-class=\"{true: 'text-info', false: 'text-muted'}[input.required]\">\n" +
    "        <strong>{{input.required && 'Yes' || 'No'}}</strong>\n" +
    "    </span>\n" +
    "\n" +
    "    <label class=\"control-label\">Required</label>\n" +
    "</div></div>\n" +
    "\n" +
    "<div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <span class=\"form-control-static input-xlarge pull-right\" ng-class=\"{true: 'text-info', false: 'text-muted'}[input.allow_empty]\">\n" +
    "        <strong>{{input.allow_empty && 'Yes' || 'No'}}</strong>\n" +
    "    </span>\n" +
    "\n" +
    "    <label class=\"control-label\">Allow Empty</label>\n" +
    "</div></div>\n" +
    "\n" +
    "<div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <span class=\"form-control-static input-xlarge pull-right\" ng-class=\"{true: 'text-info', false: 'text-muted'}[input.continue_if_empty]\">\n" +
    "        <strong>{{input.continue_if_empty && 'Yes' || 'No'}}</strong>\n" +
    "    </span>\n" +
    "\n" +
    "    <label class=\"control-label\">Continue if Empty</label>\n" +
    "</div></div>\n" +
    "\n" +
    "<div ng-show=\"input.error_message\" class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <label class=\"control-label\">Validation Failure Message</label>\n" +
    "    <p class=\"text-muted\">{{ input.error_message }}</textarea>\n" +
    "</div></div>\n" +
    "\n" +
    "<div ng-show=\"input.filters.length > 0\">\n" +
    "<ag-collapse class=\"panel-warning\">\n" +
    "    <collapse-header>\n" +
    "        <h4 class=\"panel-title\">\n" +
    "            <i class=\"glyphicon glyphicon-filter\"></i>\n" +
    "            Filters\n" +
    "        </h4>\n" +
    "    </collapse-header>\n" +
    "    \n" +
    "    <collapse-body>\n" +
    "        <ag-collapse class=\"panel-blank\" ng-repeat=\"filter in input.filters\">\n" +
    "            <collapse-header>\n" +
    "                <h4 class=\"panel-title\">\n" +
    "                    {{filter.name}}\n" +
    "                </h4>\n" +
    "            </collapse-header>\n" +
    "        \n" +
    "            <collapse-body class=\"list-group in\">\n" +
    "                <li class=\"list-group-item\"\n" +
    "                    ng-repeat=\"(optionName, optionValue) in filter.options\">\n" +
    "                    <div class=\"form-group form-horizontal\">\n" +
    "                        <label class=\"control-label col-sm-4\">{{optionName}}:</label>\n" +
    "\n" +
    "                        <div class=\"col-sm-7\">\n" +
    "                            <p class=\"form-control-static input-xlarge\"\n" +
    "                                ng-show=\"filterOptions[filter.name][optionName] == 'bool'\"\n" +
    "                                ng-class=\"{true: 'text-info', false: 'text-muted'}[optionValue]\">\n" +
    "                                {{optionValue && 'Yes' || 'No'}}\n" +
    "                            </p>\n" +
    "                            <p \n" +
    "                                class=\"form-control-static input-xlarge\"\n" +
    "                                ng-show=\"filterOptions[filter.name][optionName] != 'bool'\">\n" +
    "                                {{ optionValue }}\n" +
    "                            </p>\n" +
    "                        </div>\n" +
    "                    </div>\n" +
    "                </li>\n" +
    "            </collapse-body>\n" +
    "        </ag-collapse>\n" +
    "    </collapse-body>\n" +
    "</ag-collapse>\n" +
    "</div>\n" +
    "\n" +
    "<div ng-show=\"input.validators.length > 0\">\n" +
    "<ag-collapse class=\"panel-warning\">\n" +
    "    <collapse-header>\n" +
    "        <h4 class=\"panel-title\">\n" +
    "            <i class=\"glyphicon glyphicon-ok\"></i>\n" +
    "            Validators\n" +
    "        </h4>\n" +
    "    </collapse-header>\n" +
    "\n" +
    "    <collapse-body>\n" +
    "        <ag-collapse class=\"panel panel-blank\" ng-repeat=\"validator in input.validators\">\n" +
    "            <collapse-header>\n" +
    "                <h4 class=\"panel-title\">\n" +
    "                    {{validator.name}}\n" +
    "                </h4>\n" +
    "            </collapse-header>\n" +
    "        \n" +
    "            <collapse-body class=\"list-group in\">\n" +
    "                <li class=\"list-group-item\" \n" +
    "                    ng-repeat=\"(optionName, optionValue) in validator.options\">\n" +
    "                    <div class=\"form-group form-horizontal\">\n" +
    "                        <label class=\"control-label col-sm-4\">{{optionName}}:</label>\n" +
    "\n" +
    "                        <div class=\"col-sm-7\">\n" +
    "                            <p class=\"form-control-static input-xlarge\"\n" +
    "                                ng-show=\"validatorOptions[validator.name][optionName] == 'bool'\"\n" +
    "                                ng-class=\"{true: 'text-info', false: 'text-muted'}[optionValue]\">\n" +
    "                                {{optionValue && 'Yes' || 'No'}}\n" +
    "                            </p>\n" +
    "                            <p \n" +
    "                                class=\"form-control-static input-xlarge\"\n" +
    "                                ng-show=\"validatorOptions[validator.name][optionName] != 'bool'\">\n" +
    "                                {{ optionValue }}\n" +
    "                            </p>\n" +
    "                        </div>\n" +
    "                    </div>\n" +
    "                </li>\n" +
    "            </collapse-body>\n" +
    "        </ag-collapse>\n" +
    "    </collapse-body>\n" +
    "</ag-collapse>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/overview.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/overview.html",
    "<div class=\"panel panel-info table-responsive\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">REST services</h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <table class=\"table body-striped\">\n" +
    "        <tbody ng-repeat=\"restService in api.restServices\">\n" +
    "            <tr>\n" +
    "                <td rowspan=\"2\" width=\"40%\">\n" +
    "                    <p><strong><a ui-sref=\".rest({service: restService.service_name})\">\n" +
    "                        {{ restService.service_name }}\n" +
    "                    </a></strong></p>\n" +
    "\n" +
    "                    <p class=\"text-muted\" ng-show=\"restService.documentation.description\">\n" +
    "                        {{ restService.documentation.description }}\n" +
    "                    </p>\n" +
    "\n" +
    "                    <p class=\"text-warning\" ng-hide=\"restService.documentation.description\">\n" +
    "                        <a ui-sref=\".rest({service: restService.service_name, view: 'documentation', edit: true})\">\n" +
    "                            Add a description for this service\n" +
    "                        </a>\n" +
    "                    </p>\n" +
    "                </td>\n" +
    "\n" +
    "                <td width=\"60%\"><a ui-sref=\".rest({service: restService.service_name})\">{{ restService.route_match }}</a></td>\n" +
    "            </tr>\n" +
    "        </tbody>\n" +
    "    </table>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"panel panel-info\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">RPC services</h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <table class=\"table body-striped\">\n" +
    "        <tbody ng-repeat=\"rpcService in api.rpcServices\">\n" +
    "            <tr>\n" +
    "                <td rowspan=\"2\" width=\"40%\">\n" +
    "                    <p><strong><a ui-sref=\".rpc({service: rpcService.service_name})\">\n" +
    "                        {{ rpcService.service_name }}\n" +
    "                    </a></strong></p>\n" +
    "\n" +
    "                    <p class=\"text-muted\" ng-show=\"rpcService.documentation.description\">\n" +
    "                        {{ rpcService.documentation.description }}\n" +
    "                    </p>\n" +
    "\n" +
    "                    <p class=\"text-warning\" ng-hide=\"rpcService.documentation.description\">\n" +
    "                        <a ui-sref=\".rpc({service: rpcService.service_name, view: 'documentation', edit: true})\">\n" +
    "                            Add a description for this service\n" +
    "                        </a>\n" +
    "                    </p>\n" +
    "                </td>\n" +
    "                <td width=\"60%\"><a ui-sref=\".rpc({service: rpcService.service_name})\">{{ rpcService.route_match }}</a></td>\n" +
    "            </tr>\n" +
    "        </tbody>\n" +
    "    </table>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"panel panel-info\" ng-controller=\"ApiVersionController\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title pull-left\">Versioning</h4>\n" +
    "        <span class=\"btn-group pull-right\">\n" +
    "            <button class=\"btn btn-primary btn-sm\" ng-click=\"createNewApiVersion()\">Create New Version</button>\n" +
    "        </span>\n" +
    "        <div class=\"clearfix\"></div>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel-body\">\n" +
    "        <form class=\"form-inline\">\n" +
    "            <select\n" +
    "                class=\"form-control input-xlarge pull-right\"\n" +
    "                id=\"default-api-version\"\n" +
    "                ng-model=\"defaultApiVersion\"\n" +
    "                ng-options=\"ver for ver in api.versions\"\n" +
    "                ng-change=\"setDefaultApiVersion()\"></select>\n" +
    "        </form>\n" +
    "\n" +
    "        <p>\n" +
    "            <strong>Current Default API Version</strong>\n" +
    "        </p>\n" +
    "\n" +
    "        <div class=\"clearfix\"></div>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"panel panel-danger\">\n" +
    "    <div class=\"panel-heading\" ng-click=\"deleteApiPanelIsCollapsed = !deleteApiPanelIsCollapsed\">\n" +
    "        <h4 class=\"panel-title\">Delete API</h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel-body\" collapse=\"deleteApiPanelIsCollapsed\">\n" +
    "        <p>Are you sure you want to delete this API?</p>\n" +
    "\n" +
    "        <p>\n" +
    "            By default, deleting the API only removes the API module from the\n" +
    "            application configuration. You can re-enable it by re-adding the\n" +
    "            module to your application configuration at a later date.\n" +
    "        </p>\n" +
    "\n" +
    "        <div class=\"form-group\">\n" +
    "            <label>\n" +
    "                <input type=\"checkbox\" ng-model=\"recursive\">\n" +
    "                Delete all files associated with this API?\n" +
    "            </label>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"btn-group pull-right\">\n" +
    "            <button type=\"button\" class=\"btn btn-sm btn-default\"\n" +
    "                ng-click=\"deleteApiPanelIsCollapsed = !deleteApiPanelIsCollapsed\">\n" +
    "                No\n" +
    "            </button>\n" +
    "\n" +
    "            <button type=\"button\" class=\"btn btn-sm btn-danger\"\n" +
    "                ng-click=\"removeApi(recursive)\">\n" +
    "                Yes\n" +
    "            </button>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"clearfix\"></div>\n" +
    "    </div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/api/rest-services/documentation-edit-collection-pane.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/documentation-edit-collection-pane.html",
    "<div class=\"panel-body\">\n" +
    "    <div class=\"form-group\">\n" +
    "        <label class=\"control-label\">Description</label>\n" +
    "        <div class=\"controls\">\n" +
    "            <textarea \n" +
    "                placeholder=\"Insert the Collection description here\"\n" +
    "                class=\"form-control input-xlarge\"\n" +
    "                required=\"\"\n" +
    "                rows=\"3\"\n" +
    "                ng-model=\"restService.documentation.collection.description\"></textarea>\n" +
    "            <p class=\"help-block\">The description of the Collection</p>\n" +
    "        </div>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <ag-tabs pills>\n" +
    "        <ag-tab-pane ng-repeat=\"method in restService.collection_http_methods\" title=\"{{method}}\" ng-init=\"methodData = restService.documentation.collection[method]; restPart = 'collection'\"><div class=\"panel-body\">\n" +
    "            <ag-tab-pane-variable-content content-template=\"'html/api/documentation-method-edit.html'\"></ag-tab-pane-variable-content>\n" +
    "        </div></ag-tab-pane>\n" +
    "    </ag-tabs>\n" +
    "    </div></div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/api/rest-services/documentation-edit-entity-pane.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/documentation-edit-entity-pane.html",
    "<div class=\"panel-body\">\n" +
    "    <div class=\"form-group\">\n" +
    "        <label class=\"control-label\">Description</label>\n" +
    "        <div class=\"controls\">\n" +
    "            <textarea \n" +
    "                placeholder=\"Insert the Entity description here\"\n" +
    "                class=\"form-control input-xlarge\"\n" +
    "                required=\"\"\n" +
    "                rows=\"3\"\n" +
    "                ng-model=\"restService.documentation.entity.description\"></textarea>\n" +
    "            <p class=\"help-block\">The description of the Entity</p>\n" +
    "        </div>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <ag-tabs pills>\n" +
    "        <ag-tab-pane ng-repeat=\"method in restService.entity_http_methods\" title=\"{{method}}\" ng-init=\"methodData = restService.documentation.entity[method]; restPart = 'entity'\"><div class=\"panel-body\">\n" +
    "            <ag-tab-pane-variable-content content-template=\"'html/api/documentation-method-edit.html'\"></ag-tab-pane-variable-content>\n" +
    "        </div></ag-tab-pane>\n" +
    "    </ag-tabs>\n" +
    "    </div></div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/api/rest-services/documentation-edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/documentation-edit.html",
    "<div ng-controller=\"ApiDocumentationController\">\n" +
    "    <form novalidate class=\"ag form\" ng-submit=\"save(restService.documentation)\" ag-form>\n" +
    "        <div class=\"panel panel-default\">\n" +
    "            <div class=\"panel-body\">\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\">REST service description</label>\n" +
    "                    <div class=\"controls\">\n" +
    "                        <textarea \n" +
    "                            placeholder=\"Insert the description here\"\n" +
    "                            class=\"form-control input-xlarge\"\n" +
    "                            required=\"\"\n" +
    "                            rows=\"5\"\n" +
    "                            ng-model=\"restService.documentation.description\"></textarea>\n" +
    "                        <p class=\"help-block\">The general description of the REST service</p>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "                <ag-tabs pills>\n" +
    "                    <ag-tab-pane title=\"Collection\">\n" +
    "                        <ag-tab-pane-variable-content content-template=\"'html/api/rest-services/documentation-edit-collection-pane.html'\"></ag-tab-pane-variable-content>\n" +
    "                    </ag-tab-pane>\n" +
    "\n" +
    "                    <ag-tab-pane title=\"Entity\">\n" +
    "                        <ag-tab-pane-variable-content content-template=\"'html/api/rest-services/documentation-edit-entity-pane.html'\"></ag-tab-pane-variable-content>\n" +
    "                    </ag-tab-pane>\n" +
    "                </ag-tabs>\n" +
    "                </div></div>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"ag-new-input btn-group pull-right\">\n" +
    "            <button type=\"button\" class=\"btn btn-sm btn-default\" ag-cancel-edit>Cancel</button>\n" +
    "            <button type=\"submit\" class=\"btn btn-sm btn-success\">Save</button>\n" +
    "        </div>\n" +
    "    </form>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/api/rest-services/documentation-view-collection-pane.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/documentation-view-collection-pane.html",
    "<div class=\"panel-body\">\n" +
    "    <div ng-show=\"restService.documentation.collection.description\">\n" +
    "        <div ng-bind-html=\"restService.documentation.collection.description\"></div>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"text-muted\" ng-hide=\"restService.documentation.collection.description\">\n" +
    "        No collection description yet provided.\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <ag-tabs pills>\n" +
    "        <ag-tab-pane \n" +
    "            title=\"{{method}}\"\n" +
    "            ng-repeat=\"method in restService.collection_http_methods\"\n" +
    "            ng-init=\"methodData = restService.documentation.collection[method]; restPart = 'collection'\"><div class=\"panel-body\">\n" +
    "            <ag-tab-pane-variable-content content-template=\"'html/api/documentation-method-view.html'\"></ag-tab-pane-variable-content>\n" +
    "        </div></ag-tab-pane>\n" +
    "    </ag-tabs>\n" +
    "    </div></div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/api/rest-services/documentation-view-entity-pane.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/documentation-view-entity-pane.html",
    "<div class=\"panel-body\">\n" +
    "    <div ng-show=\"restService.documentation.entity.description\">\n" +
    "        <div ng-bind-html=\"restService.documentation.entity.description\"></div>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"text-muted\" ng-hide=\"restService.documentation.entity.description\">\n" +
    "        No entity description yet provided.\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "    <ag-tabs pills>\n" +
    "        <ag-tab-pane ng-repeat=\"method in restService.entity_http_methods\" title=\"{{method}}\" ng-init=\"methodData = restService.documentation.entity[method]; restPart = 'entity'; service=restService\"><div class=\"panel-body\">\n" +
    "            <ag-tab-pane-variable-content content-template=\"'html/api/documentation-method-view.html'\"></ag-tab-pane-variable-content>\n" +
    "        </div></ag-tab-pane>\n" +
    "    </ag-tabs>\n" +
    "    </div></div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/api/rest-services/documentation-view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/documentation-view.html",
    "<div ng-controller=\"ApiDocumentationController\">\n" +
    "    <div class=\"panel panel-default\">\n" +
    "        <div class=\"panel-body\">\n" +
    "            <h3>{{ restService.service_name }}: {{ restService.route_match }}</h3>\n" +
    "\n" +
    "            <div ng-show=\"restService.documentation.description\">\n" +
    "                <div ng-bind-html=\"restService.documentation.description\"></div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"text-muted\" ng-hide=\"restService.documentation.description\">\n" +
    "                No service description yet provided.\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "            <ag-tabs pills>\n" +
    "                <ag-tab-pane title=\"Collection\">\n" +
    "                    <ag-tab-pane-variable-content content-template=\"'html/api/rest-services/documentation-view-collection-pane.html'\"></ag-tab-pane-variable-content>\n" +
    "                </ag-tab-pane>\n" +
    "\n" +
    "                <ag-tab-pane title=\"Entity\">\n" +
    "                    <ag-tab-pane-variable-content content-template=\"'html/api/rest-services/documentation-view-entity-pane.html'\"></ag-tab-pane-variable-content>\n" +
    "                </ag-tab-pane>\n" +
    "            </ag-tabs>\n" +
    "            </div></div>\n" +
    "        </div>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/rest-services/edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/edit.html",
    "<ag-tabs class=\"panel-body\">\n" +
    "    <ag-tab-pane name=\"settings\" active=\"{{ view == 'settings' }}\" searchparam=\"view\" title=\"Settings\">\n" +
    "        <ag-tab-pane-variable-content content-template=\"'html/api/rest-services/settings-edit.html'\"></ag-tab-pane-variable-content>\n" +
    "    </ag-tab-pane>\n" +
    "\n" +
    "    <ag-tab-pane name=\"fields\" active=\"{{ view == 'fields' }}\" searchparam=\"view\" title=\"Fields\">\n" +
    "        <ag-tab-pane-variable-content content-template=\"'html/api/input-filter-edit.html'\"\n" +
    "            onload=\"index = $index\"></ag-tab-pane-variable-content>\n" +
    "    </ag-tab-pane>\n" +
    "\n" +
    "    <ag-tab-pane name=\"documentation\" active=\"{{ view == 'documentation' }}\" searchparam=\"view\" title=\"Documentation\">\n" +
    "        <ag-tab-pane-variable-content content-template=\"'html/api/rest-services/documentation-edit.html'\"></ag-tab-pane-variable-content>\n" +
    "    </ag-tab-pane>\n" +
    "</ag-tabs>\n" +
    "");
}]);

angular.module("html/api/rest-services/index.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/index.html",
    "<ag-include src=\"html/api/rest-services/new.html\"></ag-include>\n" +
    "\n" +
    "<div ng-show=\"!api.restServices.length\" class=\"text-muted\">\n" +
    "    No REST services defined.\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"panel-group tooltip-api\">\n" +
    "    <ag-collapse class=\"panel-info service\" \n" +
    "        ng-repeat=\"restService in api.restServices\"\n" +
    "        active=\"{{ activeService == restService.service_name }}\"\n" +
    "        name=\"{{ restService.service_name }}\"\n" +
    "        data-api=\"{{ api.name }}\"\n" +
    "        data-api-version=\"{{ version }}\"\n" +
    "        data-service-type=\"REST\"\n" +
    "        searchparam=\"service\"\n" +
    "        conditionals=\"{{ {edit: inEdit, delete: false} }}\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title pull-left\">\n" +
    "                <span class=\"glyphicon glyphicon-leaf\"></span> {{ restService.service_name }}\n" +
    "            </h4>\n" +
    "            <div class=\"btn-group pull-right service-buttons\" ng-show=\"isLatestVersion()\">\n" +
    "                <button\n" +
    "                    type=\"button\" title=\"Cancel\" class=\"btn btn-sm btn-default\"\n" +
    "                    collapse-button criteria=\"{delete: false}\"\n" +
    "                    ng-show=\"inEdit\"\n" +
    "                    ng-click=\"toggleEditState(restService.service_name, false)\">\n" +
    "                    Cancel\n" +
    "                </button>\n" +
    "\n" +
    "                <button \n" +
    "                    type=\"button\" class=\"btn btn-sm btn-success\" title=\"Edit service\"\n" +
    "                    collapse-button criteria=\"{delete: false}\"\n" +
    "                    ng-show=\"!inEdit\"\n" +
    "                    ng-click=\"toggleEditState(restService.service_name, true)\">\n" +
    "                    <i class=\"glyphicon glyphicon-edit\"></i>\n" +
    "                </button>\n" +
    "\n" +
    "                <button \n" +
    "                    type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Remove service\"\n" +
    "                    collapse-flag flags=\"{delete: true}\"\n" +
    "                    collapse-button criteria=\"{delete: false}\">\n" +
    "                    <i class=\"glyphicon glyphicon-trash\"></i>\n" +
    "                </button>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"clearfix\"></div>\n" +
    "\n" +
    "            <span collapse-show\n" +
    "                criteria=\"{delete: false}\"\n" +
    "                default-template=\"'html/empty-content.html'\"\n" +
    "                toggled-template=\"'html/api/rest-services/remove.html'\"></span>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body>\n" +
    "            <ag-toggle-include\n" +
    "                condition=\"inEdit\"\n" +
    "                on-template=\"html/api/rest-services/edit.html\"\n" +
    "                off-template=\"html/api/rest-services/view.html\"></ag-toggle-include>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/rest-services/new.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/new.html",
    "<div ng-hide=\"showNewRestServiceForm || !isLatestVersion()\">\n" +
    "    <button \n" +
    "        class=\"btn btn-default btn-primary pull-right\" title=\"Create New REST Service\"\n" +
    "        ng-click=\"showNewRestServiceForm = true\">\n" +
    "        Create New REST Service\n" +
    "    </button>\n" +
    "\n" +
    "    <div class=\"clearfix\"></div>\n" +
    "\n" +
    "    <br />\n" +
    "</div>\n" +
    "\n" +
    "<div ng-show=\"showNewRestServiceForm\" class=\"panel panel-primary\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">Create a new REST service</h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel-body\">\n" +
    "        <ag-tabs parent=\"newService\">\n" +
    "            <ag-tab-pane title=\"Code-Connected\"><div class=\"panel\"><div class=\"panel-body\">\n" +
    "                <form class=\"form\" ng-submit=\"newService.createNewRestService()\" ag-form>\n" +
    "                    <fieldset>\n" +
    "                        <div class=\"form-group\">\n" +
    "                            <label class=\"control-label\">REST Service Name</label>\n" +
    "                            <input class=\"form-control input-xlarge\" type=\"text\" ng-model=\"newService.restServiceName\" placeholder=\"REST Service Name ...\" required=\"\">\n" +
    "                        </div>\n" +
    "                    </fieldset>\n" +
    "\n" +
    "                    <div class=\"btn-group pull-right\">\n" +
    "                        <button\n" +
    "                            type=\"button\" class=\"btn btn-sm btn-default\"\n" +
    "                            ng-click=\"resetForm()\">Cancel</button>\n" +
    "                        <button type=\"submit\" class=\"btn btn-sm btn-primary\">\n" +
    "                            Create Code-Connected REST Service</button>\n" +
    "                    </div>\n" +
    "\n" +
    "                    <div class=\"clearfix\"></div>\n" +
    "                </form>\n" +
    "            </div></div></ag-tab-pane>\n" +
    "\n" +
    "            <ag-tab-pane title=\"DB-Connected\"><div class=\"panel\"><div class=\"panel-body\">\n" +
    "                <div class=\"panel panel-warning\" ng-show=\"dbAdapters.length < 1\">\n" +
    "                    <div class=\"panel-heading\">\n" +
    "                        <h4 class=\"panel-title\">No DB Adapters Present</h4>\n" +
    "                    </div>\n" +
    "\n" +
    "                    <div class=\"panel-body\">\n" +
    "                        <p>\n" +
    "                            You have not yet configured any database adapters, and\n" +
    "                            thus cannot create a DB-Connected REST service.\n" +
    "                        </p>\n" +
    "\n" +
    "                        <p>\n" +
    "                            You can create adapters on the \n" +
    "                            <a ui-sref=\"ag.settings.db-adapters\">Database Adapters setting page</a>.\n" +
    "                        </p>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "\n" +
    "                <form class=\"form\" \n" +
    "                    ng-show=\"dbAdapters.length > 0\"\n" +
    "                    ng-submit=\"newService.createNewDbConnectedService()\"\n" +
    "                    ag-form>\n" +
    "                    <fieldset>\n" +
    "                        <div class=\"form-group\">\n" +
    "                            <label class=\"control-label\">DB Adapter Name</label>\n" +
    "                            <select class=\"form-control input-xlarge\" ng-model=\"newService.dbAdapterName\" ng-options=\"v.adapter_name as v.adapter_name for v in dbAdapters\"></select>\n" +
    "                        </div>\n" +
    "\n" +
    "                        <div class=\"form-group\">\n" +
    "                            <label class=\"control-label\">Table Name</label>\n" +
    "                            <input class=\"form-control input-xlarge\" type=\"text\" ng-model=\"newService.dbTableName\" placeholder=\"DB Table Name ...\">\n" +
    "                        </div>\n" +
    "                    </fieldset>\n" +
    "\n" +
    "                    <div class=\"btn-group pull-right\">\n" +
    "                        <button\n" +
    "                            type=\"button\" class=\"btn btn-sm btn-default\"\n" +
    "                            ng-click=\"resetForm()\">Cancel</button>\n" +
    "                        <button type=\"submit\" class=\"btn btn-sm btn-primary\">\n" +
    "                            Create DB-Connected REST Service</button>\n" +
    "                    </div>\n" +
    "\n" +
    "                    <div class=\"clearfix\"></div>\n" +
    "                </form>\n" +
    "            </div></div></ag-tab-pane>\n" +
    "        </ag-tabs>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/rest-services/remove.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/remove.html",
    "<div class=\"panel panel-danger\">\n" +
    "    <div class=\"panel-heading\">Remove REST service</div>\n" +
    "\n" +
    "    <div class=\"panel-body\">\n" +
    "        <p>Are you sure you want to delete the service?</p>\n" +
    "\n" +
    "        <div class=\"form-group\">\n" +
    "            <label>\n" +
    "                <input type=\"checkbox\" ng-model=\"recursive\">\n" +
    "                Delete all files and directories for this service?\n" +
    "            </label>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"btn-group pull-right\">\n" +
    "            <button type=\"button\" class=\"btn btn-sm btn-default\"\n" +
    "                collapse-flag flags=\"{delete: false}\">\n" +
    "                No\n" +
    "            </button>\n" +
    "            <button type=\"button\" class=\"btn btn-sm btn-danger\"\n" +
    "                ng-click=\"removeRestService(restService.controller_service_name, recursive)\">\n" +
    "                Yes\n" +
    "            </button>\n" +
    "        </div>\n" +
    "    </div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/api/rest-services/settings-edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/settings-edit.html",
    "<form novalidate class=\"ag form\" ng-submit=\"saveRestService($index)\" ag-form>\n" +
    "<fieldset class=\"panel-group\">\n" +
    "    <ag-collapse class=\"panel-success\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">General Settings</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group in\">\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">Route to match</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <input type=\"text\" placeholder=\"/api/your_resource\" class=\"form-control input-xlarge\" required=\"\" ng-model=\"restService.route_match\">\n" +
    "                    <p class=\"help-block\">\n" +
    "                        The URI for the service; \"[&nbsp;]\" indicate optional\n" +
    "                        segments, and \":varname\" indicates a variable URI segment\n" +
    "                        to capture.\n" +
    "                    </p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">Number of entities to display per page of a collection</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <input type=\"text\" placeholder=\"25\" class=\"form-control input-xlarge\" ng-model=\"restService.page_size\">\n" +
    "                    <p class=\"help-block\">Indicate the number of entities that should be displayed per page when GET requests are made to the collection.</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">HTTP methods allowed for ENTITIES</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <label ng-repeat=\"method in ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']\" class=\"checkbox inline\">\n" +
    "                        <input type=\"checkbox\" name=\"entity_http_methods_test\" value=\"{{method}}\" ng-checked=\"restService.entity_http_methods.indexOf(method) > -1\" ng-click=\"toggleSelection(restService.entity_http_methods, $event)\">\n" +
    "                        <span>{{method}}</span>\n" +
    "                    </label>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">HTTP methods allowed for COLLECTIONS</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <label ng-repeat=\"method in ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']\" class=\"checkbox inline\">\n" +
    "                        <input type=\"checkbox\" value=\"{{method}}\" ng-checked=\"restService.collection_http_methods.indexOf(method) > -1\" ng-click=\"toggleSelection(restService.collection_http_methods, $event)\">\n" +
    "                        <span>{{method}}</span>\n" +
    "                    </label>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <ag-collapse class=\"panel-success\" show=\"isDbConnected(restService)\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">Database Settings</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group\">\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\" for=\"selector\">DB Adapter Name</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <select\n" +
    "                        class=\"form-control input-xlarge\"\n" +
    "                        ng-model=\"restService.adapter_name\"\n" +
    "                        ng-options=\"v.adapter_name as v.adapter_name for v in dbAdapters\"></select>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\" for=\"table_name\">DB Table Name</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <input type=\"text\" placeholder=\"Table Name\" class=\"form-control input-xlarge\" required=\"\" ng-model=\"restService.table_name\">\n" +
    "                    <p class=\"help-block\">The name of the database table used by this endpoint</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\" for=\"table_service\">TableGateway Service Name</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <input type=\"text\" placeholder=\"TableGateway Service Name\" class=\"form-control input-xlarge\" required=\"\" ng-model=\"restService.table_service\">\n" +
    "                    <p class=\"help-block\">The name of the TableGateway service used by this endpoint; change only if you have created your own implementation.</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <ag-collapse class=\"panel-success\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">REST Parameters</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group\">\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">Route Identifier Name</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <input type=\"text\" placeholder=\"id\" class=\"form-control input-xlarge\" required=\"\" ng-model=\"restService.route_identifier_name\">\n" +
    "                    <p class=\"help-block\">Name of the route parameter representing the unique identifier</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">Entity Identifier Name</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <input type=\"text\" placeholder=\"id\" class=\"form-control input-xlarge\" required=\"\" ng-model=\"restService.entity_identifier_name\">\n" +
    "                    <p class=\"help-block\">Name of the field in the PHP entity representing the unique identifier</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\" for=\"hydrator_name\">Hydrator Service Name</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <select class=\"form-control input-xlarge\"\n" +
    "                        ng-model=\"restService.hydrator_name\"\n" +
    "                        ng-options=\"v for v in hydrators\"></select>\n" +
    "                    <p class=\"help-block\">The name of the hydrator service used to hydrate entities</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">Collection Name</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <input type=\"text\" placeholder=\"id\" class=\"form-control input-xlarge\" required=\"\" ng-model=\"restService.collection_name\">\n" +
    "                    <p class=\"help-block\">Name of the field representing the collection in the response.</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\" for=\"page_size_param\">Page size parameter</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <input type=\"text\" placeholder=\"\" class=\"form-control input-xlarge\" ng-model=\"restService.page_size_param\">\n" +
    "                    <p class=\"help-block\">The query string parameter that will represent the number of results per page when retrieving a collection.</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">Collection Query String whitelist</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <tags-input\n" +
    "                        custom-class=\"ag-tags\"\n" +
    "                        ng-model=\"restService.collection_query_whitelist\"\n" +
    "                        add-on-space=\"true\"\n" +
    "                        max-length=\"256\"\n" +
    "                        allowed-tags-pattern=\"^[a-zA-Z0-9_+.-]+$\"\n" +
    "                        placeholder=\"Add a variable to the whitelist\">\n" +
    "                    </tags-input>\n" +
    "                    <p class=\"help-block\">Specify query string variables that\n" +
    "                    should be represented in relational links for collections\n" +
    "                    of this resource (e.g., \"filter\", \"sort\", \"version\").</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <ag-collapse class=\"panel-success\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">Content Negotiation</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group\">\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">Content Negotiation Selector</label>\n" +
    "                    <div class=\"controls\">\n" +
    "                    <select class=\"form-control input-xlarge\" ng-model=\"restService.selector\" ng-options=\"v as v for (k,v) in selectors\"></select>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">Accept whitelist</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <tags-input\n" +
    "                        custom-class=\"ag-tags\"\n" +
    "                        ng-model=\"restService.accept_whitelist\"\n" +
    "                        add-on-space=\"true\"\n" +
    "                        max-length=\"256\"\n" +
    "                        allowed-tags-pattern=\"^[a-zA-Z-]+/[a-zA-Z0-9*_+.-]+$\"\n" +
    "                        placeholder=\"Add a mediatype to the whitelist\">\n" +
    "                    </tags-input>\n" +
    "                    <p class=\"help-block\">Specify mediatypes for representations this API can provide (tied to Accept request header)</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">Content-Type whitelist</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <tags-input\n" +
    "                        custom-class=\"ag-tags\"\n" +
    "                        ng-model=\"restService.content_type_whitelist\"\n" +
    "                        add-on-space=\"true\"\n" +
    "                        max-length=\"256\"\n" +
    "                        allowed-tags-pattern=\"^[a-zA-Z-]+/[a-zA-Z0-9*_+.-]+$\"\n" +
    "                        placeholder=\"Add a mediatype to the whitelist\">\n" +
    "                    </tags-input>\n" +
    "                    <p class=\"help-block\">Specify mediatypes allowed for submitted content (tied to the Content-Type request header)</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <ag-collapse class=\"panel-success\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">Service Class Names</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group\">\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\" for=\"entity_class\">Entity Class</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <input type=\"text\" placeholder=\"\" class=\"form-control input-xlarge\" ng-model=\"restService.entity_class\">\n" +
    "                    <p class=\"help-block\">The fully qualified class name of the\n" +
    "                    class representing an entity for this service.</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\" for=\"collection_class\">Collection Class</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <input type=\"text\" placeholder=\"\" class=\"form-control input-xlarge\" ng-model=\"restService.collection_class\">\n" +
    "                    <p class=\"help-block\">The fully qualified class name of the\n" +
    "                    class representing a collection for this service.</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "</fieldset>\n" +
    "\n" +
    "<div class=\"ag-new-input btn-group pull-right\">\n" +
    "    <!-- todo Make the \"cancel\" action revert  -->\n" +
    "    <button type=\"button\" class=\"btn btn-sm btn-default\" ng-click=\"toggleEditState(restService.service_name, false)\">Cancel</button>\n" +
    "    <button type=\"submit\" class=\"btn btn-sm btn-success\">Save</button>\n" +
    "</div>\n" +
    "</form>\n" +
    "");
}]);

angular.module("html/api/rest-services/settings-view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/settings-view.html",
    "<div class=\"panel-group tooltip-api\">\n" +
    "    <ag-collapse class=\"panel-default\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">General Settings</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group in\">\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Route matches:</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.route_match }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Page Size</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.page_size }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">HTTP Allowed Entity Methods</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.entity_http_methods.join(', ') }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">HTTP Allowed Collection Methods</h4>\n" +
    "                <p class=\"list-group-item-text\">\n" +
    "                {{ restService.collection_http_methods.join(', ') }}\n" +
    "                </p>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <ag-collapse class=\"panel-default\" show=\"isDbConnected(restService)\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">Database Settings</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group\">\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">DB Adapter Name</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.adapter_name }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">DB Table Name</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.table_name }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Table Gateway Service Name</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.table_service }}</p>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <ag-collapse class=\"panel-default\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">REST Parameters</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group\">\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Route Identifier Name</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.route_identifier_name || 'n/a' }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Entity Identifier Name</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.entity_identifier_name || 'n/a' }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Hydrator Service Name</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.hydrator_name }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Collection Name</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.collection_name }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Page Size Parameter (Query string)</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.page_size_param }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Collection Query String whitelist</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.collection_query_whitelist.join(', ') || 'n/a' }}</p>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <ag-collapse class=\"panel-default\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">Content Negotiation</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group\">\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Content Negotiation Selector</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.selector }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Accept whitelist</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.accept_whitelist.join(', ') || 'n/a' }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Content-Type whitelist</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.content_type_whitelist.join(', ') || 'n/a' }}</p>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <ag-collapse class=\"panel-default\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">Service/Class/Route Names</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group\">\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Controller Service Name</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.controller_service_name | controllerservicename }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Resource Class</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.resource_class }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Entity Class</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.entity_class }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Collection Class</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.collection_class }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Route Name</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ restService.route_name }}</p>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/rest-services/source-code.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/source-code.html",
    "<div class=\"panel panel-default\">\n" +
    "    <div class=\"panel-body\">\n" +
    "        <p>Click on the file name to show the source code.</p>\n" +
    "    </div>\n" +
    "\n" +
    "    <table class=\"table table-striped\">\n" +
    "        <tr>\n" +
    "            <td>Collection Class</td>\n" +
    "            <td><button \n" +
    "                data-doclink=\"{{restService.collection_class}}\"\n" +
    "                class=\"btn-link\"\n" +
    "                ng-click=\"getSourceCode(restService.collection_class, 'Collection')\">{{ restService.collection_class }}.php</button></td>\n" +
    "        </tr>\n" +
    "\n" +
    "        <tr>\n" +
    "            <td>Entity Class</td>\n" +
    "            <td><button\n" +
    "                data-doclink=\"{{restService.entity_class}}\"\n" +
    "                class=\"btn-link\"\n" +
    "                ng-click=\"getSourceCode(restService.entity_class, 'Entity')\">{{ restService.entity_class }}.php</button></td>\n" +
    "        </tr>\n" +
    "\n" +
    "        <tr ng-show=\"restService.resource_class\">\n" +
    "            <td>Resource Class</td>\n" +
    "            <td><button\n" +
    "                data-doclink=\"{{restService.resource_class}}\"\n" +
    "                class=\"btn-link\"\n" +
    "                ng-click=\"getSourceCode(restService.resource_class, 'Resource')\">{{ restService.resource_class }}.php</td>\n" +
    "        </tr>\n" +
    "    </table>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/rest-services/view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rest-services/view.html",
    "<ag-tabs class=\"panel-body\">\n" +
    "    <ag-tab-pane name=\"settings\" active=\"{{ view == 'settings' }}\" searchparam=\"view\" title=\"Settings\">\n" +
    "        <ag-tab-pane-variable-content content-template=\"'html/api/rest-services/settings-view.html'\"></ag-tab-pane-variable-content>\n" +
    "    </ag-tab-pane>\n" +
    "\n" +
    "    <ag-tab-pane name=\"fields\" active=\"{{ view == 'fields' }}\" searchparam=\"view\" title=\"Fields\">\n" +
    "        <ag-tab-pane-variable-content content-template=\"'html/api/input-filter-view.html'\"\n" +
    "            onload=\"index = $index\"></ag-tab-pane-variable-content>\n" +
    "    </ag-tab-pane>\n" +
    "\n" +
    "    <ag-tab-pane name=\"documentation\" active=\"{{ view == 'documentation' }}\" searchparam=\"view\" title=\"Documentation\">\n" +
    "        <ag-tab-pane-variable-content content-template=\"'html/api/rest-services/documentation-view.html'\"\n" +
    "            onload=\"index = $index\"></ag-tab-pane-variable-content>\n" +
    "    </ag-tab-pane>\n" +
    "\n" +
    "    <ag-tab-pane name=\"source\" active=\"{{ view == 'source' }}\" searchparam=\"view\" title=\"Source Code\">\n" +
    "        <ag-tab-pane-variable-content \n" +
    "            content-template=\"'html/api/rest-services/source-code.html'\"\n" +
    "            onload=\"index = $index; service = restService\"></ag-tab-pane-variable-content>\n" +
    "    </ag-tab-pane>\n" +
    "</ag-tabs>\n" +
    "");
}]);

angular.module("html/api/rpc-services/documentation-edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rpc-services/documentation-edit.html",
    "<div ng-controller=\"ApiDocumentationController\">\n" +
    "    <form novalidate class=\"ag form\" ng-submit=\"save(rpcService.documentation)\" ag-form>\n" +
    "        <div class=\"panel panel-default\">\n" +
    "            <div class=\"panel-body list-group\">\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\">Description</label>\n" +
    "                    <div class=\"controls\">\n" +
    "                        <textarea \n" +
    "                            placeholder=\"Insert the description here\"\n" +
    "                            class=\"form-control input-xlarge\"\n" +
    "                            required=\"\"\n" +
    "                            rows=\"5\"\n" +
    "                            ng-model=\"rpcService.documentation.description\"></textarea>\n" +
    "                        <p class=\"help-block\">The description of the RPC Service</p>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "                <ag-tabs pills>\n" +
    "                    <ag-tab-pane ng-repeat=\"method in rpcService.http_methods\" title=\"{{method}}\" ng-init=\"methodData = rpcService.documentation[method]\"><div class=\"panel-body\">\n" +
    "                        <ag-tab-pane-variable-content content-template=\"'html/api/documentation-method-edit.html'\"></ag-tab-pane-variable-content>\n" +
    "                    </div></ag-tab-pane>\n" +
    "                </ag-tabs>\n" +
    "                </div></div>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"ag-new-input btn-group pull-right\">\n" +
    "            <button type=\"button\" class=\"btn btn-sm btn-default\" ag-cancel-edit>Cancel</button>\n" +
    "            <button type=\"submit\" class=\"btn btn-sm btn-success\">Save</button>\n" +
    "        </div>\n" +
    "    </form>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/api/rpc-services/documentation-view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rpc-services/documentation-view.html",
    "<div ng-controller=\"ApiDocumentationController\">\n" +
    "    <div class=\"panel panel-default\">\n" +
    "        <div class=\"panel-body\">\n" +
    "            <h3>{{ rpcService.service_name }}: {{ rpcService.route_match }}</h3>\n" +
    "\n" +
    "            <div class=\"control-group\">\n" +
    "                <label class=\"control-label\">Description</label>\n" +
    "                <div ng-show=\"rpcService.documentation.description\"><div ng-bind-html=\"rpcService.documentation.description\"></div></div>\n" +
    "                <div ng-hide=\"rpcService.documentation.description\" class=\"text-muted\">No service description yet provided.</div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"panel panel-default\"><div class=\"panel-body\">\n" +
    "            <ag-tabs pills>\n" +
    "                <ag-tab-pane ng-repeat=\"method in rpcService.http_methods\" title=\"{{method}}\" ng-init=\"methodData = rpcService.documentation[method]; service=rpcService\"><div class=\"panel-body\">\n" +
    "                    <ag-tab-pane-variable-content content-template=\"'html/api/documentation-method-view.html'\"></ag-tab-pane-variable-content>\n" +
    "                </div></ag-tab-pane>\n" +
    "            </ag-tabs>\n" +
    "            </div></div>\n" +
    "        </div>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/rpc-services/edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rpc-services/edit.html",
    "<ag-tabs>\n" +
    "    <ag-tab-pane name=\"settings\" active=\"{{ view == 'settings' }}\" searchparam=\"view\" title=\"Settings\">\n" +
    "        <ng-include src=\"'html/api/rpc-services/settings-edit.html'\"></ng-include>\n" +
    "    </ag-tab-pane>\n" +
    "\n" +
    "    <ag-tab-pane name=\"fields\" active=\"{{ view == 'fields' }}\" searchparam=\"view\" title=\"Fields\">\n" +
    "        <ng-include src=\"'html/api/input-filter-edit.html'\"\n" +
    "            onload=\"index = $index\"></ng-include>\n" +
    "    </ag-tab-pane>\n" +
    "\n" +
    "    <ag-tab-pane name=\"documentation\" active=\"{{ view == 'documentation' }}\" searchparam=\"view\" title=\"Documentation\">\n" +
    "        <ag-include src=\"html/api/rpc-services/documentation-edit.html\"></ag-include>\n" +
    "    </ag-tab-pane>\n" +
    "</ag-tabs>\n" +
    "");
}]);

angular.module("html/api/rpc-services/index.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rpc-services/index.html",
    "<ag-include src=\"html/api/rpc-services/new.html\"></ag-include>\n" +
    "\n" +
    "<div ng-show=\"!api.rpcServices.length\" class=\"text-muted\">\n" +
    "    No RPC services defined.\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"panel-group tooltip-api\" id=\"accordion\">\n" +
    "    <ag-collapse class=\"panel-info service\"\n" +
    "        ng-repeat=\"rpcService in api.rpcServices\"\n" +
    "        active=\"{{ activeService == rpcService.service_name }}\"\n" +
    "        name=\"{{ rpcService.service_name }}\"\n" +
    "        data-api=\"{{ api.name }}\"\n" +
    "        data-api-version=\"{{ version }}\"\n" +
    "        data-service-type=\"RPC\"\n" +
    "        searchparam=\"service\"\n" +
    "        conditionals=\"{{ {edit: inEdit, delete: false} }}\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">\n" +
    "                <i class=\"btn btn-small glyphicon glyphicon-fire\"></i>\n" +
    "\n" +
    "                {{ rpcService.service_name }}\n" +
    "\n" +
    "                <div class=\"btn-group pull-right service-buttons\" ng-show=\"isLatestVersion()\">\n" +
    "                    <button\n" +
    "                        type=\"button\" title=\"Cancel\" class=\"btn btn-sm btn-default\"\n" +
    "                        ng-show=\"inEdit\"\n" +
    "                        collapse-button criteria=\"{delete: false}\"\n" +
    "                        ng-click=\"toggleEditState(rpcService.service_name, false)\">\n" +
    "                        Cancel\n" +
    "                    </button>\n" +
    "\n" +
    "                    <button \n" +
    "                        type=\"button\" class=\"btn btn-sm btn-success\" title=\"Edit service\"\n" +
    "                        ng-show=\"!inEdit\"\n" +
    "                        collapse-button criteria=\"{delete: false}\"\n" +
    "                        ng-click=\"toggleEditState(rpcService.service_name, true)\">\n" +
    "                        <i class=\"glyphicon glyphicon-edit\"></i>\n" +
    "                    </button>\n" +
    "\n" +
    "                    <button \n" +
    "                        type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Remove service\"\n" +
    "                        collapse-flag flags=\"{delete: true}\"\n" +
    "                        collapse-button criteria=\"{delete: false}\">\n" +
    "                        <i class=\"glyphicon glyphicon-trash\"></i>\n" +
    "                    </button>\n" +
    "                </div>\n" +
    "            </h4>\n" +
    "\n" +
    "            <div class=\"clearfix\"></div>\n" +
    "\n" +
    "            <span collapse-show\n" +
    "                criteria=\"{delete: false}\"\n" +
    "                default-template=\"'html/empty-content.html'\"\n" +
    "                toggled-template=\"'html/api/rpc-services/remove.html'\"></span>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body ng-class=\"{in: (activeService == rpcService.service_name)}\">\n" +
    "            <ag-toggle-include\n" +
    "                condition=\"inEdit\"\n" +
    "                on-template=\"html/api/rpc-services/edit.html\"\n" +
    "                off-template=\"html/api/rpc-services/view.html\"></ag-toggle-include>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/rpc-services/new.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rpc-services/new.html",
    "<div ng-hide=\"showNewRpcServiceForm || !isLatestVersion()\">\n" +
    "    <button \n" +
    "        class=\"btn btn-default btn-primary pull-right\" title=\"Create New RPC Service\" position=\"bottom\"\n" +
    "        ng-click=\"showNewRpcServiceForm = true\">\n" +
    "        Create New RPC Service\n" +
    "    </button>\n" +
    "\n" +
    "    <div class=\"clearfix\"></div>\n" +
    "\n" +
    "    <br />\n" +
    "</div>\n" +
    "\n" +
    "\n" +
    "<div ng-show=\"showNewRpcServiceForm\" class=\"panel panel-primary\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">Create a new RPC service</h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel-body\">\n" +
    "        <form class=\"form\" ng-submit=\"createNewRpcService()\" ag-form>\n" +
    "            <fieldset>\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\">RPC Service Name</label>\n" +
    "                    <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"rpcServiceName\" placeholder=\"RPC Service Name ...\" required=\"\">\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\">Route to match</label>\n" +
    "                    <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"rpcServiceRoute\" placeholder=\"Route to match\">\n" +
    "                </div>\n" +
    "            </fieldset>\n" +
    "\n" +
    "            <div class=\"btn-group pull-right\">\n" +
    "                <button\n" +
    "                    type=\"button\" class=\"btn btn-sm btn-default\"\n" +
    "                    ng-click=\"resetForm()\">Cancel</button>\n" +
    "                <button type=\"submit\" class=\"btn btn-sm btn-primary\">Create RPC Service</button>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"clearfix\"></div>\n" +
    "        </form>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/api/rpc-services/remove.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rpc-services/remove.html",
    "<div class=\"panel panel-danger\">\n" +
    "    <div class=\"panel-heading\">Remove RPC service</div>\n" +
    "\n" +
    "    <div class=\"panel-body\">\n" +
    "        <p>Are you sure you want to delete the service?</p>\n" +
    "\n" +
    "        <div class=\"form-group\">\n" +
    "            <label>\n" +
    "                <input type=\"checkbox\" ng-model=\"recursive\">\n" +
    "                Delete all files and directories for this service?\n" +
    "            </label>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"btn-group pull-right\">\n" +
    "            <button type=\"button\" class=\"btn btn-sm btn-default\"\n" +
    "                collapse-flag flags=\"{delete: false}\">\n" +
    "                No\n" +
    "            </button>\n" +
    "            <button type=\"button\" class=\"btn btn-sm btn-danger\"\n" +
    "                ng-click=\"removeRpcService(rpcService.controller_service_name, recursive)\">\n" +
    "                Yes\n" +
    "            </button>\n" +
    "        </div>\n" +
    "    </div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/api/rpc-services/settings-edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rpc-services/settings-edit.html",
    "<form novalidate class=\"ag form\" ng-submit=\"saveRpcService($index)\" ag-form>\n" +
    "    <ag-collapse class=\"panel-success\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">General Settings</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group in\">\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">Route to match</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <input type=\"text\" placeholder=\"/api/for/your/service\"\n" +
    "                        class=\"form-control input-xlarge\" required=\"\" ng-model=\"rpcService.route_match\">\n" +
    "                    <p class=\"help-block\">\n" +
    "                        The URI for the service; \"[&nbsp;]\" indicate optional\n" +
    "                        segments, and \":varname\" indicates a variable URI segment\n" +
    "                        to capture.\n" +
    "                    </p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">Allowed HTTP Methods</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <label ng-repeat=\"method in ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']\" class=\"checkbox inline\">\n" +
    "                        <input type=\"checkbox\" class=\"input-xlarge\" value=\"{{method}}\" ng-checked=\"rpcService.http_methods.indexOf(method) > -1\" ng-click=\"toggleSelection(rpcService.http_methods, $event)\">\n" +
    "                        <span>{{method}}</span>\n" +
    "                    </label>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <ag-collapse class=\"panel-success\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">Content Negotiation</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group\">\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\">Content Negotiation Selector</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <select class=\"form-control input-xlarge\"\n" +
    "                        ng-model=\"rpcService.selector\" ng-options=\"v as v for (k,v) in selectors\"></select>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\" for=\"accept_whitelist\">Accept whitelist</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <tags-input\n" +
    "                        custom-class=\"ag-tags\"\n" +
    "                        ng-model=\"rpcService.accept_whitelist\"\n" +
    "                        add-on-space=\"true\"\n" +
    "                        max-length=\"256\"\n" +
    "                        allowed-tags-pattern=\"^[a-zA-Z-]+/[a-zA-Z0-9*_+.-]+$\"\n" +
    "                        placeholder=\"Add a mediatype to the whitelist\">\n" +
    "                    </tags-input>\n" +
    "                    <p class=\"help-block\">Specify mediatypes for representations this API can provide (tied to Accept request header)</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"form-group list-group-item\">\n" +
    "                <label class=\"control-label\" for=\"content_type_whitelist\">Content-Type whitelist</label>\n" +
    "                <div class=\"controls\">\n" +
    "                    <tags-input\n" +
    "                        custom-class=\"ag-tags\"\n" +
    "                        ng-model=\"rpcService.content_type_whitelist\"\n" +
    "                        add-on-space=\"true\"\n" +
    "                        max-length=\"256\"\n" +
    "                        allowed-tags-pattern=\"^[a-zA-Z-]+/[a-zA-Z0-9*_+.-]+$\"\n" +
    "                        placeholder=\"Add a mediatype to the whitelist\">\n" +
    "                    </tags-input>\n" +
    "                    <p class=\"help-block\">Specify mediatypes allowed for submitted content (tied to the Content-Type request header)</p>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <div class=\"ag-new-input btn-group pull-right\">\n" +
    "        <!-- todo Implement logic to reset form/model -->\n" +
    "        <button type=\"button\" class=\"btn btn-sm btn-default\"\n" +
    "            ag-cancel-edit>Cancel</button>\n" +
    "        <button type=\"submit\" class=\"btn btn-sm btn-success\">Save</button>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"clearfix\"></div>\n" +
    "</form>\n" +
    "");
}]);

angular.module("html/api/rpc-services/settings-view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rpc-services/settings-view.html",
    "<div class=\"panel-group tooltip-api\">\n" +
    "    <ag-collapse class=\"panel-default\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">General Settings</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group in\">\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Route matches:</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ rpcService.route_match }}</p>\n" +
    "            </div>\n" +
    "        \n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Allowed HTTP Methods</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ rpcService.http_methods.join(', ') }}</p>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <ag-collapse class=\"panel-default\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">Content Negotiation</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group\">\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Content Negotiation Selector</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ rpcService.selector }}</p>\n" +
    "            </div>\n" +
    "            \n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Accept whitelist</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ rpcService.accept_whitelist.join(', ') || 'n/a' }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Content-Type whitelist</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ rpcService.content_type_whitelist.join(', ') || 'n/a' }}</p>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <ag-collapse class=\"panel-default\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">Service/Route Names</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body class=\"list-group\">\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Controller Service Name</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ rpcService.controller_service_name | controllerservicename }}</p>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"list-group-item\">\n" +
    "                <h4 class=\"list-group-item-heading\">Route Name</h4>\n" +
    "                <p class=\"list-group-item-text\">{{ rpcService.route_name }}</p>\n" +
    "            </div>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/api/rpc-services/source-code.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rpc-services/source-code.html",
    "<ag-collapse class=\"panel-blank\">\n" +
    "    <collapse-body>\n" +
    "        <p>Click on the file name to show the source code.</p>\n" +
    "    </collapse-body>\n" +
    "\n" +
    "    <table class=\"table table-striped\">    \n" +
    "        <tr>\n" +
    "            <td>Controller Service Class</td>\n" +
    "            <td><button\n" +
    "                data-doclink=\"{{service.controller_class}}\"\n" +
    "                class=\"btn-link\"\n" +
    "                ng-click=\"getSourceCode(service.controller_class, 'Controller Service')\">{{ service.controller_class }}.php</button></td>\n" +
    "        </tr>\n" +
    "    </table>\n" +
    "</ag-collapse>\n" +
    "");
}]);

angular.module("html/api/rpc-services/view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/rpc-services/view.html",
    "<ag-tabs>\n" +
    "    <ag-tab-pane name=\"settings\" active=\"{{ view == 'settings' }}\" searchparam=\"view\" title=\"Settings\">\n" +
    "        <ng-include src=\"'html/api/rpc-services/settings-view.html'\"></ng-include>\n" +
    "    </ag-tab-pane>\n" +
    "\n" +
    "    <ag-tab-pane name=\"fields\" active=\"{{ view == 'fields' }}\" searchparam=\"view\" title=\"Fields\">\n" +
    "        <ng-include src=\"'html/api/input-filter-view.html'\" onload=\"index = $index\"></ng-include>\n" +
    "    </ag-tab-pane>\n" +
    "\n" +
    "    <ag-tab-pane name=\"documentation\" active=\"{{ view == 'documentation' }}\" searchparam=\"view\" title=\"Documentation\">\n" +
    "        <ng-include src=\"'html/api/rpc-services/documentation-view.html'\"></ng-include>\n" +
    "    </ag-tab-pane>\n" +
    "\n" +
    "    <ag-tab-pane name=\"source\" active=\"{{ view == 'source' }}\" searchparam=\"view\" title=\"Source Code\" title=\"Source Code\">\n" +
    "        <ng-include \n" +
    "            src=\"'html/api/rpc-services/source-code.html'\"\n" +
    "            onload=\"index = $index; service = rpcService\"></ng-include>\n" +
    "    </ag-tab-pane>\n" +
    "</ag-tabs>\n" +
    "");
}]);

angular.module("html/api/sidebar.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/api/sidebar.html",
    "<ul class=\"nav nav-pills ag-admin-nav-pills nav-stacked ag-sidenav\">\n" +
    "  <li class=\"form-inline\" ng-controller=\"ApiVersionController\">\n" +
    "    Current Version:\n" +
    "    <select\n" +
    "      class=\"form-control input-sm\"\n" +
    "      ng-model=\"currentVersion\"\n" +
    "      ng-options=\"ver for ver in api.versions\"\n" +
    "      ng-change=\"changeVersion()\"></select>\n" +
    "  </li>\n" +
    "  <li ng-class=\"{active: ('ag.api.version' | isState)}\"><a ui-sref=\"ag.api.version\">Overview</a></li>\n" +
    "  <li ng-class=\"{active: ('ag.api.version.rest' | isState)}\"><a ui-sref=\"ag.api.version.rest\">REST Services</a></li>\n" +
    "  <li ng-class=\"{active: ('ag.api.version.rpc' | isState)}\"><a ui-sref=\"ag.api.version.rpc\">RPC Services</a></li>\n" +
    "  <li ng-class=\"{active: ('ag.api.version.authorization' | isState)}\"><a ui-sref=\"ag.api.version.authorization\">Authorization</a></li>\n" +
    "</ul>\n" +
    "\n" +
    "");
}]);

angular.module("html/breadcrumbs.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/breadcrumbs.html",
    "<ol class=\"breadcrumb\">\n" +
    "  <li ng-repeat=\"breadcrumb in breadcrumbs\" ng-class=\"{active: breadcrumb.active}\">\n" +
    "    <a ng-if=\"!breadcrumb.active\" ui-sref=\"{{ breadcrumb.href }}(params)\">{{ breadcrumb.title }}</a>\n" +
    "    <span ng-if=\"breadcrumb.active\">{{ breadcrumb.title }}</span>\n" +
    "  </li>\n" +
    "</ol>\n" +
    "");
}]);

angular.module("html/content.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/content.html",
    "\n" +
    "");
}]);

angular.module("html/dashboard-sidebar.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/dashboard-sidebar.html",
    "<img src=\"img/ag-hero.png\" alt=\"Apigility\" class=\"scale\">\n" +
    "\n" +
    "");
}]);

angular.module("html/directives/ag-edit-inplace.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/directives/ag-edit-inplace.html",
    "<span>\n" +
    "    <span class=\"form-control-static input-xlarge\" ng-click=\"isFormVisible = true\">\n" +
    "        {{ agInputName }}\n" +
    "    </span>\n" +
    "\n" +
    "    <form class=\"form-inline hide\">\n" +
    "        <div class=\"form-group\">\n" +
    "            <input \n" +
    "                type=\"text\" class=\"form-control input-xlarge\"\n" +
    "                ag-on-enter=\"submit()\"\n" +
    "                ng-model=\"agInputName\">\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"btn-group\">\n" +
    "            <button \n" +
    "                type=\"button\" class=\"btn btn-sm btn-default\"\n" +
    "                ng-click=\"resetForm()\">\n" +
    "                Cancel\n" +
    "            </button>\n" +
    "            <button type=\"button\" class=\"btn btn-sm btn-success\"\n" +
    "                ng-click=\"submit()\">\n" +
    "                Change field name\n" +
    "            </button>\n" +
    "        </div>\n" +
    "    </form>\n" +
    "</span>\n" +
    "");
}]);

angular.module("html/empty-content.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/empty-content.html",
    "<!-- empty content -->\n" +
    "");
}]);

angular.module("html/empty.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/empty.html",
    "<div class=\"empty\"></div>\n" +
    "");
}]);

angular.module("html/index.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/index.html",
    "<div class=\"row\">\n" +
    "    <div class=\"col-md-12\">\n" +
    "        <div class=\"panel panel-info\" ng-show=\"!dashboard.modules.length\">\n" +
    "            <div class=\"panel-heading\">\n" +
    "                <h4 class=\"panel-title\">\n" +
    "                    <i class=\"glyphicon glyphicon-question-sign\"></i> Getting started\n" +
    "                </h4>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"panel-body\" ng-show=\"!dashboard.modules.length\">\n" +
    "                <p class=\"text-warning\">\n" +
    "                    Don't know where to start?\n" +
    "                    <a href=\"https://apigility.org/documentation\">Read the documentation</a>\n" +
    "                    or <a href=\"https://apigility.org/video\">watch the screencast</a>\n" +
    "                </p>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"panel panel-info\">\n" +
    "            <div class=\"panel-heading\">\n" +
    "                <h4 class=\"panel-title\">\n" +
    "                    <a ui-sref=\"ag.api\">APIs</a>\n" +
    "                </h4>\n" +
    "            </div>\n" +
    "\n" +
    "           \n" +
    "            <div class=\"panel-body\" ng-show=\"!dashboard.modules.length\">\n" +
    "                <p class=\"text-warning\">\n" +
    "                    No APIs;\n" +
    "                    <a ui-sref=\"ag.api\">would you like to create one now?</a>\n" +
    "                </p>\n" +
    "            </div>\n" +
    "\n" +
    "            <ul class=\"list-group\">\n" +
    "                <li ng-repeat=\"api in dashboard.modules\" class=\"list-group-item\">\n" +
    "                    <h4><a ui-sref=\"ag.api.version({apiName: api.name, version: api.latestVersion})\">\n" +
    "                        {{ api.name }} (v{{ api.latestVersion }})\n" +
    "                    </a></h4>\n" +
    "\n" +
    "                    <div class=\"row\">\n" +
    "                        <div class=\"col-sm-2 col-sm-offset-1\"><b>REST Services:</b></div>\n" +
    "\n" +
    "                        <div class=\"col-sm-9 list-group\">\n" +
    "                            <p class=\"list-group-item\" ng-repeat=\"service in api.rest\">\n" +
    "                                <a ui-sref=\"ag.api.version.rest({service: service, version: api.latestVersion, apiName: api.name})\">\n" +
    "                                    {{ service }}\n" +
    "                                </a>\n" +
    "                            </p>\n" +
    "\n" +
    "                            <p ng-hide=\"api.rest.length\" class=\"text-warning list-group-item\">\n" +
    "                                No REST services configured; \n" +
    "                                <a ui-sref=\"ag.api.version.rest({version: api.latestVersion, apiName: api.name})\">\n" +
    "                                    would you like to create one?\n" +
    "                                </a>\n" +
    "                            </p>\n" +
    "                        </div>\n" +
    "                    </div>\n" +
    "\n" +
    "                    <div class=\"row\">\n" +
    "                        <div class=\"col-sm-2 col-sm-offset-1\"><b>RPC Services:</b></div>\n" +
    "\n" +
    "                        <div class=\"col-sm-9 list-group\">\n" +
    "                            <p class=\"list-group-item\" ng-repeat=\"service in api.rpc\">\n" +
    "                                <a ui-sref=\"ag.api.version.rpc({service: service, version: api.latestVersion, apiName: api.name})\">\n" +
    "                                    {{ service }}\n" +
    "                                </a>\n" +
    "                            </p>\n" +
    "\n" +
    "                            <p ng-hide=\"api.rpc.length\" class=\"text-warning list-group-item\">\n" +
    "                                No RPC services configured; \n" +
    "                                <a ui-sref=\"ag.api.version.rpc({version: api.latestVersion, apiName: api.name})\">\n" +
    "                                    would you like to create one?\n" +
    "                                </a>\n" +
    "                            </p>\n" +
    "                        </div>\n" +
    "                    </div>\n" +
    "                </li>\n" +
    "            </ul>\n" +
    "        </div>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"row\">\n" +
    "    <div class=\"col-md-6\">\n" +
    "        <div class=\"panel panel-info\">\n" +
    "            <div class=\"panel-heading\">\n" +
    "                <h4 class=\"panel-title\">\n" +
    "                    <i class=\"glyphicon glyphicon-lock\"></i>\n" +
    "                    <a ui-sref=\"ag.settings.authentication\">Authentication</a>\n" +
    "                </h4>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"panel-body\" ng-show=\"!dashboard.authentication\">\n" +
    "                <p class=\"text-warning\">\n" +
    "                    No authentication configured; <a ui-sref=\"ag.settings.authentication\">would you like to set it up now?</a>\n" +
    "                </p>\n" +
    "            </div>\n" +
    "\n" +
    "            <table class=\"table\">\n" +
    "                <ag-conditional-include\n" +
    "                    condition=\"isHttpBasicAuthentication(dashboard.authentication)\"\n" +
    "                    src=\"html/settings/authentication/http-basic-view.html\"></ag-conditional-include>\n" +
    "                <ag-conditional-include\n" +
    "                    condition=\"isHttpDigestAuthentication(dashboard.authentication)\"\n" +
    "                    src=\"html/settings/authentication/http-digest-view.html\"></ag-conditional-include>\n" +
    "                <ag-conditional-include\n" +
    "                    condition=\"isOAuth2(dashboard.authentication)\"\n" +
    "                    src=\"html/settings/authentication/oauth2-view.html\"></ag-conditional-include>\n" +
    "            </table>\n" +
    "        </div>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"col-md-6\">\n" +
    "        <div class=\"panel panel-info\">\n" +
    "            <div class=\"panel-heading\">\n" +
    "                <h4 class=\"panel-title\">\n" +
    "                    <i class=\"glyphicon glyphicon-book\"></i>\n" +
    "                    <a ui-sref=\"ag.settings.db-adapters\">Database Adapters</a>\n" +
    "                </h4>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"panel-body\" ng-show=\"!dashboard.dbAdapters.length\">\n" +
    "                <p class=\"text-warning\">\n" +
    "                    No database adapters configured;\n" +
    "                    <a ui-sref=\"ag.settings.db-adapters\">would you like to set one up now?</a>\n" +
    "                </p>\n" +
    "            </div>\n" +
    "\n" +
    "            <ul class=\"list-group\">\n" +
    "                <li ng-repeat=\"adapter in dashboard.dbAdapters\" class=\"list-group-item\">\n" +
    "                    <a ui-sref=\"ag.settings.db-adapters({adapter: adapter.adapter_name})\">{{ adapter.adapter_name }}</a>\n" +
    "                </li>\n" +
    "            </ul>\n" +
    "        </div>\n" +
    "    </div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/modals/cache-check.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/modals/cache-check.html",
    "<div class=\"modal-header\"><h4 class=\"modal-title text-danger\">WARNING!</h4></div>\n" +
    "\n" +
    "<div class=\"modal-body\">\n" +
    "    <h4 class=\"text-danger\">OpCode Cache Detected!</h4>\n" +
    "\n" +
    "    <p>\n" +
    "        We have detected that your Apigility Admin API is running with a\n" +
    "        PHP OpCode Cache enabled. You will need to disable it in order\n" +
    "        to ensure that the API will be able to perform its operations.\n" +
    "    </p>\n" +
    "\n" +
    "    <p>\n" +
    "        To do this, you will need to either edit your <kbd>php.ini</kbd>\n" +
    "        file or your web server configuration (in which case you will\n" +
    "        set a PHP admin configuration directive). The setting you will\n" +
    "        need to change will depend on the OpCode Cache you have enabled,\n" +
    "        per the table below.\n" +
    "    </p>\n" +
    "\n" +
    "    <table class=\"table table-striped\">\n" +
    "        <thead>\n" +
    "            <tr>\n" +
    "                <th>OpCode Cache</th>\n" +
    "                <th>Setting</th>\n" +
    "                <th>Value</th>\n" +
    "            </tr>\n" +
    "        </thead>\n" +
    "\n" +
    "        <tbody>\n" +
    "            <tr>\n" +
    "                <td>APC</td>\n" +
    "                <td><kbd>apc.enabled</kbd></td>\n" +
    "                <td><kbd>\"0\"</kbd></td>\n" +
    "            </tr>\n" +
    "\n" +
    "            <tr>\n" +
    "                <td>EAccelerator</td>\n" +
    "                <td><kbd>eaccelerator.enable</kbd></td>\n" +
    "                <td><kbd>\"0\"</kbd></td>\n" +
    "            </tr>\n" +
    "\n" +
    "            <tr>\n" +
    "                <td>OpCache</td>\n" +
    "                <td><kbd>opcache.enable</kbd></td>\n" +
    "                <td><kbd>\"0\"</kbd></td>\n" +
    "            </tr>\n" +
    "\n" +
    "            <tr>\n" +
    "                <td>WinCache</td>\n" +
    "                <td><kbd>wincache.ocenabled</kbd></td>\n" +
    "                <td><kbd>\"0\"</kbd></td>\n" +
    "            </tr>\n" +
    "\n" +
    "            <tr>\n" +
    "                <td>XCache</td>\n" +
    "                <td><kbd>xcache.cacher</kbd></td>\n" +
    "                <td><kbd>\"0\"</kbd></td>\n" +
    "            </tr>\n" +
    "\n" +
    "            <tr>\n" +
    "                <td>Zend Data Cache</td>\n" +
    "                <td><kbd>zend_datacache.apc_compatibility</kbd></td>\n" +
    "                <td><kbd>\"0\"</kbd> or <kbd>\"Off\"</kbd></td>\n" +
    "            </tr>\n" +
    "\n" +
    "            <tr>\n" +
    "                <td>Zend Optimizer+</td>\n" +
    "                <td><kbd>zend_optimizerplus.enable</kbd></td>\n" +
    "                <td><kbd>\"0\"</kbd></td>\n" +
    "            </tr>\n" +
    "        </tbody>\n" +
    "    </table>\n" +
    "\n" +
    "    <p>\n" +
    "        Please make the setting change indicated, restart your web\n" +
    "        server, and then reload this page.\n" +
    "    </p>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/modals/create-api-form.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/modals/create-api-form.html",
    "<div class=\"modal-header\"><h4 class=\"modal-title\">Create New API</h4></div>\n" +
    "\n" +
    "<div class=\"modal-body\">\n" +
    "    <form class=\"form\" ng-submit=\"createNewApi($event)\" ag-form>\n" +
    "        <fieldset>\n" +
    "            <div class=\"form-group\">\n" +
    "                <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"apiName\" placeholder=\"API Name ...\" required=\"\">\n" +
    "            </div>\n" +
    "        </fieldset>\n" +
    "\n" +
    "        <div class=\"btn-group pull-right\">\n" +
    "            <button type=\"button\" class=\"btn btn-sm btn-default\" ng-click=\"cancel()\">Cancel</button>\n" +
    "            <button type=\"submit\" class=\"btn btn-sm btn-primary\">Create API</button>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"clearfix\"></div>\n" +
    "    </form>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/modals/fs-perms.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/modals/fs-perms.html",
    "<div class=\"modal-header\"><h4 class=\"modal-title text-danger\">WARNING!</h4></div>\n" +
    "\n" +
    "<div class=\"modal-body\">\n" +
    "    <h4 class=\"text-danger\">Filesystem is not writable!</h4>\n" +
    "\n" +
    "    <p>\n" +
    "        We have detected that your Apigility Admin API is running\n" +
    "        without the ability to write to one of the following\n" +
    "        directories:\n" +
    "    </p>\n" +
    "\n" +
    "    <ul>\n" +
    "        <li><code>config/</code></li>\n" +
    "        <li><code>config/autoload/</code></li>\n" +
    "        <li><code>module/</code></li>\n" +
    "    </ul>\n" +
    "\n" +
    "    <p>\n" +
    "        As such, the Apigility Admin API will not be able to do its\n" +
    "        work, which involves writing and updating configuration files,\n" +
    "        as well as generating PHP class stubs.\n" +
    "    </p>\n" +
    "\n" +
    "    <p>\n" +
    "        To correct the issue, make these directories, and all\n" +
    "        descendents, writable by the user under which the web server is\n" +
    "        running (we've determined <strong>\"{{ user }}\"</strong> from the\n" +
    "        server).\n" +
    "    </p>\n" +
    "\n" +
    "    <p>\n" +
    "        Once you have updated the permissions, reload this page.\n" +
    "    </p>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/modals/help-content-negotiation.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/modals/help-content-negotiation.html",
    "<div class=\"modal-header\">\n" +
    "    <button type=\"button\" class=\"close\" ng-click=\"$close()\">&times;</button>\n" +
    "    <h4 class=\"modal-title\">Help: Content Negotiation</h4>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"modal-body\">\n" +
    "    <p>\n" +
    "        Content Negotiation selectors are used by the application to\n" +
    "        determine the representation to use in the response based on\n" +
    "        the <kbd>Accept</kbd> header sent by the client.\n" +
    "    </p>\n" +
    "\n" +
    "    <p>\n" +
    "        Selectors are named, and within your REST and RPC services,\n" +
    "        you will select from these names to indicate which\n" +
    "        representations you will support.\n" +
    "    </p>\n" +
    "\n" +
    "    <p>\n" +
    "        Each selector consists of maps of Zend Framework 2 <a href=\"http://framework.zend.com/manual/2.2/en/modules/zend.view.quick-start.html#controllers-and-view-models\">View\n" +
    "        Models</a> and the <kbd>Accept</kbd> mimetypes that, when\n" +
    "        matched, will cause the given View Model to be used.\n" +
    "    </p>\n" +
    "\n" +
    "    <p>\n" +
    "        <b>Note:</b> For this Content Negotiation to work in your\n" +
    "        RPC services, you will need to always return a\n" +
    "        <kbd>ZF\\ContentNegotiation\\ViewModel</kbd>.\n" +
    "    </p>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"modal-footer\">\n" +
    "    <p class=\"pull-left\">\n" +
    "        <a href=\"https://apigility.org/documentation/api-primer/content-negotiation\" target=\"_blank\">\n" +
    "            <i class=\"glyphicon glyphicon-info-sign\"></i>\n" +
    "            More information\n" +
    "        </a>\n" +
    "    </p>\n" +
    "    <button type=\"button\" class=\"btn btn-default\" ng-click=\"$close()\">Close</button>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"clearfix\"></div>\n" +
    "");
}]);

angular.module("html/modals/help-input-filter.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/modals/help-input-filter.html",
    "<div class=\"modal-header\">\n" +
    "    <button type=\"button\" ng-click=\"$close()\" class=\"close\">&times;</button>\n" +
    "    <h4 class=\"modal-title\">Help: Fields</h4>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"modal-body\">\n" +
    "    <p>\n" +
    "        Fields are used to both describe expected data, as well\n" +
    "        as specify how that data should be validated.\n" +
    "    </p>\n" +
    "\n" +
    "    <p>\n" +
    "        Clicking the \"Create New Field\" button will prompt you\n" +
    "        for a field name, and then create it. If you want to\n" +
    "        change the name later, click the name to edit it.\n" +
    "        Click the <i class=\"glyphicon\n" +
    "            glyphicon-trash\"></i> icon to remove the field.\n" +
    "    </p>\n" +
    "\n" +
    "    <p>\n" +
    "        Fields are comprised of:\n" +
    "    </p>\n" +
    "\n" +
    "    <ul>\n" +
    "        <li>A \"required\" flag - is the field required in order to\n" +
    "        validate?</li>\n" +
    "        <li>An \"allow empty\" flag - can the field be a null or\n" +
    "        empty string and still be considered valid?</li>\n" +
    "        <li>A \"continue if empty\" flag - if the field is empty and\n" +
    "        required, should the validations still be run?</li>\n" +
    "        <li>One or more <em>validators</em>.</li>\n" +
    "        <li>One or more <em>filters</em>, used to normalize the\n" +
    "        field prior to validation.</li>\n" +
    "    </ul>\n" +
    "\n" +
    "    <p>\n" +
    "        Validators and filters may be added, manipulated, and\n" +
    "        removed in the same ways. In each case, you must first Click\n" +
    "        the <i class=\"glyphicon glyphicon-wrench\"></i> icon to\n" +
    "        expand the input and reveal the \"Add Validator\" and \"Add\n" +
    "        Filter\" buttons.  Adding either will reveal a form that\n" +
    "        allows you to select from available validators or filters.\n" +
    "    </p>\n" +
    "\n" +
    "    <p>\n" +
    "        Validators and filters will be executed in the order in which they\n" +
    "        appear in the admin tool; you may grab the <i\n" +
    "        class=\"glyphicon glyphicon-resize-vertical\"></i> icon in order\n" +
    "        to drag and re-order them. Clicking the <i\n" +
    "            class=\"glyphicon glyphicon-trash\"></i> icon will\n" +
    "        remove it.\n" +
    "    </p>\n" +
    "\n" +
    "    <p>\n" +
    "        Most validators and filters also support a number of options. After\n" +
    "        adding one, you may add options; these may be\n" +
    "        selected from a dialog, and an appropriate form input \n" +
    "        will then be provided to allow you to specify a value.\n" +
    "        You cannot edit options after creating them; however,\n" +
    "        you can remove them via the <i class=\"glyphicon\n" +
    "            glyphicon-trash\"></i> icon and re-add them with\n" +
    "        the changes you desire.\n" +
    "    </p>\n" +
    "\n" +
    "    <p>\n" +
    "        For more information on available validators and their\n" +
    "        options, please see <a\n" +
    "        href=\"http://framework.zend.com/manual/2.2/en/modules/zend.validator.set.html\">the\n" +
    "ZF2 reference guide on validators</a>. \n" +
    "    </p>\n" +
    "\n" +
    "    <p>\n" +
    "        For more information on available filters and their\n" +
    "        options, please see <a\n" +
    "        href=\"http://framework.zend.com/manual/2.2/en/modules/zend.filter.set.html\">the\n" +
    "ZF2 reference guide on filters</a>. \n" +
    "    </p>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"modal-footer\">\n" +
    "    <p class=\"pull-left\">\n" +
    "        <a href=\"https://apigility.org/documentation/content-validation/basic-usage\" target=\"_blank\">\n" +
    "            <i class=\"glyphicon glyphicon-info-sign\"></i>\n" +
    "            More information\n" +
    "        </a>\n" +
    "    </p>\n" +
    "\n" +
    "    <button type=\"button\" ng-click=\"$close()\" class=\"btn btn-default\">Close</button>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"clearfix\"></div>\n" +
    "\n" +
    "");
}]);

angular.module("html/modals/source-code.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/modals/source-code.html",
    "<div class=\"modal-content\">\n" +
    "    <div class=\"modal-header\">\n" +
    "        <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button>\n" +
    "        <h4 class=\"modal-title\">{{ classType }}<br /><font size=\"-1\">{{ filename }}</font></h4>\n" +
    "    </div>\n" +
    "    <div class=\"modal-body ag-code\" ng-bind-html=\"sourceCode\"></div>\n" +
    "    <div class=\"modal-footer\">\n" +
    "        <button type=\"button\" class=\"btn btn-default\" ng-click=\"$dismiss()\">Close</button>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/settings/authentication/http-basic-edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/http-basic-edit.html",
    "<div class=\"panel-body\"><form class=\"form\" ng-submit=\"updateHttpBasicAuthentication()\" ag-form>\n" +
    "    <fieldset>\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"realm\">Authentication Realm</label>\n" +
    "            <input type=\"text\" placeholder=\"api\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"httpBasic.realm\">\n" +
    "            <p class=\"help-block\">The Authentication Realm for the HTTP Basic Authentication</p>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"htpasswd\">Location of htpasswd file</label>\n" +
    "            <input type=\"text\" placeholder=\"data/htpasswd\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"httpBasic.htpasswd\">\n" +
    "            <p class=\"help-block\">Location on the filesystem of the htpasswd file</p>\n" +
    "        </div>\n" +
    "    </fieldset>\n" +
    "\n" +
    "    <div class=\"btn-group pull-right\">\n" +
    "        <button\n" +
    "            type=\"button\" title=\"Cancel\" class=\"btn btn-sm btn-default\" ag-cancel-edit>Cancel</button>\n" +
    "        <button type=\"submit\" title=\"Save\" class=\"btn btn-sm btn-success\">Save</button>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"clearfix\"></div>\n" +
    "</form></div>\n" +
    "");
}]);

angular.module("html/settings/authentication/http-basic-view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/http-basic-view.html",
    "<table class=\"table table-striped\">\n" +
    "    <tr>\n" +
    "        <td>Realm</td>\n" +
    "        <td>{{httpBasic.realm}}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr>\n" +
    "        <td>htpasswd Location</td>\n" +
    "        <td>{{httpBasic.htpasswd}}</td>\n" +
    "    </tr>\n" +
    "</table>\n" +
    "");
}]);

angular.module("html/settings/authentication/http-basic.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/http-basic.html",
    "<ag-collapse class=\"panel-info\"\n" +
    "    no-chevron\n" +
    "    conditionals=\"{{ {edit: inEdit, delete: false} }}\">\n" +
    "    <collapse-header>\n" +
    "        <h4 class=\"panel-title\">\n" +
    "            <i class=\"glyphicon glyphicon-lock\"></i>\n" +
    "\n" +
    "            HTTP Basic Settings\n" +
    "\n" +
    "            <div class=\"btn-group pull-right\">\n" +
    "                <button\n" +
    "                    type=\"button\" title=\"Cancel\" class=\"btn btn-sm btn-default\"\n" +
    "                    collapse-flag flags=\"{edit: false}\" collapse-click=\"cancelEdit\"\n" +
    "                    collapse-button criteria=\"{delete: false, edit: true}\">\n" +
    "                    Cancel\n" +
    "                </button>\n" +
    "\n" +
    "                <button \n" +
    "                    type=\"button\" class=\"btn btn-sm btn-success\" title=\"Edit settings\"\n" +
    "                    collapse-flag flags=\"{edit: true}\" collapse-click=\"startEdit\"\n" +
    "                    collapse-button criteria=\"{delete: false, edit: false}\">\n" +
    "                    <i class=\"glyphicon glyphicon-edit\"></i>\n" +
    "                </button>\n" +
    "\n" +
    "                <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Remove Authentication\"\n" +
    "                    collapse-flag flags=\"{delete: true}\"\n" +
    "                    collapse-button criteria=\"{delete: false}\">\n" +
    "                    <i class=\"glyphicon glyphicon-trash\"></i>\n" +
    "                </button>\n" +
    "            </div>\n" +
    "        </h4>\n" +
    "\n" +
    "        <div class=\"clearfix\"></div>\n" +
    "\n" +
    "        <span collapse-show\n" +
    "            criteria=\"{delete: false}\"\n" +
    "            default-template=\"'html/empty-content.html'\"\n" +
    "            toggled-template=\"'html/settings/authentication/remove.html'\"></span>\n" +
    "    </collapse-header>\n" +
    "\n" +
    "    <collapse-body>\n" +
    "        <span\n" +
    "            collapse-show\n" +
    "            criteria=\"{edit: false}\"\n" +
    "            default-template=\"'html/settings/authentication/http-basic-view.html'\"\n" +
    "            toggled-template=\"'html/settings/authentication/http-basic-edit.html'\"></span>\n" +
    "    </collapse-body>\n" +
    "</ag-collapse>\n" +
    "");
}]);

angular.module("html/settings/authentication/http-digest-edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/http-digest-edit.html",
    "<div class=\"panel-body\"><form class=\"form\" ng-submit=\"updateHttpDigestAuthentication()\" ag-form>\n" +
    "    <fieldset>\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"realm\">Realm</label>\n" +
    "            <input type=\"text\" placeholder=\"api\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"httpDigest.realm\">\n" +
    "            <p class=\"help-block\">HTTP authentication realm</p>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"htdigest\">Location of htdigest file</label>\n" +
    "            <input type=\"text\" placeholder=\"data/htdigest\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"httpDigest.htdigest\">\n" +
    "            <p class=\"help-block\">Location on the filesystem of the htdigest file</p>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"digest_domains\">Digest domains</label>\n" +
    "            <tags-input \n" +
    "                custom-class=\"ag-tags\"\n" +
    "                ng-model=\"httpDigest.digest_domains\"\n" +
    "                add-on-space=\"true\"\n" +
    "                min-length=\"1\"\n" +
    "                max-length=\"256\"\n" +
    "                allowed-tags-pattern=\"^/[a-zA-Z0-9_+./%-]*$\"\n" +
    "                placeholder=\"Add a path\">\n" +
    "            </tags-input>\n" +
    "            <p class=\"help-block\">Space-separated list of URI paths for which authentication will be applied</p>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"nonce_timeout\">Nonce timeout</label>\n" +
    "            <input type=\"text\" placeholder=\"3600\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"httpDigest.nonce_timeout\">\n" +
    "            <p class=\"help-block\">Expiration in seconds for inactive authentication</p>\n" +
    "        </div>\n" +
    "    </fieldset>\n" +
    "\n" +
    "    <div class=\"btn-group pull-right\">\n" +
    "        <button\n" +
    "            type=\"button\" title=\"Cancel\" class=\"btn btn-sm btn-default\" ag-cancel-edit>Cancel</button>\n" +
    "        <button type=\"submit\" title=\"Save\" class=\"btn btn-sm btn-success\">Save</button>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"clearfix\"></div>\n" +
    "</form></div>\n" +
    "");
}]);

angular.module("html/settings/authentication/http-digest-view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/http-digest-view.html",
    "<table class=\"table table-striped\">\n" +
    "    <tr>\n" +
    "        <td>Realm</td>\n" +
    "        <td>{{httpDigest.realm}}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr>\n" +
    "        <td>htdigest Location</td>\n" +
    "        <td>{{httpDigest.htdigest}}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr>\n" +
    "        <td>Digest Domains</td>\n" +
    "        <td>\n" +
    "            <p ng-repeat=\"domain in httpDigest.digest_domains\">{{domain}}</p>\n" +
    "        </td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr>\n" +
    "        <td>Nonce Timeout</td>\n" +
    "        <td>{{httpDigest.nonce_timeout}}</td>\n" +
    "    </tr>\n" +
    "</table>\n" +
    "");
}]);

angular.module("html/settings/authentication/http-digest.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/http-digest.html",
    "<ag-collapse class=\"panel-info\"\n" +
    "    no-chevron\n" +
    "    conditionals=\"{edit: inEdit, delete: false}\">\n" +
    "    <collapse-header>\n" +
    "        <h4 class=\"panel-title\">\n" +
    "            <i class=\"glyphicon glyphicon-lock\"></i>\n" +
    "\n" +
    "            HTTP Digest Settings\n" +
    "\n" +
    "            <div class=\"btn-group pull-right\">\n" +
    "                <button\n" +
    "                    type=\"button\" title=\"Cancel\" class=\"btn btn-sm btn-default\"\n" +
    "                    collapse-flag flags=\"{edit: false}\" collapse-click=\"cancelEdit\"\n" +
    "                    collapse-button criteria=\"{delete: false, edit: true}\">\n" +
    "                    Cancel\n" +
    "                </button>\n" +
    "\n" +
    "                <button \n" +
    "                    type=\"button\" class=\"btn btn-sm btn-success\" title=\"Edit settings\"\n" +
    "                    collapse-flag flags=\"{edit: true}\" collapse-click=\"startEdit\"\n" +
    "                    collapse-button criteria=\"{delete: false, edit: false}\">\n" +
    "                    <i class=\"glyphicon glyphicon-edit\"></i>\n" +
    "                </button>\n" +
    "\n" +
    "                <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Remove Authentication\"\n" +
    "                    collapse-flag flags=\"{delete: true}\"\n" +
    "                    collapse-button criteria=\"{delete: false}\">\n" +
    "                    <i class=\"glyphicon glyphicon-trash\"></i>\n" +
    "                </button>\n" +
    "            </div>\n" +
    "        </h4>\n" +
    "\n" +
    "        <div class=\"clearfix\"></div>\n" +
    "\n" +
    "        <span collapse-show\n" +
    "            criteria=\"{delete: false}\"\n" +
    "            default-template=\"'html/empty-content.html'\"\n" +
    "            toggled-template=\"'html/settings/authentication/remove.html'\"></span>\n" +
    "    </collapse-header>\n" +
    "\n" +
    "    <collapse-body>\n" +
    "        <span\n" +
    "            collapse-show\n" +
    "            criteria=\"{edit: false}\"\n" +
    "            default-template=\"'html/settings/authentication/http-digest-view.html'\"\n" +
    "            toggled-template=\"'html/settings/authentication/http-digest-edit.html'\"></span>\n" +
    "    </collapse-body>\n" +
    "</ag-collapse>\n" +
    "");
}]);

angular.module("html/settings/authentication/index.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/index.html",
    "<div ng-show=\"showAuthenticationSetup()\">\n" +
    "    <div class=\"panel\">\n" +
    "        <p>\n" +
    "        There is currently NO Authentication. Select <b>ONE</b> of the\n" +
    "        authentication methods below for your application:\n" +
    "        </p>\n" +
    "    \n" +
    "        <div class=\"btn-group\">\n" +
    "            <button ng-click=\"showHttpBasicAuthenticationForm = true\" class=\"btn btn-sm btn-primary\" title=\"HTTP Basic\">\n" +
    "                HTTP Basic\n" +
    "            </button>\n" +
    "\n" +
    "            <button ng-click=\"showHttpDigestAuthenticationForm = true\" class=\"btn btn-sm btn-primary\" title=\"HTTP Digest\">\n" +
    "                HTTP Digest\n" +
    "            </button>\n" +
    "\n" +
    "            <button ng-click=\"showOAuth2AuthenticationForm = true\" class=\"btn btn-sm btn-primary\" title=\"OAuth2\">\n" +
    "                OAuth2\n" +
    "            </button>\n" +
    "        </div>\n" +
    "    </div>\n" +
    "\n" +
    "    <p><a href=\"https://apigility.org/documentation/auth/authentication-about\" target=\"_blank\">\n" +
    "        <i class=\"glyphicon glyphicon-info-sign\"></i> More Information\n" +
    "    </a></p>\n" +
    "</div>\n" +
    "\n" +
    "<ag-conditional-include condition=\"showHttpBasicAuthenticationForm\"\n" +
    "    src=\"html/settings/authentication/new-http-basic.html\"></ag-conditional-include>\n" +
    "\n" +
    "<ag-conditional-include condition=\"showHttpBasicAuthentication\"\n" +
    "    src=\"html/settings/authentication/http-basic.html\"></ag-conditional-include>\n" +
    "\n" +
    "<ag-conditional-include condition=\"showHttpDigestAuthenticationForm\"\n" +
    "    src=\"html/settings/authentication/new-http-digest.html\"></ag-conditional-include>\n" +
    "\n" +
    "<ag-conditional-include condition=\"showHttpDigestAuthentication\"\n" +
    "    src=\"html/settings/authentication/http-digest.html\"></ag-conditional-include>\n" +
    "\n" +
    "<ag-conditional-include condition=\"showOAuth2AuthenticationForm\"\n" +
    "    src=\"html/settings/authentication/new-oauth2.html\"></ag-conditional-include>\n" +
    "\n" +
    "<ag-conditional-include condition=\"showOAuth2Authentication\"\n" +
    "    src=\"html/settings/authentication/oauth2.html\"></ag-conditional-include>\n" +
    "\n" +
    "");
}]);

angular.module("html/settings/authentication/new-http-basic.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/new-http-basic.html",
    "<div class=\"panel panel-primary\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">Setup HTTP Basic Authentication</h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel-body\">\n" +
    "        <form class=\"form\" ng-submit=\"createHttpBasicAuthentication()\" ag-form>\n" +
    "            <fieldset>\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"realm\">Authentication Realm</label>\n" +
    "                    <input type=\"text\" placeholder=\"api\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"realm\">\n" +
    "                    <p class=\"help-block\">The Authentication Realm for the HTTP Basic Authentication</p>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"htpasswd\">Location of htpasswd file</label>\n" +
    "                    <input type=\"text\" placeholder=\"data/htpasswd\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"htpasswd\">\n" +
    "                    <p class=\"help-block\">Location on the filesystem of the htpasswd file</p>\n" +
    "                </div>\n" +
    "            </fieldset>\n" +
    "\n" +
    "            <div class=\"btn-group pull-right\">\n" +
    "                <button class=\"btn btn-sm btn-default\" type=\"button\" ng-click=\"resetForm()\">Cancel</button>\n" +
    "                <button class=\"btn btn-sm btn-primary\" type=\"submit\">Save</button>\n" +
    "            </div>\n" +
    "        </form>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/settings/authentication/new-http-digest.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/new-http-digest.html",
    "<div class=\"panel panel-primary\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">Setup HTTP Digest Authentication</h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel-body\">\n" +
    "        <form class=\"form\" ng-submit=\"createHttpDigestAuthentication()\" ag-form>\n" +
    "            <fieldset>\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"realm\">Realm</label>\n" +
    "                    <input type=\"text\" placeholder=\"api\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"realm\">\n" +
    "                    <p class=\"help-block\">HTTP authentication realm</p>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"htdigest\">Location of htdigest file</label>\n" +
    "                    <input type=\"text\" placeholder=\"data/htdigest\"\n" +
    "                        class=\"form-control input-xlarge\" required=\"required\" ng-model=\"htdigest\">\n" +
    "                    <p class=\"help-block\">Location on the filesystem of the htdigest file</p>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"digest_domains\">Digest domains</label>\n" +
    "                    <tags-input \n" +
    "                        custom-class=\"ag-tags\"\n" +
    "                        ng-model=\"digest_domains\"\n" +
    "                        add-on-space=\"true\"\n" +
    "                        min-length=\"1\"\n" +
    "                        max-length=\"256\"\n" +
    "                        allowed-tags-pattern=\"^/[a-zA-Z0-9_+./%-]*$\"\n" +
    "                        placeholder=\"Add a path\">\n" +
    "                    </tags-input>\n" +
    "                    <p class=\"help-block\">Space-separated list of URI paths for which authentication will be applied</p>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"nonce_timeout\">Nonce timeout</label>\n" +
    "                    <input type=\"text\" placeholder=\"3600\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"nonce_timeout\">\n" +
    "                    <p class=\"help-block\">Expiration in seconds for inactive authentication</p>\n" +
    "                </div>\n" +
    "            </fieldset>\n" +
    "\n" +
    "            <div class=\"btn-group pull-right\">\n" +
    "                <button class=\"btn btn-sm btn-default\" type=\"button\" ng-click=\"resetForm()\">Cancel</button>\n" +
    "                <button class=\"btn btn-sm btn-primary\" type=\"submit\">Save</button>\n" +
    "            </div>\n" +
    "        </form>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/settings/authentication/new-oauth2.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/new-oauth2.html",
    "<div class=\"panel panel-primary\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">Setup OAuth2 Authentication</h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel-body\">\n" +
    "        <form class=\"form\" ng-submit=\"createOAuth2Authentication()\" ag-form>\n" +
    "            <fieldset>\n" +
    "                <div class=\"form-group\">\n" +
    "                    <div class=\"btn-group\" data-toggle=\"buttons\">\n" +
    "                        <label class=\"btn btn-primary\">\n" +
    "                            <input type=\"radio\" name=\"dsn_type\" ng-model=\"dsn_type\" value=\"PDO\" checked=\"checked\"> PDO\n" +
    "                        </label>\n" +
    "                        <label class=\"btn btn-primary\">\n" +
    "                            <input type=\"radio\" name=\"dsn_type\" ng-model=\"dsn_type\" value=\"Mongo\"> Mongo\n" +
    "                        </label>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "            </fieldset>\n" +
    "\n" +
    "            <fieldset ng-show=\"dsn_type == 'PDO'\">\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"dsn\">PDO DSN</label>\n" +
    "                    <input type=\"text\" placeholder=\"sqlite::memory:\" class=\"form-control input-xlarge\" ng-model=\"dsn\">\n" +
    "                    <p class=\"help-block\">The PDO database source name (DSN).</p>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"username\">Username</label>\n" +
    "                    <input type=\"text\" placeholder=\"username\" class=\"form-control input-xlarge\" ng-model=\"username\">\n" +
    "                    <p class=\"help-block\">Username associated with the database holding\n" +
    "                        OAuth2 credentials (required if not using SQLite).</p>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"password\">Password</label>\n" +
    "                    <input type=\"password\" placeholder=\"password\" class=\"form-control input-xlarge\" ng-model=\"password\">\n" +
    "                    <p class=\"help-block\">Password for the username listed (required if not using SQLite).</p>\n" +
    "                </div>\n" +
    "            </fieldset>\n" +
    "\n" +
    "            <fieldset ng-show=\"dsn_type == 'Mongo'\">\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"dsn\">Mongo server\n" +
    "                        connection string</label>\n" +
    "                    <input type=\"text\" placeholder=\"(optional) mongodb://[username:password@]host1[:port1][,host2[:port2:],...]/db\" class=\"form-control input-xlarge\" ng-model=\"dsn\">\n" +
    "                    <p class=\"help-block\">The MongoClient server connection\n" +
    "                    string; if not provided, \"mongodb://localhost:27017\" will be\n" +
    "                    used. \"mongodb://\" may be omitted from the string.</p>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"database\">Mongo Database</label>\n" +
    "                    <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"database\">\n" +
    "                    <p class=\"help-block\">The Mongo database name.</p>\n" +
    "                </div>\n" +
    "            </fieldset>\n" +
    "\n" +
    "            <fieldset>\n" +
    "                <div class=\"form-group\" ng-show=\"dsn_type\">\n" +
    "                    <label class=\"control-label\" for=\"route_match\">OAuth2 route</label>\n" +
    "                    <input type=\"text\" placeholder=\"/oauth\" class=\"form-control input-xlarge\" ng-model=\"route_match\">\n" +
    "                    <p class=\"help-block\">Base URI to use as the OAuth2 server endpoint.</p>\n" +
    "                </div>\n" +
    "            </fieldset>\n" +
    "\n" +
    "            <div class=\"btn-group pull-right\">\n" +
    "                <button class=\"btn btn-sm btn-default\" type=\"button\" ng-click=\"resetForm()\">Cancel</button>\n" +
    "                <button class=\"btn btn-sm btn-primary\" type=\"submit\">Save</button>\n" +
    "            </div>\n" +
    "        </form>\n" +
    "    </div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/settings/authentication/oauth2-edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/oauth2-edit.html",
    "<div class=\"panel-body\"><form ag-form class=\"form\" ng-submit=\"updateOAuth2Authentication()\">\n" +
    "    <fieldset ng-show=\"oauth2.dsn_type == 'PDO'\">\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"dsn\">PDO DSN</label>\n" +
    "            <input type=\"text\" placeholder=\"sqlite::memory:\" class=\"form-control input-xlarge\" ng-model=\"oauth2.dsn\">\n" +
    "            <p class=\"help-block\">The PDO database source name (DSN).</p>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"username\">PDO Username</label>\n" +
    "            <input type=\"text\" placeholder=\"username\" class=\"form-control input-xlarge\" ng-model=\"oauth2.username\">\n" +
    "            <p class=\"help-block\">Username associated with the database holding\n" +
    "                OAuth2 credentials (required if not using SQLite).</p>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"password\">PDO Password</label>\n" +
    "            <input type=\"password\" placeholder=\"password\" class=\"form-control input-xlarge\" ng-model=\"oauth2.password\">\n" +
    "            <p class=\"help-block\">Password for the username listed (required if not using SQLite).</p>\n" +
    "        </div>\n" +
    "    </fieldset>\n" +
    "\n" +
    "    <fieldset ng-show=\"oauth2.dsn_type == 'Mongo'\">\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"dsn\">Mongo server\n" +
    "                connection string</label>\n" +
    "            <input type=\"text\" placeholder=\"(optional) mongodb://[username:password@]host1[:port1][,host2[:port2:],...]/db\" class=\"form-control input-xlarge\" ng-model=\"oauth2.dsn\">\n" +
    "            <p class=\"help-block\">The MongoClient server connection\n" +
    "            string; if not provided, \"mongodb://localhost:27017\" will be\n" +
    "            used. \"mongodb://\" may be omitted from the string.</p>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\" ng-show=\"oauth2.dsn_type == 'Mongo'\">\n" +
    "            <label class=\"control-label\" for=\"database\">Mongo Database</label>\n" +
    "            <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"oauth2.database\">\n" +
    "            <p class=\"help-block\">The Mongo database name.</p>\n" +
    "        </div>\n" +
    "    </fieldset>\n" +
    "\n" +
    "    <fieldset>\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"route_match\">OAuth2 route</label>\n" +
    "            <input type=\"text\" placeholder=\"/oauth\" class=\"form-control input-xlarge\" ng-model=\"oauth2.route_match\">\n" +
    "            <p class=\"help-block\">Base URI to use as the OAuth2 server endpoint.</p>\n" +
    "        </div>\n" +
    "    </fieldset>\n" +
    "\n" +
    "    <div class=\"btn-group pull-right\">\n" +
    "        <button\n" +
    "            type=\"button\" title=\"Cancel\" class=\"btn btn-sm btn-default\"\n" +
    "            collapse-flag flags=\"{edit: false}\" click=\"cancelEdit\">Cancel</button>\n" +
    "        <button type=\"submit\" title=\"Save\" class=\"btn btn-sm btn-success\">Save</button>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"clearfix\"></div>\n" +
    "</form></div>\n" +
    "");
}]);

angular.module("html/settings/authentication/oauth2-view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/oauth2-view.html",
    "<table class=\"table table-striped\">\n" +
    "    <tr ng-show=\"oauth2.dsn_type == 'PDO'\">\n" +
    "        <td>PDO DSN</td>\n" +
    "        <td>{{oauth2.dsn}}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr ng-show=\"oauth2.username && (oauth2.dsn_type == 'PDO')\">\n" +
    "        <td>Database username</td>\n" +
    "        <td>{{oauth2.username}}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr ng-show=\"oauth2.password && (oauth2.dsn_type == 'PDO')\">\n" +
    "        <td>Database password</td>\n" +
    "        <td>{{oauth2.password}}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr ng-show=\"oauth2.dsn_type == 'Mongo'\">\n" +
    "        <td>Mongo server connection string</td>\n" +
    "        <td>{{oauth2.dsn}}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr ng-show=\"oauth2.database && (oauth2.dsn_type == 'Mongo')\">\n" +
    "        <td>Database name</td>\n" +
    "        <td>{{oauth2.database}}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr>\n" +
    "        <td>OAuth2 route</td>\n" +
    "        <td>{{oauth2.route_match}}</td>\n" +
    "    </tr>\n" +
    "</table>\n" +
    "");
}]);

angular.module("html/settings/authentication/oauth2.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/oauth2.html",
    "<ag-collapse class=\"panel-info\"\n" +
    "    no-chevron\n" +
    "    conditionals=\"{{ {edit: inEdit, delete: false} }}\">\n" +
    "    <collapse-header>\n" +
    "        <h4 class=\"panel-title\">\n" +
    "            <i class=\"glyphicon glyphicon-lock\"></i>\n" +
    "\n" +
    "            OAuth2 Settings\n" +
    "\n" +
    "            <div class=\"btn-group pull-right\">\n" +
    "                <button\n" +
    "                    type=\"button\" title=\"Cancel\" class=\"btn btn-sm btn-default\"\n" +
    "                    collapse-flag flags=\"{edit: false}\" collapse-click=\"cancelEdit\"\n" +
    "                    collapse-button criteria=\"{delete: false, edit: true}\">\n" +
    "                    Cancel\n" +
    "                </button>\n" +
    "\n" +
    "                <button \n" +
    "                    type=\"button\" class=\"btn btn-sm btn-success\" title=\"Edit settings\"\n" +
    "                    collapse-flag flags=\"{edit: true}\" collapse-click=\"startEdit\"\n" +
    "                    collapse-button criteria=\"{delete: false, edit: false}\">\n" +
    "                    <i class=\"glyphicon glyphicon-edit\"></i>\n" +
    "                </button>\n" +
    "\n" +
    "                <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Remove Authentication\"\n" +
    "                    collapse-flag flags=\"{delete: true}\"\n" +
    "                    collapse-button criteria=\"{delete: false}\">\n" +
    "                    <i class=\"glyphicon glyphicon-trash\"></i>\n" +
    "                </button>\n" +
    "            </div>\n" +
    "        </h4>\n" +
    "\n" +
    "        <div class=\"clearfix\"></div>\n" +
    "\n" +
    "        <span collapse-show\n" +
    "            criteria=\"{delete: false}\"\n" +
    "            default-template=\"'html/empty-content.html'\"\n" +
    "            toggled-template=\"'html/settings/authentication/remove.html'\"></span>\n" +
    "    </collapse-header>\n" +
    "\n" +
    "    <collapse-body>\n" +
    "        <span\n" +
    "            collapse-show\n" +
    "            criteria=\"{edit: false}\"\n" +
    "            default-template=\"'html/settings/authentication/oauth2-view.html'\"\n" +
    "            toggled-template=\"'html/settings/authentication/oauth2-edit.html'\"></span>\n" +
    "    </collapse-body>\n" +
    "</ag-collapse>\n" +
    "\n" +
    "");
}]);

angular.module("html/settings/authentication/remove.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/authentication/remove.html",
    "<div class=\"panel panel-danger\">\n" +
    "    <div class=\"panel-heading\"><h4 class=\"panel-title\">Remove Authentication</h4></div>\n" +
    "    <div class=\"panel-body\">\n" +
    "        <form ng-submit=\"removeAuthentication()\">\n" +
    "            <p>Are you sure you want to remove authentication?</p>\n" +
    "\n" +
    "            <div class=\"btn-group pull-right\">\n" +
    "                <button collapse-flag flags=\"{delete: false}\" type=\"button\" class=\"btn btn-sm btn-default\">Cancel</button>\n" +
    "                <button type=\"submit\" class=\"btn btn-sm btn-danger\">Yes</button>\n" +
    "            </div>\n" +
    "        </form>\n" +
    "    </div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/settings/content-negotiation/edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/content-negotiation/edit.html",
    "<form class=\"form-inline\" role=\"form\" ag-form>\n" +
    "<div class=\"panel-body\">\n" +
    "  <div class=\"ag-new-input pull-right\">\n" +
    "    <div class=\"form-group\">\n" +
    "      <input type=\"text\" placeholder=\"View model class\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"selector.viewModel\">\n" +
    "    </div>\n" +
    "\n" +
    "    <button type=\"button\" ng-click=\"addViewModel(selector.viewModel, selector)\" class=\"btn btn-default btn-success\" title=\"Add View Model\">\n" +
    "        Add View Model\n" +
    "    </button>\n" +
    "  </div>\n" +
    "</div>\n" +
    "\n" +
    "<table class=\"table table-striped\">\n" +
    "  <thead>\n" +
    "    <tr>\n" +
    "      <th width=\"40%\">View Model</th>\n" +
    "      <th>Mediatypes</th>\n" +
    "      <th width=\"5%\">&nbsp;</th>\n" +
    "    </tr>\n" +
    "  </thead>\n" +
    "\n" +
    "  <tbody>\n" +
    "    <tr ng-repeat=\"(viewModel, mediaTypes) in selector.selectors\">\n" +
    "      <td>{{ viewModel }}</td>\n" +
    "\n" +
    "      <td>\n" +
    "        <div class=\"control-group\">\n" +
    "          <tags-input\n" +
    "            custom-class=\"ag-tags\"\n" +
    "            ng-model=\"mediaTypes\"\n" +
    "            add-on-space=\"true\"\n" +
    "            max-length=\"256\"\n" +
    "            allowed-tags-pattern=\"^[a-zA-Z-]+/[a-zA-Z0-9*_+.-]+$\"\n" +
    "            placeholder=\"Add a mediatype\">\n" +
    "          </tags-input>\n" +
    "        </div>\n" +
    "      </td>\n" +
    "\n" +
    "      <td>\n" +
    "        <button type=\"button\" class=\"btn btn-sm btn-danger\"\n" +
    "          ng-click=\"removeViewModel(viewModel, selector)\">\n" +
    "          <i class=\"glyphicon glyphicon-trash\"></i>\n" +
    "        </button>\n" +
    "      </td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr>\n" +
    "      <td colspan=\"3\">\n" +
    "        <div class=\"btn-group pull-right\">\n" +
    "          <button type=\"button\" collapse-flag flags=\"{editSelector: false}\"\n" +
    "              ag-cancel-edit ng-click=\"resetSelectorForm(selector)\" class=\"btn btn-sm btn-default\">Cancel</button>\n" +
    "          <button type=\"button\" ng-click=\"updateSelector(selector)\" class=\"btn btn-sm btn-success\">Update Selector</button>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"clearfix\"></div>\n" +
    "      </td>\n" +
    "    </tr>\n" +
    "  </tbody>\n" +
    "</table>\n" +
    "</form>\n" +
    "");
}]);

angular.module("html/settings/content-negotiation/index.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/content-negotiation/index.html",
    "<ag-include src=\"html/settings/content-negotiation/new-selector-form.html\"></ag-include>\n" +
    "\n" +
    "<div class=\"ag panel-group tooltip-api\">\n" +
    "  <ag-collapse class=\"panel-info\"\n" +
    "    ng-repeat=\"selector in selectors\"\n" +
    "    active=\"{{ activeSelector == selector.content_name }}\"\n" +
    "    name=\"{{ selector.content_name }}\"\n" +
    "    searchparam=\"selector\"\n" +
    "    conditionals=\"{{ {deleteSelector: false, editSelector: inEdit} }}\">\n" +
    "    <collapse-header>\n" +
    "      <h4 class=\"panel-title\">\n" +
    "        <i class=\"glyphicon glyphicon-tags\"></i> {{ selector.content_name }}\n" +
    "\n" +
    "        <div class=\"btn-group pull-right\">\n" +
    "            <button\n" +
    "                type=\"button\" title=\"Cancel\" class=\"btn btn-sm btn-default\"\n" +
    "                collapse-button criteria=\"{deleteSelector: false, editSelector: true}\"\n" +
    "                ui-sref=\"ag.settings.content-negotiation({ edit: null})\"\n" +
    "                ui-sref-options=\"{ notify: false }\">\n" +
    "                Cancel\n" +
    "            </button>\n" +
    "\n" +
    "            <button \n" +
    "                type=\"button\" class=\"btn btn-sm btn-success\" title=\"Edit selector\"\n" +
    "                collapse-button criteria=\"{deleteSelector: false, editSelector: false}\"\n" +
    "                ui-sref=\"ag.settings.content-negotiation({ edit: true})\"\n" +
    "                ui-sref-options=\"{ notify: false }\">\n" +
    "                <i class=\"glyphicon glyphicon-edit\"></i>\n" +
    "            </button>\n" +
    "\n" +
    "            <button \n" +
    "                type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Remove selector\"\n" +
    "                collapse-flag flags=\"{deleteSelector: true}\"\n" +
    "                collapse-button criteria=\"{deleteSelector: false}\">\n" +
    "                <i class=\"glyphicon glyphicon-trash\"></i>\n" +
    "            </button>\n" +
    "        </div>\n" +
    "      </h4>\n" +
    "\n" +
    "      <div class=\"clearfix\"></div>\n" +
    "\n" +
    "      <span collapse-show\n" +
    "          criteria=\"{deleteSelector: false}\"\n" +
    "          default-template=\"'html/empty-content.html'\"\n" +
    "          toggled-template=\"'html/settings/content-negotiation/remove.html'\"></span>\n" +
    "    </collapse-header>\n" +
    "\n" +
    "    <collapse-body>\n" +
    "        <div collapse-show criteria=\"{editSelector: false}\"\n" +
    "            default-template=\"'html/settings/content-negotiation/view.html'\"\n" +
    "            toggled-template=\"'html/settings/content-negotiation/edit.html'\"></div>\n" +
    "    </collapse-body>\n" +
    "  </ag-collapse>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/settings/content-negotiation/new-selector-form.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/content-negotiation/new-selector-form.html",
    "<div>\n" +
    "  <button class=\"btn btn-info pull-left\" title=\"Help\" ng-click=\"help()\">\n" +
    "    <i class=\"glyphicon glyphicon-info-sign\"></i>\n" +
    "  </button>\n" +
    "\n" +
    "  <form ng-hide=\"showNewSelectorForm\" class=\"form-inline pull-right\" role=\"form\">\n" +
    "    <div class=\"form-group\">\n" +
    "      <input type=\"text\" placeholder=\"Selector name\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"newSelector.content_name\">\n" +
    "    </div>\n" +
    "\n" +
    "    <button type=\"button\" ng-click=\"showNewSelectorForm = true\" class=\"btn btn-default btn-primary\" title=\"Create New Selector\">\n" +
    "      Create New Selector\n" +
    "    </button>\n" +
    "  </form>\n" +
    "</div>\n" +
    "<div class=\"clearfix ag-new-input\"></div>\n" +
    "\n" +
    "<div ng-show=\"showNewSelectorForm\" class=\"panel panel-primary\">\n" +
    "  <div class=\"panel-heading\">\n" +
    "    <h4 class=\"panel-title\">{{ newSelector.content_name }}</h4>\n" +
    "  </div>\n" +
    "\n" +
    "  <div class=\"panel-body\">\n" +
    "    <div class=\"ag-new-input pull-right\"><form class=\"form-inline\" role=\"form\" ag-form>\n" +
    "      <div class=\"form-group\">\n" +
    "        <input type=\"text\" placeholder=\"View model class\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"newSelector.viewModel\">\n" +
    "      </div>\n" +
    "\n" +
    "      <button type=\"button\" ng-click=\"addViewModel(newSelector.viewModel, newSelector)\" class=\"btn btn-default btn-primary\" title=\"Add View Model\">\n" +
    "          Add View Model\n" +
    "      </button>\n" +
    "    </form></div>\n" +
    "  </div>\n" +
    "\n" +
    "  <table class=\"table table-striped\">\n" +
    "    <thead>\n" +
    "      <tr>\n" +
    "        <th width=\"40%\">View Model</th>\n" +
    "        <th>Mediatypes</th>\n" +
    "      </tr>\n" +
    "    </thead>\n" +
    "\n" +
    "    <tbody>\n" +
    "      <tr ng-repeat=\"(viewModel, mediaTypes) in newSelector.selectors\">\n" +
    "        <td>{{ viewModel }}</td>\n" +
    "\n" +
    "        <td>\n" +
    "          <div class=\"control-group\">\n" +
    "            <tags-input\n" +
    "              custom-class=\"ag-tags\"\n" +
    "              ng-model=\"mediaTypes\"\n" +
    "              add-on-space=\"true\"\n" +
    "              max-length=\"256\"\n" +
    "              allowed-tags-pattern=\"^[a-zA-Z-]+/[a-zA-Z0-9*_+.-]+$\"\n" +
    "              placeholder=\"Add a mediatype\">\n" +
    "            </tags-input>\n" +
    "          </div>\n" +
    "        </td>\n" +
    "      </tr>\n" +
    "\n" +
    "      <tr>\n" +
    "        <td colspan=\"2\">\n" +
    "          <div class=\"btn-group pull-right\">\n" +
    "            <button type=\"button\" ng-click=\"resetNewSelectorForm()&&(showNewSelectorForm = false)\" class=\"btn btn-sm btn-default\">Cancel</button>\n" +
    "            <button type=\"button\" ng-click=\"createSelector()\" class=\"btn btn-sm btn-primary\">Create Selector</button>\n" +
    "          </div>\n" +
    "\n" +
    "          <div class=\"clearfix\"></div>\n" +
    "        </td>\n" +
    "      </tr>\n" +
    "    </tbody>\n" +
    "  </table>\n" +
    "</div>\n" +
    "\n" +
    "");
}]);

angular.module("html/settings/content-negotiation/remove.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/content-negotiation/remove.html",
    "<div class=\"panel panel-danger\">\n" +
    "    <div class=\"panel-heading\"><h4 class=\"panel-title\">Remove Selector</h4></div>\n" +
    "    <div class=\"panel-body\">\n" +
    "    <form ng-submit=\"removeSelector(selector.content_name)\">\n" +
    "        <p>Are you sure you want to delete the Selector?</p>\n" +
    "\n" +
    "        <div class=\"btn-group pull-right\">\n" +
    "        <button collapse-flag flags=\"{deleteSelector: false}\" type=\"button\" class=\"btn btn-sm btn-default\">Cancel</button>\n" +
    "        <button type=\"submit\" class=\"btn btn-sm btn-danger\">Yes</button>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"clearfix\"></div>\n" +
    "    </form>\n" +
    "    </div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/settings/content-negotiation/view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/content-negotiation/view.html",
    "<table class=\"table table-striped\">\n" +
    "  <thead>\n" +
    "    <tr>\n" +
    "      <th width=\"40%\">View Model</th>\n" +
    "      <th>Mediatypes</th>\n" +
    "    </tr>\n" +
    "  </thead>\n" +
    "\n" +
    "  <tbody>\n" +
    "    <tr ng-repeat=\"(viewModel, mediaTypes) in selector.selectors\">\n" +
    "      <td>{{ viewModel }}</td>\n" +
    "\n" +
    "      <td>\n" +
    "        <ul>\n" +
    "          <li ng-repeat=\"mediaType in mediaTypes\">{{ mediaType }}</li>\n" +
    "        </ul>\n" +
    "      </td>\n" +
    "    </tr>\n" +
    "  </tbody>\n" +
    "</table>\n" +
    "\n" +
    "");
}]);

angular.module("html/settings/dashboard.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/dashboard.html",
    "<div class=\"panel panel-info\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">\n" +
    "            <i class=\"glyphicon glyphicon-lock\"></i>\n" +
    "            <a ui-sref=\"ag.settings.authentication\">Authentication</a>\n" +
    "        </h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel-body\" ng-show=\"!dashboard.authentication\">\n" +
    "        <p class=\"text-warning\">\n" +
    "            No authentication configured; <a ui-sref=\"ag.settings.authentication\">would you like to set it up now?</a>\n" +
    "        </p>\n" +
    "\n" +
    "        <p><a href=\"https://apigility.org/documentation/auth/authentication-about\" target=\"_blank\">\n" +
    "            <i class=\"glyphicon glyphicon-info-sign\"></i> More information\n" +
    "        </a></p>    \n" +
    "    </div>\n" +
    "\n" +
    "    <table class=\"table\">\n" +
    "        <ag-conditional-include\n" +
    "            condition=\"isHttpBasicAuthentication(dashboard.authentication)\"\n" +
    "            src=\"html/settings/authentication/http-basic-view.html\"></ag-conditional-include>\n" +
    "        <ag-conditional-include\n" +
    "            condition=\"isHttpDigestAuthentication(dashboard.authentication)\"\n" +
    "            src=\"html/settings/authentication/http-digest-view.html\"></ag-conditional-include>\n" +
    "        <ag-conditional-include\n" +
    "            condition=\"isOAuth2(dashboard.authentication)\"\n" +
    "            src=\"html/settings/authentication/oauth2-view.html\"></ag-conditional-include>\n" +
    "    </table>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"panel panel-info\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">\n" +
    "            <i class=\"glyphicon glyphicon-tags\"></i>\n" +
    "            <a ui-sref=\"ag.settings.content-negotiation\">Content Negotiation</a>\n" +
    "        </h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel-body\" ng-show=\"!dashboard.contentNegotiation.length\">\n" +
    "        <p class=\"text-warning\">\n" +
    "            No content negotiation selector rules configured;\n" +
    "            <a ui-sref=\"ag.settings.content-negotiation\">would you like to set one up now?</a>\n" +
    "        </p>\n" +
    "\n" +
    "        <p><a href=\"https://apigility.org/documentation/api-primer/content-negotiation\" target=\"_blank\">\n" +
    "            <i class=\"glyphicon glyphicon-info-sign\"></i> More information\n" +
    "        </a></p>    \n" +
    "    </div>\n" +
    "\n" +
    "    <ul class=\"list-group\">\n" +
    "        <li ng-repeat=\"selector in dashboard.contentNegotiation\" class=\"list-group-item\">\n" +
    "            <a ui-sref=\"ag.settings.content-negotiation({selector: selector.content_name})\">{{ selector.content_name }}</a>\n" +
    "        </li>\n" +
    "    </ul>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"panel panel-info\">\n" +
    "    <div class=\"panel-heading\">\n" +
    "        <h4 class=\"panel-title\">\n" +
    "            <i class=\"glyphicon glyphicon-book\"></i>\n" +
    "            <a ui-sref=\"ag.settings.db-adapters\">Database Adapters</a>\n" +
    "        </h4>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"panel-body\" ng-show=\"!dashboard.dbAdapters.length\">\n" +
    "        <p class=\"text-warning\">\n" +
    "            No database adapters configured;\n" +
    "            <a ui-sref=\"ag.settings.db-adapters\">would you like to set one up now?</a>\n" +
    "        </p>\n" +
    "    </div>\n" +
    "\n" +
    "    <ul class=\"list-group\">\n" +
    "        <li ng-repeat=\"adapter in dashboard.dbAdapters\" class=\"list-group-item\">\n" +
    "            <a ui-sref=\"ag.settings.db-adapters({adapter: adapter.adapter_name})\">{{ adapter.adapter_name }}</a>\n" +
    "        </li>\n" +
    "    </ul>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/settings/db-adapters/edit.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/db-adapters/edit.html",
    "<form class=\"panel-body\" novalidate ng-submit=\"saveDbAdapter($index)\" ag-form>\n" +
    "    <fieldset>\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"adapterName\">Adapter Name</label>\n" +
    "            <div class=\"controls\">\n" +
    "                <input type=\"text\" placeholder=\"adapter name\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"dbAdapter.adapter_name\">\n" +
    "                <p class=\"help-block\">\"Virtual\" name of the DB adapter service</p>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"driver\">Driver Type</label>\n" +
    "            <div class=\"controls\">\n" +
    "                <select class=\"form-control input-xlarge\" ng-model=\"dbAdapter.driver\">\n" +
    "                    <option>IbmDb2</option>\n" +
    "                    <option>Mysqli</option>\n" +
    "                    <option>Oci8</option>\n" +
    "                    <option>Pdo_Mysql</option>\n" +
    "                    <option>Pdo_Oci</option>\n" +
    "                    <option>Pdo_Pgsql</option>\n" +
    "                    <option>Pdo_Sqlite</option>\n" +
    "                    <option>Pgsql</option>\n" +
    "                    <option>Sqlsrv</option>\n" +
    "                </select>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"database\">Database</label>\n" +
    "            <div class=\"controls\">\n" +
    "                <input type=\"text\" placeholder=\"database name\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"dbAdapter.database\">\n" +
    "                <p class=\"help-block\">Name of database or database file</p>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\">\n" +
    "            <label class=\"control-label\" for=\"dsn\">DSN</label>\n" +
    "            <div class=\"controls\">\n" +
    "                <input type=\"text\" placeholder=\"(Optional) DSN for database\" class=\"form-control input-xlarge\" ng-model=\"dbAdapter.dsn\">\n" +
    "                <p class=\"help-block\">DSN for the database; can be used to\n" +
    "                provide the full database connection specification. Use this if\n" +
    "                developing for Google App Engine.</p>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\" ng-show=\"dbAdapter.driver != 'Pdo_Sqlite'\">\n" +
    "            <label class=\"control-label\" for=\"username\">Username</label>\n" +
    "            <div class=\"controls\">\n" +
    "                <input type=\"text\" placeholder=\"(Optional) Username\" class=\"form-control input-xlarge\" ng-model=\"dbAdapter.username\">\n" +
    "                <p class=\"help-block\">Username with which to connect</p>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\" ng-show=\"dbAdapter.driver != 'Pdo_Sqlite'\">\n" +
    "            <label class=\"control-label\" for=\"password\">Password</label>\n" +
    "            <div class=\"controls\">\n" +
    "                <input type=\"text\" placeholder=\"(Optional) Password\" class=\"form-control input-xlarge\" ng-model=\"dbAdapter.password\">\n" +
    "                <p class=\"help-block\">Password with which to connect</p>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\" ng-show=\"dbAdapter.driver != 'Pdo_Sqlite'\">\n" +
    "            <label class=\"control-label\" for=\"hostname\">Hostname</label>\n" +
    "            <div class=\"controls\">\n" +
    "                <input type=\"text\" placeholder=\"(Optional) Hostname\" class=\"form-control input-xlarge\" ng-model=\"dbAdapter.hostname\">\n" +
    "                <p class=\"help-block\">Hostname to which to connect</p>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\" ng-show=\"dbAdapter.driver != 'Pdo_Sqlite'\">\n" +
    "            <label class=\"control-label\" for=\"port\">Port</label>\n" +
    "            <div class=\"controls\">\n" +
    "                <input type=\"text\" placeholder=\"(Optional) Port\" class=\"form-control input-xlarge\" ng-model=\"dbAdapter.port\">\n" +
    "                <p class=\"help-block\">Port to which to connect</p>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"form-group\" ng-show=\"dbAdapter.driver && (dbAdapter.driver != 'Pdo_Pgsql') && (dbAdapter.driver != 'Pgsql')\">\n" +
    "            <label class=\"control-label\" for=\"charset\">Charset</label>\n" +
    "            <div class=\"controls\">\n" +
    "                <input type=\"text\" placeholder=\"(Optional) Charset\" class=\"form-control input-xlarge\" ng-model=\"dbAdapter.charset\">\n" +
    "                <p class=\"help-block\">Charset of database</p>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "    </fieldset>\n" +
    "\n" +
    "    <ag-collapse class=\"panel-default\">\n" +
    "        <collapse-header>\n" +
    "            <h4 class=\"panel-title\">Driver Options</h4>\n" +
    "        </collapse-header>\n" +
    "\n" +
    "        <collapse-body>\n" +
    "            <li\n" +
    "                class=\"list-group-item\"\n" +
    "                ng-repeat=\"(optionKey, optionValue) in dbAdapter.driver_options\">\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label>Option:</label>\n" +
    "                    <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"optionKey\">\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label>Value:</label>\n" +
    "                    <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"optionValue\">\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"btn-group ag-new-input pull-right\">\n" +
    "                    <button type=\"button\" class=\"btn btn-sm btn-danger\"\n" +
    "                        ng-click=\"removeDriverOption(dbAdapter, optionKey)\">Remove Option</button>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"clearfix\"></div>\n" +
    "            </li>\n" +
    "\n" +
    "            <li class=\"list-group-item\">\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label>Option:</label>\n" +
    "                    <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"dbAdapter._newOptionKey\">\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label>Value:</label>\n" +
    "                    <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"dbAdapter._newOptionValue\">\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"btn-group ag-new-input pull-right\">\n" +
    "                    <button type=\"button\" class=\"btn btn-sm btn-primary\"\n" +
    "                        ng-click=\"addDriverOption(dbAdapter)\">Add Option</button>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"clearfix\"></div>\n" +
    "            </li>\n" +
    "        </collapse-body>\n" +
    "    </ag-collapse>\n" +
    "\n" +
    "    <div class=\"btn-group pull-right\">\n" +
    "        <button type=\"button\" class=\"btn btn-sm btn-default\" ag-cancel-edit>Cancel</a>\n" +
    "        <button type=\"submit\" class=\"btn btn-sm btn-success\">Save</button>\n" +
    "    </div>\n" +
    "\n" +
    "    <div class=\"clearfix\"></div>\n" +
    "</form>\n" +
    "\n" +
    "");
}]);

angular.module("html/settings/db-adapters/index.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/db-adapters/index.html",
    "<ag-include src=\"html/settings/db-adapters/new-adapter-form.html\"></ag-include>\n" +
    "\n" +
    "<div class=\"ag panel-group tooltip-api\">\n" +
    "  <ag-collapse class=\"panel-info\"\n" +
    "    ng-repeat=\"dbAdapter in dbAdapters\"\n" +
    "    active=\"{{ activeAdapter == dbAdapter.adapter_name }}\"\n" +
    "    name=\"{{ dbAdapter.adapter_name }}\"\n" +
    "    searchparam=\"adapter\"\n" +
    "    conditionals=\"{{ {edit: inEdit, delete: false} }}\">\n" +
    "    <collapse-header>\n" +
    "      <h4 class=\"panel-title\">\n" +
    "        <i class=\"glyphicon glyphicon-book\"></i>\n" +
    "\n" +
    "        {{ dbAdapter.adapter_name }}\n" +
    "\n" +
    "        <div class=\"btn-group pull-right\">\n" +
    "            <button\n" +
    "                type=\"button\" title=\"Cancel\" class=\"btn btn-sm btn-default\"\n" +
    "                collapse-button criteria=\"{delete: false, edit: true}\"\n" +
    "                ui-sref=\"ag.settings.db-adapters({ edit: null})\"\n" +
    "                ui-sref-options=\"{ notify: false }\">\n" +
    "                Cancel\n" +
    "            </button>\n" +
    "\n" +
    "            <button \n" +
    "                type=\"button\" class=\"btn btn-sm btn-success\" title=\"Edit adapter\"\n" +
    "                collapse-button criteria=\"{delete: false, edit: false}\"\n" +
    "                ui-sref=\"ag.settings.db-adapters({ edit: true})\"\n" +
    "                ui-sref-options=\"{ notify: false }\">\n" +
    "                <i class=\"glyphicon glyphicon-edit\"></i>\n" +
    "            </button>\n" +
    "\n" +
    "            <button \n" +
    "                type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Remove adapter\"\n" +
    "                collapse-flag flags=\"{delete: true}\"\n" +
    "                collapse-button criteria=\"{delete: false}\">\n" +
    "                <i class=\"glyphicon glyphicon-trash\"></i>\n" +
    "            </button>\n" +
    "        </div>\n" +
    "      </h4>\n" +
    "\n" +
    "      <div class=\"clearfix\"></div>\n" +
    "\n" +
    "      <span collapse-show\n" +
    "          criteria=\"{delete: false}\"\n" +
    "          default-template=\"'html/empty-content.html'\"\n" +
    "          toggled-template=\"'html/settings/db-adapters/remove.html'\"></span>\n" +
    "    </collapse-header>\n" +
    "\n" +
    "    <collapse-body>\n" +
    "        <div\n" +
    "            collapse-show\n" +
    "            criteria=\"{edit: false}\"\n" +
    "            default-template=\"'html/settings/db-adapters/view.html'\"\n" +
    "            toggled-template=\"'html/settings/db-adapters/edit.html'\"></div>\n" +
    "    </collapse-body>\n" +
    "  </ag-collapse>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/settings/db-adapters/new-adapter-form.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/db-adapters/new-adapter-form.html",
    "<div ng-hide=\"showNewDbAdapterForm\" class=\"pull-right\">\n" +
    "    <button ng-click=\"showNewDbAdapterForm = true\" class=\"btn btn-default btn-primary\" title=\"Create New DB Adapter\">\n" +
    "        Create New DB Adapter\n" +
    "    </button>\n" +
    "</div>\n" +
    "<div class=\"clearfix\"></div>\n" +
    "\n" +
    "<div ng-show=\"showNewDbAdapterForm\" class=\"panel panel-primary\">\n" +
    "    <div class=\"panel-heading\"><h4 class=\"panel-title\">Create a New DB Adapter</h4></div>\n" +
    "\n" +
    "    <div class=\"panel-body\">\n" +
    "        <form ng-submit=\"createNewDbAdapter()\" ag-form>\n" +
    "            <fieldset>\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"adapterName\">Adapter Name</label>\n" +
    "                    <div class=\"controls\">\n" +
    "                        <input type=\"text\" placeholder=\"adapter name\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"adapter_name\">\n" +
    "                        <p class=\"help-block\">\"Virtual\" name of the DB adapter\n" +
    "                            service. Examples include \"DB\\Foo\", \"db.foo\",\n" +
    "                            \"FooAdapter\"; essentially, a unique name for the adapter\n" +
    "                            that's descriptive of its purpose.</p>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"driver\">Driver Type</label>\n" +
    "                    <div class=\"controls\">\n" +
    "                        <select class=\"form-control input-xlarge\" ng-model=\"driver\">\n" +
    "                            <option>IbmDb2</option>\n" +
    "                            <option>Mysqli</option>\n" +
    "                            <option>Oci8</option>\n" +
    "                            <option>Pdo_Mysql</option>\n" +
    "                            <option>Pdo_Oci</option>\n" +
    "                            <option>Pdo_Pgsql</option>\n" +
    "                            <option>Pdo_Sqlite</option>\n" +
    "                            <option>Pgsql</option>\n" +
    "                            <option>Sqlsrv</option>\n" +
    "                        </select>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"database\">Database</label>\n" +
    "                    <div class=\"controls\">\n" +
    "                        <input type=\"text\" placeholder=\"database name\" class=\"form-control input-xlarge\" required=\"required\" ng-model=\"database\">\n" +
    "                        <p class=\"help-block\">Name of database or database file</p>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\">\n" +
    "                    <label class=\"control-label\" for=\"dsn\">DSN</label>\n" +
    "                    <div class=\"controls\">\n" +
    "                        <input type=\"text\" placeholder=\"(Optional) DSN for database\" class=\"form-control input-xlarge\" ng-model=\"dsn\">\n" +
    "                        <p class=\"help-block\">DSN for the database; can be used to\n" +
    "                        provide the full database connection specification. Use this if\n" +
    "                        developing for Google App Engine.</p>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\" ng-show=\"driver && (driver != 'Pdo_Sqlite')\">\n" +
    "                    <label class=\"control-label\" for=\"username\">Username</label>\n" +
    "                    <div class=\"controls\">\n" +
    "                        <input type=\"text\" placeholder=\"(Optional) Username\" class=\"form-control input-xlarge\" ng-model=\"username\">\n" +
    "                        <p class=\"help-block\">Username with which to connect</p>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\" ng-show=\"driver && (driver != 'Pdo_Sqlite')\">\n" +
    "                    <label class=\"control-label\" for=\"password\">Password</label>\n" +
    "                    <div class=\"controls\">\n" +
    "                        <input type=\"text\" placeholder=\"(Optional) Password\" class=\"form-control input-xlarge\" ng-model=\"password\">\n" +
    "                        <p class=\"help-block\">Password with which to connect</p>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\" ng-show=\"driver && (driver != 'Pdo_Sqlite')\">\n" +
    "                    <label class=\"control-label\" for=\"hostname\">Hostname</label>\n" +
    "                    <div class=\"controls\">\n" +
    "                        <input type=\"text\" placeholder=\"(Optional) Hostname\" class=\"form-control input-xlarge\" ng-model=\"hostname\">\n" +
    "                        <p class=\"help-block\">Hostname to which to connect</p>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\" ng-show=\"driver && (driver != 'Pdo_Sqlite')\">\n" +
    "                    <label class=\"control-label\" for=\"port\">Port</label>\n" +
    "                    <div class=\"controls\">\n" +
    "                        <input type=\"text\" placeholder=\"(Optional) Port\" class=\"form-control input-xlarge\" ng-model=\"port\">\n" +
    "                        <p class=\"help-block\">Port to which to connect</p>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "\n" +
    "                <div class=\"form-group\" ng-show=\"driver && (driver != 'Pdo_Pgsql') && (driver != 'Pgsql')\">\n" +
    "                    <label class=\"control-label\" for=\"charset\">Charset</label>\n" +
    "                    <div class=\"controls\">\n" +
    "                        <input type=\"text\" placeholder=\"(Optional) Charset\" class=\"form-control input-xlarge\" ng-model=\"charset\">\n" +
    "                        <p class=\"help-block\">Charset of database</p>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "            </fieldset>\n" +
    "\n" +
    "            <ag-collapse class=\"panel-default\">\n" +
    "                <collapse-header>\n" +
    "                    <h4 class=\"panel-title\">Driver Options</h4>\n" +
    "                </collapse-header>\n" +
    "\n" +
    "                <collapse-body>\n" +
    "                    <li\n" +
    "                        class=\"list-group-item\"\n" +
    "                        ng-repeat=\"(optionKey, optionValue) in driver_options\">\n" +
    "\n" +
    "                        <div class=\"form-group\">\n" +
    "                            <label>Option:</label>\n" +
    "                            <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"optionKey\">\n" +
    "                        </div>\n" +
    "\n" +
    "                        <div class=\"form-group\">\n" +
    "                            <label>Value:</label>\n" +
    "                            <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"optionValue\">\n" +
    "                        </div>\n" +
    "\n" +
    "                        <div class=\"btn-group ag-new-input pull-right\">\n" +
    "                            <button type=\"button\" class=\"btn btn-sm btn-danger\"\n" +
    "                                ng-click=\"removeNewDriverOption(driver_options, optionKey)\">Remove Option</button>\n" +
    "                        </div>\n" +
    "\n" +
    "                        <div class=\"clearfix\"></div>\n" +
    "                    </li>\n" +
    "\n" +
    "                    <li class=\"list-group-item\">\n" +
    "                        <div class=\"form-group\">\n" +
    "                            <label>Option:</label>\n" +
    "                            <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"newOptionKey\">\n" +
    "                        </div>\n" +
    "\n" +
    "                        <div class=\"form-group\">\n" +
    "                            <label>Value:</label>\n" +
    "                            <input type=\"text\" class=\"form-control input-xlarge\" ng-model=\"newOptionValue\">\n" +
    "                        </div>\n" +
    "\n" +
    "                        <div class=\"btn-group ag-new-input pull-right\">\n" +
    "                            <button type=\"button\" class=\"btn btn-sm btn-primary\"\n" +
    "                                ng-click=\"addNewDriverOption(driver_options)\">Add Option</button>\n" +
    "                        </div>\n" +
    "\n" +
    "                        <div class=\"clearfix\"></div>\n" +
    "                    </li>\n" +
    "                </collapse-body>\n" +
    "            </ag-collapse>\n" +
    "\n" +
    "            <div class=\"btn-group pull-right\">\n" +
    "                <a ng-click=\"resetForm()&&(showNewDbAdapterForm = false)\" class=\"btn btn-sm btn-default\">Cancel</a>\n" +
    "                <button type=\"submit\" class=\"btn btn-sm btn-primary\">Create DB Adapter</button>\n" +
    "            </div>\n" +
    "\n" +
    "            <div class=\"clearfix\"></div>\n" +
    "        </form>\n" +
    "    </div>\n" +
    "</div>\n" +
    "\n" +
    "<br clear=\"left\">\n" +
    "\n" +
    "");
}]);

angular.module("html/settings/db-adapters/remove.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/db-adapters/remove.html",
    "<div class=\"panel panel-danger\">\n" +
    "    <div class=\"panel-heading\"><h4 class=\"panel-title\">Remove DB Adapter</h4></div>\n" +
    "    <div class=\"panel-body\">\n" +
    "    <form ng-submit=\"removeDbAdapter(dbAdapter.adapter_name)\">\n" +
    "        <p>Are you sure you want to delete the Adapter</p>\n" +
    "\n" +
    "        <div class=\"btn-group pull-right\">\n" +
    "        <button collapse-flag flags=\"{delete: false}\" type=\"button\" class=\"btn btn-sm btn-default\">Cancel</button>\n" +
    "        <button type=\"submit\" class=\"btn btn-sm btn-danger\">Yes</button>\n" +
    "        </div>\n" +
    "\n" +
    "        <div class=\"clearfix\"></div>\n" +
    "    </form>\n" +
    "    </div>\n" +
    "</div>\n" +
    "");
}]);

angular.module("html/settings/db-adapters/view.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/db-adapters/view.html",
    "<table class=\"table table-striped\">\n" +
    "    <tr>\n" +
    "        <td>Driver</td>\n" +
    "        <td>{{ dbAdapter.driver }}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr>\n" +
    "        <td>Database</td>\n" +
    "        <td>{{ dbAdapter.database }}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr ng-show=\"dbAdapter.dsn\">\n" +
    "        <td>DSN</td>\n" +
    "        <td>{{ dbAdapter.dsn }}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr ng-show=\"dbAdapter.username\">\n" +
    "        <td>Username</td>\n" +
    "        <td>{{ dbAdapter.username }}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr ng-show=\"dbAdapter.password\">\n" +
    "        <td>Password</td>\n" +
    "        <td>{{ dbAdapter.password }}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr ng-show=\"dbAdapter.hostname\">\n" +
    "        <td>Hostname</td>\n" +
    "        <td>{{ dbAdapter.hostname }}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr ng-show=\"dbAdapter.port\">\n" +
    "        <td>Port</td>\n" +
    "        <td>{{ dbAdapter.port }}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr ng-show=\"dbAdapter.charset\">\n" +
    "        <td>Charset</td>\n" +
    "        <td>{{ dbAdapter.charset }}</td>\n" +
    "    </tr>\n" +
    "\n" +
    "    <tr ng-show=\"dbAdapter.driver_options\">\n" +
    "        <td colspan=\"2\">\n" +
    "        <ag-collapse class=\"panel-default\">\n" +
    "            <collapse-header>\n" +
    "                <h4 class=\"panel-title\">Driver Options</h4>\n" +
    "            </collapse-header>\n" +
    "\n" +
    "            <collapse-body>\n" +
    "                <table class=\"table table-striped\">\n" +
    "                    <tr ng-repeat=\"(optionKey, optionValue) in dbAdapter.driver_options\">\n" +
    "                        <td>{{ optionKey }}</td>\n" +
    "                        <td>{{ optionValue }}</td>\n" +
    "                    </tr>\n" +
    "                </table>\n" +
    "            </collapse-body>\n" +
    "        </ag-collapse>\n" +
    "        </td>\n" +
    "    </tr>\n" +
    "</table>\n" +
    "\n" +
    "");
}]);

angular.module("html/settings/sidebar.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/settings/sidebar.html",
    "<ul class=\"nav nav-pills ag-admin-nav-pills nav-stacked\">\n" +
    "    <li ng-class=\"{active: ('ag.settings.overview' | isState)}\"><a ui-sref=\"ag.settings.overview\">General Information</a></li>\n" +
    "    <li ng-class=\"{active: ('ag.settings.authentication' | isState)}\"><a ui-sref=\"ag.settings.authentication\">Authentication</a></li>\n" +
    "    <li ng-class=\"{active: ('ag.settings.content-negotiation' | isState)}\"><a ui-sref=\"ag.settings.content-negotiation\">Content Negotiation</a></li>\n" +
    "    <li ng-class=\"{active: ('ag.settings.db-adapters' | isState)}\"><a ui-sref=\"ag.settings.db-adapters\">Database Adapters</a></li>\n" +
    "</ul>\n" +
    "\n" +
    "");
}]);

angular.module("html/view-navigation.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("html/view-navigation.html",
    "<aside class=\"ag-sidebar\" role=\"complementary\">\n" +
    "  <div ng-show=\"!$root.stateParams.apiName\">\n" +
    "    <ul class=\"nav ag-sidenav\">\n" +
    "      <li ng-class=\"{active: ($root.stateParams.section == 'general-information')}\"><a ng-href=\"/#/global/information\">General Information</a></li>\n" +
    "      <li ng-class=\"{active: ($root.stateParams.section == 'db-adapters')}\"><a ng-href=\"/#/global/db-adapters\">Database Adapters</a></li>\n" +
    "    </ul>\n" +
    "  </div>\n" +
    "  <div ng-show=\"$root.stateParams.apiName\">\n" +
    "    <ul class=\"nav ag-sidenav\">\n" +
    "      <li ng-class=\"{active: ($root.stateParams.section == 'info')}\"><a ng-href=\"/#/api/{{ $root.stateParams.apiName }}/overview\">General Information</a></li>\n" +
    "      <li ng-class=\"{active: ($root.stateParams.section == 'authorization')}\"><a ng-href=\"/#/api/{{ $root.stateParams.apiName }}/authorization\">Authorization</a></li>\n" +
    "      <li ng-class=\"{active: ($root.stateParams.section == 'rest-services')}\"><a ng-href=\"/#/api/{{ $root.stateParams.apiName }}/rest-services\">REST Services</a></li>\n" +
    "      <li ng-class=\"{active: ($root.stateParams.section == 'rpc-services')}\"><a ng-href=\"/#/api/{{ $root.stateParams.apiName }}/rpc-services\">RPC Services</a></li>\n" +
    "    </ul>\n" +
    "  </div>\n" +
    "</aside>\n" +
    "");
}]);
