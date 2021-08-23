(
        function (jQuery, scope) {
            var generator = 'ConLite';
            var Con;
            // var $ = jQuery.noConflict();
            var $ = jQuery;

            scope.Con = scope.Con || {
                Plugin: {},
                cfg: {},
                sid: 0
            };
            Con = scope.Con;
            Con.$ = jQuery;
            Con.cfg.enableLog = true;

            Con.namespace = function (namespace) {
                var ns = namespace.split('.'),
                        o = scope, i;
                for (i = 0; i < ns.length; i++) {
                    o[ns[i]] = o[ns[i]] || {};
                    o = o[ns[i]];
                }
                return o;
            };

            Con.getFrame = function (name) {
                try {
                    // Contenido's file and image browser
                    if ("undefined" === typeof (scope.top.content)) {
                        switch (name) {
                            case 'header':
                                return scope.header;
                            case 'content':
                                return scope.contentFrame;
                            case 'left':
                                return scope.top.left;
                            case 'left_deco':
                                return scope.top.left.left_deco;
                            case 'left_top':
                                return scope.top.left.left_top;
                            case 'left_bottom':
                                return scope.top.left.left_bottom;
                            case 'right':
                                return scope.top.right;
                            case 'right_top':
                                return scope.top.right.right_top;
                            case 'right_bottom':
                                return scope.top.right.right_bottom;
                        }
                    }
                    // everywhere else
                    switch (name) {
                        case 'header':
                            return scope.top.header;
                        case 'content':
                            return scope.top.content;
                        case 'left':
                            return scope.top.content.left;
                        case 'left_deco':
                            return scope.top.content.left.left_deco;
                        case 'left_top':
                            return scope.top.content.left.left_top;
                        case 'left_bottom':
                            return scope.top.content.left.left_bottom;
                        case 'right':
                            return scope.top.content.right;
                        case 'right_top':
                            if ('undefined' !== $.type(scope.top.content.right)) {
                                return scope.top.content.right.right_top;
                            } else {
                                return scope.top.content.right_top;
                            }
                        case 'right_bottom':
                            if ('undefined' !== typeof (scope.top.content.right)) {
                                return scope.top.content.right.right_bottom;
                            } else {
                                return scope.top.content.right_bottom;
                            }
                    }
                } catch (e) {
                    Con.log(["getFrame: Couldn't get frame " + name, e], generator, 'warn');
                    return null;
                }
            };


            Con.log = function (mixedVar, source, severity) {
                severity = severity || 'log';

                if (!Con.cfg.enableLog) {
                    return;
                } else if (-1 === $.inArray(severity, ['log', 'info', 'warn', 'error'])) {
                    return;
                }

                if (scope.console && 'function' === typeof scope.console[severity]) {
                    var msg = severity.toUpperCase() + ': ' + source + ': ';
                    scope.console[severity](msg, mixedVar);
                }
            };

            // Console emulation, to prevent errors if console is not available
            if (!('console' in scope)) {
                (function () {
                    scope.console = {
                        log: function () {
                        },
                        debug: function () {
                        },
                        info: function () {
                        },
                        warn: function () {
                        },
                        error: function () {
                        }
                    };
                })();
            }
        }
)(jQuery, window);