// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/** global: M */
/** global: Y */
/** global: YUI */
/** global: event */

M.mod_bigbluebuttonbn = M.mod_bigbluebuttonbn || {};

M.mod_bigbluebuttonbn.recordings = {

    datasource: null,
    locale: 'en',
    datatable: {},

    /**
     * Initialise recordings code.
     *
     * @method init
     * @param {object} data
     */
    init: function(data) {
        this.datasource = new Y.DataSource.Get({
            source: M.cfg.wwwroot + "/mod/bigbluebuttonbn/bbb_broker.php?"
        });
        if (data.recordings_html === false &&
            (data.profile_features.indexOf('all') != -1 || data.profile_features.indexOf('showrecordings') != -1)) {
            this.locale = data.locale;
            this.datatable.columns = data.columns;
            this.datatable.data = this.datatableInitFormatDates(data.data);
            this.datatableInit();
        }
        M.mod_bigbluebuttonbn.helpers.init();
    },

    datatableInitFormatDates: function(data) {
        for (var i = 0; i < data.length; i++) {
            var date = new Date(data[i].date);
            data[i].date = date.toLocaleDateString(this.locale, {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
        return data;
    },

    datatableInit: function() {
        var columns = this.datatable.columns;
        var data = this.datatable.data;
        YUI({
            lang: this.locale
        }).use('datatable', 'datatable-sort', 'datatable-paginator', 'datatype-number', function(Y) {
            var table = new Y.DataTable({
                width: "1195px",
                columns: columns,
                data: data,
                rowsPerPage: 10,
                paginatorLocation: ['header', 'footer']
            }).render('#bigbluebuttonbn_recordings_table');
            return table;
        });
    },

    recordingElementPayload: function(element) {
        var nodeelement = Y.one(element);
        var node = nodeelement.ancestor('div');
        return {
            action: nodeelement.getAttribute('data-action'),
            recordingid: node.getAttribute('data-recordingid'),
            meetingid: node.getAttribute('data-meetingid')
        };
    },

    recordingAction: function(element, confirmation, extras) {
        var payload = this.recordingElementPayload(element);
        for (var attrname in extras) {
            payload[attrname] = extras[attrname];
        }
        // The action doesn't require confirmation.
        if (!confirmation) {
            this.recordingActionPerform(payload);
            return;
        }
        // Create the confirmation dialogue.
        var confirm = new M.core.confirm({
            modal: true,
            centered: true,
            question: this.recordingConfirmationMessage(payload)
        });
        // If it is confirmed.
        confirm.on('complete-yes', function() {
            this.recordingActionPerform(payload);
        }, this);
    },

    recordingActionPerform: function(data) {
        M.mod_bigbluebuttonbn.helpers.toggleSpinningWheelOn(data);
        M.mod_bigbluebuttonbn.broker.recordingActionPerform(data);
    },

    recordingPublish: function(element) {
        var extras = {
            source: 'published',
            goalstate: 'true'
        };
        this.recordingAction(element, false, extras);
    },

    recordingUnpublish: function(element) {
        var extras = {
            source: 'published',
            goalstate: 'false'
        };
        this.recordingAction(element, false, extras);
    },

    recordingProtect: function(element) {
        var extras = {
            source: 'protected',
            goalstate: 'true'
        };
        this.recordingAction(element, false, extras);
    },

    recordingUnprotect: function(element) {
        var extras = {
            source: 'protected',
            goalstate: 'false'
        };
        this.recordingAction(element, false, extras);
    },

    recordingDelete: function(element) {
        var extras = {
            source: 'found',
            goalstate: false
        };
        var requireConfirmation = true;
        if (this.recordingIsImported(element)) {
            // When recordingDelete is performed on imported recordings use default response for validation.
            requireConfirmation = false;
            extras.source = 'status';
            extras.goalstate = true;
            extras.attempts = 1;
        }
        this.recordingAction(element, requireConfirmation, extras);
    },

    recordingImport: function(element) {
        var extras = {};
        this.recordingAction(element, true, extras);
    },

    recordingUpdate: function(element) {
        var nodeelement = Y.one(element);
        var node = nodeelement.ancestor('div');
        var extras = {
            target: node.getAttribute('data-target'),
            source: node.getAttribute('data-source'),
            goalstate: nodeelement.getAttribute('data-goalstate')
        };
        this.recordingAction(element, false, extras);
    },

    recordingEdit: function(element) {
        var link = Y.one(element);
        var node = link.ancestor('div');
        var text = node.one('> span');
        text.hide();
        link.hide();
        var inputtext = Y.Node.create('<input type="text" class="form-control"></input>');
        inputtext.setAttribute('id', link.getAttribute('id'));
        inputtext.setAttribute('value', text.getHTML());
        inputtext.setAttribute('data-value', text.getHTML());
        inputtext.on('keydown', M.mod_bigbluebuttonbn.recordings.recordingEditKeydown);
        inputtext.on('focusout', M.mod_bigbluebuttonbn.recordings.recordingEditOnfocusout);
        node.append(inputtext);
        inputtext.focus().select();
    },

    recordingEditKeydown: function(event) {
        var keyCode = event.which || event.keyCode;
        if (keyCode == 13) {
            M.mod_bigbluebuttonbn.recordings.recordingEditPerform(event.currentTarget);
            return;
        }
        if (keyCode == 27) {
            M.mod_bigbluebuttonbn.recordings.recordingEditOnfocusout(event.currentTarget);
        }
    },

    recordingEditOnfocusout: function(nodeelement) {
        var node = nodeelement.ancestor('div');
        nodeelement.hide();
        node.one('> span').show();
        node.one('> a').show();
    },

    recordingEditPerform: function(nodeelement) {
        var node = nodeelement.ancestor('div');
        var text = nodeelement.get('value');
        // Perform the update.
        nodeelement.setAttribute('data-action', 'edit');
        nodeelement.setAttribute('data-goalstate', text);
        nodeelement.hide();
        this.recordingUpdate(nodeelement.getDOMNode());
        node.one('> span').setHTML(text).show();
        node.one('> a').show();
    },

    recordingEditCompletion: function(data, failed) {
        var elementid = M.mod_bigbluebuttonbn.helpers.elementId(data.action, data.target);
        var link = Y.one('a#' + elementid + '-' + data.recordingid);
        var node = link.ancestor('div');
        var text = node.one('> span');
        if (typeof text === 'undefined') {
            return;
        }
        var inputtext = node.one('> input');
        if (failed) {
            text.setHTML(inputtext.getAttribute('data-value'));
        }
        inputtext.remove();
    },

    recordingPlay: function(element) {
        var nodeelement = Y.one(element);
        var extras = {
            target: nodeelement.getAttribute('data-target'),
            source: 'published',
            goalstate: 'true',
            attempts: 1,
            dataset: nodeelement.getData()
        };
        this.recordingAction(element, false, extras);
    },

    recordingConfirmationMessage: function(data) {
        var confirmation, recordingType, elementid, associatedLinks, confirmationWarning;
        confirmation = M.util.get_string('view_recording_' + data.action + '_confirmation', 'bigbluebuttonbn');
        if (typeof confirmation === 'undefined') {
            return '';
        }
        recordingType = M.util.get_string('view_recording', 'bigbluebuttonbn');
        if (Y.one('#playbacks-' + data.recordingid).get('dataset').imported === 'true') {
            recordingType = M.util.get_string('view_recording_link', 'bigbluebuttonbn');
        }
        confirmation = confirmation.replace("{$a}", recordingType);
        if (data.action === 'import') {
            return confirmation;
        }
        // If it has associated links imported in a different course/activity, show that in confirmation dialog.
        elementid = M.mod_bigbluebuttonbn.helpers.elementId(data.action, data.target);
        associatedLinks = Y.one('a#' + elementid + '-' + data.recordingid).get('dataset').links;
        if (associatedLinks === 0) {
            return confirmation;
        }
        confirmationWarning = M.util.get_string('view_recording_' + data.action + '_confirmation_warning_p',
            'bigbluebuttonbn');
        if (associatedLinks == 1) {
            confirmationWarning = M.util.get_string('view_recording_' + data.action + '_confirmation_warning_s',
                'bigbluebuttonbn');
        }
        confirmationWarning = confirmationWarning.replace("{$a}", associatedLinks) + '. ';
        return confirmationWarning + '\n\n' + confirmation;
    },

    recordingActionCompletion: function(data) {
        var container, table, row;
        if (data.action == 'delete') {
            row = Y.one('div#recording-actionbar-' + data.recordingid).ancestor('td').ancestor('tr');
            table = row.ancestor('tbody');
            if (table.all('tr').size() == 1) {
                container = Y.one('#bigbluebuttonbn_view_recordings_content');
                container.prepend('<span>' + M.util.get_string('view_message_norecordings', 'bigbluebuttonbn') + '</span>');
                container.one('#bigbluebuttonbn_recordings_table').remove();
                return;
            }
            row.remove();
            return;
        }
        if (data.action == 'import') {
            row = Y.one('div#recording-actionbar-' + data.recordingid).ancestor('td').ancestor('tr');
            row.remove();
            return;
        }
        if (data.action == 'play') {
            M.mod_bigbluebuttonbn.helpers.toggleSpinningWheelOff(data);
            window.open(data.dataset.href, "_self");
            return;
        }
        M.mod_bigbluebuttonbn.helpers.updateData(data);
        M.mod_bigbluebuttonbn.helpers.toggleSpinningWheelOff(data);
        M.mod_bigbluebuttonbn.helpers.updateId(data);
        if (data.action === 'publish') {
            this.recordingPublishCompletion(data.recordingid);
            return;
        }
        if (data.action === 'unpublish') {
            this.recordingUnpublishCompletion(data.recordingid);
        }
    },

    recordingActionFailover: function(data) {
        var alert = new M.core.alert({
            title: M.util.get_string('error', 'moodle'),
            message: data.message
        });
        alert.show();
        M.mod_bigbluebuttonbn.helpers.toggleSpinningWheelOff(data);
        if (data.action === 'edit') {
            this.recordingEditCompletion(data, true);
        }
    },

    recordingPublishCompletion: function(recordingid) {
        var playbacks = Y.one('#playbacks-' + recordingid);
        playbacks.show();
        var preview = Y.one('#preview-' + recordingid);
        if (preview === null) {
            return;
        }
        preview.show();
        M.mod_bigbluebuttonbn.helpers.reloadPreview(recordingid);
    },

    recordingUnpublishCompletion: function(recordingid) {
        var playbacks = Y.one('#playbacks-' + recordingid);
        playbacks.hide();
        var preview = Y.one('#preview-' + recordingid);
        if (preview === null) {
            return;
        }
        preview.hide();
    },

    recordingIsImported: function(element) {
        var nodeelement = Y.one(element);
        var node = nodeelement.ancestor('tr');
        return (node.getAttribute('data-imported') === 'true');
    }
};
