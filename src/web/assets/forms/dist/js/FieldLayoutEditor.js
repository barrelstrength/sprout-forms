/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

if (typeof Craft.SproutForms === typeof undefined) {
    Craft.SproutForms = {};
}

(function($) {
    /**
     * Craft.SproutForms.FieldLayoutEditor class
     * Handles the buttons for creating new groups and fields inside a the drag and drop UI
     */
    Craft.SproutForms.FieldLayoutEditor = Garnish.Base.extend({

        $container: null,
        $groupButton: null,
        $fieldButton: null,
        $settings: null,

        fld: null,
        modal: null,
        formLayout: null,
        fieldsLayout: null,
        wheelHtml: null,
        continueEditing: null,

        // The Dragula instance
        drake: null,
        // The dragula instance for tabs
        drakeTabs: null,
        tabsLayout: null,
        $saveFormButton: null,

        /**
         * The constructor.
         * @param - All the tabs of the form fieldLayout.
         */
        init: function(currentTabs, continueEditing) {
            var that = this;

            this.$saveFormButton = $("#save-form-button");

            this.continueEditing = continueEditing;

            this.initButtons();

            this.initTabSettings();

            this.wheelHtml = ' <span class="settings icon icon-wheel"></span>';

            this.modal = Craft.SproutForms.FieldModal.getInstance();

            this.modal.on('newField', $.proxy(function(e) {
                var field = e.field;
                var group = field.group;
                this.addField(field.id, field.name, group.name);
            }, this));

            this.modal.on('saveField', $.proxy(function(e) {
                var field = e.field;
                var group = field.group;
                // Let's update the name and the icon if the field is updated
                this.resetField(field, group);
            }, this));

            // DRAGULA FOR TABS
            this.tabsLayout = this.getId('sprout-forms-tabs');
            this.drakeTabs = dragula([this.tabsLayout], {
                accepts: function(el, target, source, sibling) {
                    // let's try to not allows reorder the PLUS icon
                    return sibling === null || $(el).is('.drag-tab');
                },
                invalid: function(el, handle) {
                    // do not move any item with donotdrag class.
                    return el.classList.contains('donotdrag');
                }
            })
                .on('drag', function(el) {
                    $(el).addClass('drag-tab-active');
                })
                .on('drop', function(el, target, source) {
                    $(el).removeClass('drag-tab-active');
                    $(target).find('.drag-tab-active').removeClass('drag-tab-active');
                    $(source).find('.drag-tab-active').removeClass('drag-tab-active');
                    // Reorder fields
                    if ($(target).attr("id") === $(source).attr("id")) {
                        // lets update the hidden tab field to reorder the tabs
                        $("#sprout-forms-tabs li.drag-tab a").each(function(i) {
                            var tabId = $(this).attr('id');
                            var mainDiv = $("#sproutforms-fieldlayout-container");

                            if (tabId) {
                                var currentTab = $("#sproutforms-" + tabId);
                                if (currentTab) {
                                    mainDiv.append(currentTab);
                                }
                            }
                        });
                    }
                });

            // Show the wheel on hover
            /*$('#sprout-forms-tabs').find('a').hover( function() {
                $(this).find('#icon-wheel').toggle();
            } );
            */
            // DRAGULA
            this.fieldsLayout = this.getId('right-copy');

            // Drag from right to left
            this.drake = dragula([null, this.fieldsLayout], {
                copy: function(el, source) {
                    return source === that.fieldsLayout;
                },
                accepts: function(el, target) {
                    return target !== that.fieldsLayout;
                },
                invalid: function(el, handle) {
                    // do not move any item with donotdrag class.
                    return el.classList.contains('donotdrag');
                }
            })
                .on('drag', function(el) {
                    $(el).addClass('drag-active');
                })
                .on('drop', function(el, target, source) {
                    $(el).removeClass('drag-active');
                    $(target).find('.drag-active').removeClass('drag-active');
                    $(source).find('.drag-active').removeClass('drag-active');

                    // Reorder fields
                    if ($(target).attr("id") === $(source).attr("id")) {
                        // just if we need check when the field is reorder
                        // not needed because the order is saved from the hidden field
                        // when the form is saved
                    }
                    if (target && source === that.fieldsLayout) {
                        // get the tab name by the first div fields
                        var tab = $(el).closest(".sproutforms-tab-fields");
                        var tabName = tab.data('tabname');
                        var tabId = tab.data('tabid');
                        var fieldType = $(el).data("type");

                        that.createDefaultField(fieldType, tabId, tabName, el);
                    }
                })
                .on('over', function(el, container) {
                    $(el).addClass('drag-active');
                    $(container).addClass('container-active');
                })
                .on('out', function(el, container) {
                    $(el).removeClass('drag-active');
                    $(container).removeClass('container-active');
                });

            // Adds auto-scroll to main container when dragging
            var scroll = autoScroll(
                [
                    document.querySelector('#content-container')
                ],
                {
                    margin: 20,
                    maxSpeed: 10,
                    scrollWhenOutside: true,
                    autoScroll: function() {
                        //Only scroll when the pointer is down, and there is a child being dragged.
                        return this.down && that.drake.dragging;
                    }
                }
            );

            // Adds auto-scroll to main container when dragging
            var tabScroll = autoScroll(
                [
                    document.querySelector('#sprout-forms-tabs')
                ],
                {
                    margin: 20,
                    maxSpeed: 10,
                    scrollWhenOutside: true,
                    autoScroll: function() {
                        //Only scroll when the pointer is down, and there is a child being dragged.
                        return this.down && that.drakeTabs.dragging;
                    }
                }
            );

            // Add the drop containers for each tab
            for (var i = 0; i < currentTabs.length; i++) {
                this.drake.containers.push(this.getId('sproutforms-tab-container-' + currentTabs[i].id));
            }
            // Prevent save with Ctrl+S when the the field is dropped
            /*$(document).bind('keydown', function(e) {
                if(e.ctrlKey && (e.which == 83)) {
                    if (!that.$saveFormButton.removeClass('disabled').siblings('.spinner').hasClass("hidden")){
                        e.preventDefault();
                        e.stopPropagation();
                        // Not working
                        return false;
                    }
                }
            });*/
        },

        clickHandler: function(e) {
            var target = e.target;
            if (target === this.tabsLayout) {
                return;
            }
            target.innerHTML += ' [click!]';

            setTimeout(function() {
                target.innerHTML = target.innerHTML.replace(/ \[click!\]/g, '');
            }, 500);
        },

        createDefaultField: function(type, tabId, tabName, el) {
            var that = this;
            //this.$saveFormButton.addClass('disabled').siblings('.spinner').removeClass('hidden');

            $(el).removeClass('source-field');
            $(el).addClass('target-field');
            $(el).find('.source-field-header').remove();
            $(el).find('.body').removeClass('hidden');
            // try to check position of the field
            var nextDiv = $(el).next(".target-field");
            var nextId = nextDiv.attr('id');
            if (typeof nextId === 'undefined' || nextId === null) {
                nextDiv = null;
            }else{
                // Last field
                var nextDivId = nextId.split('-');
                nextId = nextDivId[1];
            }

            var defaultName = $(el).data('defaultname') ? $(el).data('defaultname') : Craft.t('sprout-forms', 'Untitled');

            // Add the Field Header
            $(el).prepend($([
                '<div class="active-field-header">',
                '<h2>', defaultName, '</h2>',
                '</div>'
            ].join('')));

            var formId = $("#formId").val();
            var data = {
                'type': type,
                'formId': formId,
                'tabId': tabId,
                'nextId': nextId
            };

            Craft.postActionRequest('sprout-forms/fields/create-field', data, $.proxy(function(response, textStatus) {
                if (textStatus === 'success') {
                    this.initFieldOnDrop(response.field, tabName, el);
                    //that.$saveFormButton.removeClass('disabled').siblings('.spinner').addClass('hidden');
                }
            }, this));
        },

        initFieldOnDrop: function(defaultField, tabName, el) {
            if (defaultField !== null && defaultField.hasOwnProperty("id")) {
                $(el).attr('id', 'sproutfield-' + defaultField.id);

                // Add the Settings buttons
                $(el).prepend($([
                    '<ul class="settings">',
                    '<li><a id="sproutform-field-', defaultField.id, '" data-fieldid="', defaultField.id, '" href="#" tabindex="0" ><i class="fa fa-pencil fa-2x" title="', Craft.t('sprout-forms', 'Edit'), '"></i></a></li>',
                    '</ul>'
                ].join('')));

                // Add fieldLayout input
                $(el).append($([
                        '<input type="hidden" name="fieldLayout[', tabName, '][]" value="', defaultField.id, '" class="id-input">'
                    ].join('')
                ));

                this.addListener($("#sproutform-field-" + defaultField.id), 'activate', 'editField');
            }
            else {
                Craft.cp.displayError(Craft.t('sprout-forms', 'Something went wrong when creating the field :('));

                $(el).remove();
            }
        },

        getId: function(id) {
            return document.getElementById(id);
        },

        /**
         * Adds edit buttons to existing fields.
         */
        initTabSettings: function() {
            var that = this;

            $("#sprout-forms-tabs li").each(function(i, el) {

                // #delete-tab-"+tab.id
                var tabId = $(el).find('a').attr('id');

                var $editBtn = $(el).find('.settings');
                that.initializeWheel($editBtn, tabId);
            });
        },

        initializeWheel: function($editBtn, tabId) {
            var that = this;
            var $menu = $('<div class="menu" data-align="center"/>').insertAfter($editBtn),
                $ul = $('<ul/>').appendTo($menu);

            $('<li><a data-action="add" data-tab-id="' + tabId + '">' + Craft.t('app', 'Add Tab') + '</a></li>').appendTo($ul);
            $('<li><a data-action="rename" data-tab-id="' + tabId + '">' + Craft.t('app', 'Rename') + '</a></li>').appendTo($ul);
            $('<li><a id ="#delete-' + tabId + '" data-action="delete" data-tab-id="' + tabId + '">' + Craft.t('app', 'Delete') + '</a></li>').appendTo($ul);

            new Garnish.MenuBtn($editBtn, {
                onOptionSelect: $.proxy(that, 'onTabOptionSelect')
            });
        },

        onTabOptionSelect: function(option) {
            var $option = $(option),
                tabId = $option.data('tab-id'),
                action = $option.data('action');

            switch (action) {
                case 'add': {
                    this.addNewTab();
                    break;
                }
                case 'rename': {
                    this.renameTab(tabId);
                    break;
                }
                case 'delete': {
                    this.deleteTab(tabId);
                    break;
                }
            }
        },

        /**
         * Adds edit buttons to existing fields.
         */
        initButtons: function() {
            var that = this;

            // Add listeners to all the items that start with sproutform-field-
            $("a[id^='sproutform-field-']").each(function(i, el) {
                var fieldId = $(el).data('fieldid');

                if (fieldId) {
                    that.addListener($("#sproutform-field-" + fieldId), 'activate', 'editField');
                }
            });

            // get all the delete buttons
            $("a[id^='delete-tab-']").each(function(i, el) {
                var tabId = $(el).data('tabid');

                if (tabId) {
                    that.addListener($("#delete-tab-" + tabId), 'activate', 'deleteTab');
                }
            });
        },

        deleteTab: function(tabId) {
            var userResponse = this.confirmDeleteTab();

            if (userResponse) {

                var data = {
                    tabId: tabId
                };

                Craft.postActionRequest('sprout-forms/fields/delete-tab', data, $.proxy(function(response, textStatus) {
                    if (response.success) {
                        Craft.cp.displayNotice(Craft.t('sprout-forms', 'Tab Deleted'));
                        $("#sproutforms-" + tabId).slideUp(500, function() {
                            $(this).remove();
                        });
                        $("#" + tabId).closest("li").slideUp(500, function() {
                            $(this).remove();
                        });
                    }
                    else {
                        Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to delete the tab'));
                    }
                }, this));


            }
        },

        renameTab: function(tabId) {
            var $labelSpan = $('#' + tabId + ' .tab-label'),
                oldName = $labelSpan.text().trim(),
                newName = prompt(Craft.t('app', 'Give your tab a name.'), oldName);
            var response = true;
            var $tabs = $(".drag-tab");
            var formId = $("#formId").val();
            var that = this;

            if (newName && newName !== oldName) {
                // validate with current names and set the sortOrder
                $tabs.each(function(i, el) {
                    var tabname = $(el).find('.tab-label').text();

                    if (tabname === newName) {
                        response = false;
                        return false;
                    }
                });

                if (response && newName && formId) {
                    var data = {
                        name: newName,
                        oldName: oldName,
                        formId: formId
                    };

                    Craft.postActionRequest('sprout-forms/fields/rename-tab', data, $.proxy(function(response, textStatus) {
                        if (response.success) {
                            Craft.cp.displayNotice(Craft.t('sprout-forms', 'Tab renamed'));

                            // Rename all the field names
                            var $fields = $("[name^='fieldLayout[" + oldName + "]']");

                            $fields.each(function(i, el) {
                                var fieldName = $(el).attr('name');
                                var newFieldName = fieldName.replace(oldName, newName);
                                $(el).attr('name', newFieldName);
                                $labelSpan.text(newName);
                            });
                            $("#sproutforms-" + tabId).attr('data-tabname', newName);
                        }
                        else {
                            Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to rename tab'));
                        }
                    }, this));

                }
                else {
                    Craft.cp.displayError(Craft.t('sprout-forms', 'Invalid tab name'));
                }
            }
        },

        addNewTab: function() {
            var newName = this.promptForGroupName('');
            var response = true;
            var $tabs = $(".drag-tab");
            var formId = $("#formId").val();
            var that = this;
            // validate with current names and set the sortOrder
            $tabs.each(function(i, el) {
                var tabname = $(el).find('.tab-label').text();

                if (tabname) {
                    if (tabname === newName) {
                        response = false;
                        return false;
                    }
                }
            });

            if (response && newName && formId) {
                var data = {
                    name: newName,
                    // Minus the add tab button
                    sortOrder: $tabs.length,
                    formId: formId
                };

                Craft.postActionRequest('sprout-forms/fields/add-tab', data, $.proxy(function(response, textStatus) {
                    if (response.success) {
                        var tab = response.tab;

                        Craft.cp.displayNotice(Craft.t('sprout-forms', 'Tab: ' + tab.name + ' created'));

                        // Insert the new tab before the Add Tab button
                        var href = '#sproutforms-tab-' + tab.id;
                        $("#sprout-forms-tabs").append('<li class="drag-tab"><a id="tab-' + tab.id + '" class="tab" href="' + href + '" tabindex="0"><span class="tab-label">' + tab.name + '</span>&nbsp;' + this.wheelHtml + '</a></li>');
                        var $editBtn = $("#tab-" + tab.id).find('.settings');
                        // add listener to the wheel
                        that.initializeWheel($editBtn, 'tab-' + tab.id);

                        // Create the area to Drag/Drop fields on the new tab
                        $("#sproutforms-fieldlayout-container").append($([
                            '<div id="sproutforms-tab-' + tab.id + '" data-tabname="' + tab.name + '" data-tabid="' + tab.id + '" class="sproutforms-tab-fields hidden">',
                            '<div class="parent">',
                            '<div id="sproutforms-tab-container-' + tab.id + '" class="sprout-tab-container">',
                            '</div>',
                            '</div>',
                            '</div>'
                        ].join('')));

                        // Convert our new tab into Dragula vampire :)
                        this.drake.containers.push(this.getId('sproutforms-tab-container-' + tab.id));

                        // Reinitialize tabs
                        Craft.cp.initTabs();
                    }
                    else {
                        Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to create a new tab'));
                    }
                }, this));

            }
            else {
                Craft.cp.displayError(Craft.t('sprout-forms', 'Invalid tab name'));
            }

        },

        promptForGroupName: function(oldName) {
            return prompt(Craft.t('sprout-forms', 'What do you want to name your new tab?'), oldName);
        },

        confirmDeleteTab: function() {
            return confirm("Are you sure you want to delete this tab, all of it's fields, and all of it's data?");
        },

        /**
         * Event handler for the New Field button.
         * Creates a modal window that contains new field settings.
         */
        newField: function() {
            this.modal.show();
        },

        editField: function(option) {
            var option = option.currentTarget;

            var fieldId = $(option).data('fieldid');
            // Make our field available to our parent function
            this.$field = $(option);
            this.base($(option));

            this.modal.editField(fieldId);
        },

        /**
         * Renames | update icon | move field to another tab
         * of an existing field after edit it
         *
         * @param field
         * @param group
         */
        resetField: function(field, group) {

            var $fieldDiv = $("#sproutfield-" + field.id);

            // Lets update the the name and icon - (new) update if required
            $($fieldDiv).find('.body').html(field.htmlExample);
            var $requiredDiv = $($fieldDiv).find("[name='requiredFields[]']");

            if (field.required) {
                $($fieldDiv).find('.active-field-header h2').addClass('required');

                // Update or create our hidden required div
                if(!$requiredDiv.length)
                {
                    $('<input type="hidden" name="requiredFields[]" value="' + field.id + '" class="sproutforms-required-input">').appendTo($fieldDiv);
                }
                else
                {
                    $($requiredDiv).val(field.id);
                }
            }
            else {
                $($fieldDiv).find('.active-field-header h2').removeClass('required');

                // Update our hidden required div
                $($requiredDiv).val('');
            }

            $($fieldDiv).find('.active-field-header h2').html(field.name);
            $($fieldDiv).find('.active-field-header p').html(field.instructions);

            // Check if we need move the field to another tab
            var tab = $($fieldDiv).closest(".sproutforms-tab-fields");
            var tabName = tab.data('tabname');
            var tabId = tab.data('tabid');
            
            if (tabName !== group.name) {
                // let's remove the hidden field just if the user change the tab
                $($fieldDiv).find('.id-input').remove();

                // create the new hidden field and add it to the field div
                var $field = $([
                    '<input class="id-input" type="hidden" name="fieldLayout[', group.name, '][]" value="', field.id, '">'
                ].join('')).appendTo($($fieldDiv));

                // move the field to another tab
                var newTab = $("#sproutforms-tab-container-" + group.id);

                // move element to new div - like ctrl+x
                $($fieldDiv).detach().appendTo($(newTab));
            }
        }
    });

})(jQuery);
