/**
 * DataTree: Interaction of jsTree with Nette framework.
 *
 * @copyright Copyright (c) 2016 Tomas Rathouz
 * @version 0.0.1
 */

(function (window, $) {

    if (typeof $.jstree === 'undefined') {
        return console.error('datatree.js: jsTree is missing, load it please');
    }

    var datatree = function (id) {
        
        var treeId = id;
        
        this.prefixParameters = function (parameters, prefix, joinPrefix) {
            var prefixedParameters = {};
            var buildJoinPrefix = joinPrefix + '_';
            if (typeof joinPrefix == 'undefined') {
                buildJoinPrefix = '';
            }
            $.each(parameters, function(key, value) {
                prefixedParameters[prefix + '-' + buildJoinPrefix + key] = value;
            });
            return prefixedParameters;
        },

        this.fireCallback = function (url, parameters, controlName, joinTree) {
            var parameters = this.prefixParameters(parameters, controlName);
            
            if (joinTree != 'null') {
                var joinedTreeParameters = this.getJoinedTreeParameters(joinTree);
                var prefixedJoinedTreeParameters = this.prefixParameters(joinedTreeParameters, controlName, joinTree);
                $.each(prefixedJoinedTreeParameters, function (key, value) {
                    parameters[key] = value;
                });
            }
            
            var callback = $.post(url, parameters);
            callback.always(function (response) {
                if (typeof response.snippets == 'object') {
                    // Nette snippets
                    $.nette.ext('snippets').updateSnippets(response.snippets);
                    $.nette.load();
                }
                
                if (typeof response.selectedNodes == 'object') {
                    $('#' + treeId).jstree(true).select_node(response.selectedNodes);
                }
            });
            return callback;
        },

        this.getJoinedTreeParameters = function (joinedTreeId) {
            var parameters = {};
            parameters.selectedNodes = $('#' + joinedTreeId).jstree(true).get_selected();
            return parameters;
        }
        
    };

    $.datatree = datatree;

})(window, window.jQuery);
