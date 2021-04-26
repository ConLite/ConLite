
// Ignore console on platforms where it is not available
if (typeof (window.console) == "undefined") {
    console = {};
    console.log = console.warn = console.error = function (a) {
    };
}

function showMessage(Msg, Level) {
    var msgClass = "";
    switch (Level) {
        case 'info':
            msgClass = "info";
            break;
        case 'warning':
            msgClass = "warning";
            break;
        case 'error':
            msgClass = "error";
            break;
        case 'success':
            msgClass = "success";
            break;
        default:
            msgClass = "info";
    }
    if ($("#pimmsg").length == 0) {
        $("body").append('<div id="pimmsg" class="' + msgClass + '">' + Msg + '</div>');
    } else {
        $("#pimmsg").attr('class', msgClass).html(Msg);
    }

    $("#pimmsg").plainModal(
            'open',
            {
                duration: 500,
                offset: {
                    left: 20,
                    top: 10
                },
                overlay: {
                    fillColor: '#fff',
                    opacity: 0.5
                }
            });

    setTimeout(function () {
        $("#pimmsg").plainModal('close');
    }, 3000);
}

function togglePluginInfo(tableId) {
    var collapseButton = 'images/close_all.gif';
    var expandButton = 'images/open_all.gif';
    var curDiv = document.getElementById(tableId);
    var curButton = document.getElementById(tableId + '_img');

    if (curDiv.style.display == "table-row" || curDiv.style.display == "" || curDiv.style.display == "block") {
        curDiv.style.display = "none";
        curButton.src = expandButton;
    } else if (curDiv.style.display == "none") {
        if (ie == 7) {
            $('#' + tableId).css('display', 'block');
        } else {
            curDiv.style.display = "table-row";
        }
        curButton.src = collapseButton;
    }
}

// Read a page's GET URL variables and return them as an associative array.
function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        if (hash[0] == "contenido" || hash[0] == "plugin_action" || hash[0] == "plugin_id" || hash[0] == "delete_sql") {
            continue;
        }
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    console.log(vars);
    return vars;
}


$(function () {

    if ($("ul#pim_messages").length) {
        var tmp = $("ul#pim_messages li").html();
        var message = tmp.split(':');
        showMessage(message[1], message[0]);
        $("ul#pim_messages").remove();
    }

    $(document)
            .ajaxStart(function () {
                $('body').plainOverlay('show');
            })
            .ajaxStop(function () {
                $('body').plainOverlay('hide');
            });

    // add custom event before start to ui sortable
    var oldMouseStart = $.ui.sortable.prototype._mouseStart;
    $.ui.sortable.prototype._mouseStart = function (event, overrideHandle, noActivation) {
        this._trigger("CustomBeforeStart", event, this._uiHash());
        oldMouseStart.apply(this, [event, overrideHandle, noActivation]);
    };

    $("#pimPluginsExtracted").sortable({
        connectWith: "#pimPluginsInstalled",
        cursor: "move",
        opacity: 0.5,
        placeholder: "ui-state-highlight",
        forceHelperSize: true,
        forcePlaceholderSize: true,
        CustomBeforeStart: function (event, ui) {
            console.log(ui.item);
            if (ui.item.find("div.pimInfo").is(":visible")) {
                togglePluginInfo(ui.item.attr('id').replace("_", "-"));
            }
        }
    });
    $("#pimPluginsInstalled").sortable({
        placeholder: "ui-state-highlight",
        axis: "y",
        containment: "parent",
        forcePlaceholderSize: true,
        CustomBeforeStart: function (event, ui) {
            console.log(ui.item);
            if (ui.item.find("div.pimInfo").is(":visible")) {
                togglePluginInfo(ui.item.attr('id').replace("_", "-"));
            }
        },
        update: function (event, ui) {
            console.log({plugins: $("#pimPluginsInstalled").sortable("serialize")});
            $.post("ajaxmain.php", {
                plugins: $("#pimPluginsInstalled").sortable("serialize"),
                ajax: 'plugin_request',
                plugin: 'pluginmanager',
                plugin_ajax_action: 'pim_save_sort',
                contenido: cSessionId
            });
        },
        receive: function (event, ui) {
            console.log(ui.item);
            $.ajax({
                type: "POST",
                url: "ajaxmain.php",
                data: {
                    plugin_folder: ui.item.data('plugin-foldername'),
                    new_position: ui.item.index(),
                    ajax: 'plugin_request',
                    plugin: 'pluginmanager',
                    plugin_ajax_action: 'pim_install',
                    contenido: cSessionId
                },
                beforeSend: function (xhr, obj) {
                    //alert("Before");
                },
                success: function (data, textStatus, xhr) {
                    console.log(data);
                    var answer = data.split(":");
                    console.log(answer);
                    if (answer[0] == "Ok") {
                        $.ajax({
                            type: "POST",
                            url: "ajaxmain.php",
                            data: {
                                ajax: 'plugin_request',
                                plugin: 'pluginmanager',
                                plugin_ajax_action: 'pim_get_info_installed',
                                plugin_id: answer[1],
                                contenido: cSessionId
                            }
                        }).done(function (data) {
                            console.log(data);
                            $(ui.item).replaceWith(data);
                        });
                        showMessage(answer[2], 'info');
                    } else if (answer[0] == "Error") {
                        console.log("Remove New List Item.");
                        ui.sender.sortable("cancel"); // send back entry to sender :)
                        showMessage(answer[2], 'error');
                    } else {
                        //window.location.replace("index.php"); // redirect to index if answer not correct or not set
                    }
                    $("span#plugin_count").html($("#pimPluginsInstalled").children().length);
                },
                error: function (xhr, textStatus, errorThrown) {
                    showMessage(textStatus, 'error');
                    console.log('a' + textStatus);
                }
            });
            return true;
        }
    });
    $("#pimPluginsInstalled li, #pimPluginsExtracted li").disableSelection();
    // actions for buttons in plugin info
    var labelID;
    $('label.pimButLabel').click(function (e) {
        if ($(e.target).is('input')) {
            return;
        }
        labelID = $(this).attr('for');
        $('#' + labelID).trigger('click');
    });
    $("ul#pimPluginsInstalled").on("click", "input.pimImgBut", function () {
        var pluginID = $(this).attr('id');
        var thisInput = $(this);
        switch ($(this).attr('name')) {
            case "toggle_active":
                $('body').plainOverlay('show');
                $.ajax({
                    type: "POST",
                    url: "ajaxmain.php",
                    data: {
                        ajax: 'plugin_request',
                        plugin: 'pluginmanager',
                        plugin_ajax_action: 'toggle_active',
                        plugin_id: pluginID.split('-')[3],
                        contenido: cSessionId
                    },
                    success: function (data, textStatus, xhr) {
                        console.log(data);
                        var aData = data.split(":");
                        console.log(aData);
                        if (aData[0] == "Ok") {
                            if (aData[1] == '1') {
                                thisInput.attr('src', 'images/online.gif');
                            } else {
                                thisInput.attr('src', 'images/offline.gif');
                            }
                            $("label[for=" + pluginID + "]").html(aData[2]);
                        }
                    }
                });
                break;
            case "uninstall_plugin":
                $('body').plainOverlay('show');
                var hiddenFields = [];
                hiddenFields.push("plugin_id");
                hiddenFields["plugin_id"] = pluginID.split('-')[3];
                hiddenFields.push("plugin_action");
                hiddenFields["plugin_action"] = "uninstall_plugin";
                /*
                 hiddenFields.push("contenido");
                 hiddenFields["contenido"] = cSessionId;                */
                hiddenFields.push("delete_sql");
                console.log($(this).parent().children("label:eq(1)").children("input"));
                if ($(this).parent().children("label:eq(1)").children("input").prop('checked') == true) {
                    hiddenFields["delete_sql"] = "delete";
                } else {
                    hiddenFields["delete_sql"] = "hold";
                }
                $("#pim_uninstall").remove();
                var form = document.createElement('form');
                form.id = "pim_uninstall";
                form.method = 'post';
                $.each(hiddenFields, function (index, name) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = hiddenFields[name];
                    form.appendChild(input);
                });
                form.action = window.location.protocol + '//' + window.location.hostname + window.location.pathname + window.location.search;
                document.body.appendChild(form);
                form.submit();
                break;
        }
    });
});