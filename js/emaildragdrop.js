cj(document).ready(function() {
    var edd = cj('div.emaildragdrop');
    edd.on('dragover', function(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        evt.originalEvent.dataTransfer.dropEffect = 'copy';
        return false;
    });
    edd.on('dragenter', function(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        return false;
    });
    edd.on('drop', function(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        var files = evt.originalEvent.dataTransfer.files;
        if (!files.length) {
            return;
        }

        var CHUNK_SIZE = 512 * 1024;

//      CRM object not available in CiviCRM 4.2
//TODO: This isn't smarty so can't use crmURL to format it correctly. This will fail before 4.3 if not using Drupal.
        var submitUrl = '/index.php?q=civicrm/physicianhealthbc/emaildragdrop/ajax';
        if (typeof(CRM) != 'undefined') {
            submitUrl = CRM.url('civicrm/physicianhealthbc/emaildragdrop/ajax');
        }

        // This is a bit weird, but what I really want is to also be able to change the activity type and possibly change the target/assignee contact. The contact part might be doable but not sure how to get the email processor to change the type, and it's difficult to locate the processed activity after processing in a reliable way. So if I can figure that out then at that point I'd replace this with a proper form in a dialog box/ajax call.
        var newSubject = '';
        if (! cj(this).hasClass('no-questions-asked')) {
            if (files.length == 1) {
                newSubject = window.prompt('New Subject (Leave blank to keep original)', '');
                if (newSubject == null) {
                    return;
                }
            }
        }

        var dlg = cj('<div><p>Please wait...</p><div id="emaildragdrop_progress"></div></div>');
        dlg.dialog({title: "Email Drag and Drop"});
        cj('#emaildragdrop_progress').progressbar({value: false});
        cj('#emaildragdrop_progress .ui-progressbar-value').css('width', 'auto').show(); // It seems to be invisible if we don't do both of these.

        for (var filecount = 0; filecount < files.length; filecount++) {
            var fid = "" + Math.floor(Math.random() * 10000000 + 1);
            var f = files[filecount];

            // Even if we were using synchronous calls the individual slices might finish at different times because we don't have any control over when onloadend fires, so we have to keep track some other way of when they are all done. We know how many slices there will be in advance, so we just need to count them as they finish.
            var total_chunks = Math.ceil(f.size / CHUNK_SIZE);
            var chunks_finished = 0;

            for (var i = 0; i < f.size; i += CHUNK_SIZE) {
                var reader = new FileReader();
                reader.emaildragdrop_sbyte = i;

                reader.onloadend = function(ev) {
                    if (ev.target.readyState == FileReader.DONE) {

                        cj.ajax(submitUrl, {
                            async: true,
                            data: {fid: fid,
                                sbyte: this.emaildragdrop_sbyte,
                                chunk: ev.target.result
                            },
                            dataType: 'json',
                            type: 'POST'
                        })
                        .fail(function(jqXHR, textStatus, errorThrown) {
                            dlg.empty().html("Error: " + textStatus);
                            //alert("Error: " + textStatus);
                        })
                        .done(function(data, textStatus, jqXHR) {
                            if (data.msg != '') {
                                dlg.empty().html("Error: " + data.msg);
                                //alert("Error: " + data.msg);
                            } else {
                                chunks_finished++;
                                if (chunks_finished == total_chunks) {
                                    // That must be the last chunk so now send window.location.href to determine contact and optionally case id (and also user's choices for new subject, etc?) to server to complete the transaction.

                                    cj.ajax(submitUrl, {
                                        data: {fid: fid, url: window.location.href, subj: newSubject},
                                        dataType: 'json',
                                        type: 'POST'
                                    })
                                    .fail(function(jqXHR, textStatus, errorThrown) {
                                        dlg.empty().html("Error: " + textStatus);
                                        //alert("Error: " + textStatus);
                                    })
                                    .done(function(data, textStatus, jqXHR) {
                                        cj('#emaildragdrop_progress').progressbar("destroy");
                                        //dlg.dialog("destroy");
                                        //alert(data.msg);
                                        dlg.empty().html(data.msg.replace(/\n/g, '<br />'));
                                    });
                                }
                            }
                        });
                    }
                };

                var endbyte = (i+CHUNK_SIZE < f.size) ? i+CHUNK_SIZE : f.size;
                var b = f.slice(i, endbyte);
                reader.readAsBinaryString(b);
            }
        }
    });
});
