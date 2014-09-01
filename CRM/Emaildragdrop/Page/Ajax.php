<?php

require_once 'CRM/Core/Page.php';

class CRM_Emaildragdrop_Page_Ajax extends CRM_Core_Page {

function run() {
    $retval = array( 'msg' => '' );
    $config = &CRM_Core_Config::singleton();

    if (empty($_POST['fid'])) {
        $retval['msg'] = ts('Invalid parameters.') . ': ' . __LINE__;
    } elseif (empty($config->emaildragdrop_localdir_processor)) {
        $retval['msg'] = ts('Please set which mail account to use for this feature by visiting <a href="%1">Administer - CiviMail - Email Drag and Drop</a>', array(1 => CRM_Utils_System::url('civicrm/admin/setting/emaildragdrop', 'reset=1')));

    } else {

        $fid = $_POST['fid'];
        $uploadDir = $config->uploadDir . (substr($config->uploadDir, -1) == '/' ? '' : '/');
        $fname = $uploadDir . 'emaildragdrop' . $fid;

        if (isset($_POST['url'])) {
            // No more chunks. Parse url and do something with the chunks.
            list($caseid, $cid, $retval['msg']) = $this->guess_target($_POST['url']);
            $casehash = '';
            if (!empty($caseid)) {
                // see CRM_Contact_Form_Task_EmailCommon::postProcess()
                $casehash = substr(sha1(CIVICRM_SITE_KEY . $caseid), 0, 7);
            }

            $retval['msg'] = $this->sendToProcessor($fname,
                $casehash,
                empty($_POST['subj']) ? '' : $_POST['subj']
            );

            if (empty($retval['msg'])) {
                // Trigger a run of the email processor

                // One problem here is that the api call does echos, which mess up this return value which is expected to be json, so even if it's successful it gives an error. So do output buffering to catch it.
                // Note also possible directory permissions clashes if cron also runs the email processor as a user other than the web server account.

                $params = array('version' => 3, 'auth' => false);
                ob_start();
                $api_result = civicrm_api('Job', 'fetch_activities', $params);
                if ($api_result['is_error']) {
                    $retval['msg'] = $api_result['error_message'] . ': ' . __LINE__;
                }
                $retval['msg'] .= "\n" . ob_get_contents();
                ob_end_clean();
            }

            $retval['msg'] = trim($retval['msg']);
            if (empty($retval['msg'])) {
                $retval['msg'] = ts('No output from email processor. Probably something went wrong.');
            } else {
                $retval['msg'] = ts('The email processor says: ') . $retval['msg'];
            }

        } elseif (isset($_POST['chunk'])) {
            // We don't know what order they are arriving in, so use start byte in the filename and add to index file so we can order them later.
            if (file_put_contents($fname . '.' . $_POST['sbyte'], $_POST['chunk']) === FALSE) {
                $retval['msg'] = ts('Error writing file chunk on server.');
            }
// TODO: There's a potential problem here if two arrive at exactly the same time and both try to update the index file.
            if (file_put_contents($fname . '.index', $_POST['sbyte'] . "\n", FILE_APPEND | LOCK_EX) === FALSE) {
                $retval['msg'] = ts('Error writing file chunk index on server.');
            }
        } else {
            $retval['msg'] = ts('Invalid parameters.');
        }
    }

    echo json_encode($retval);
    CRM_Utils_System::civiExit();
}

function guess_target($url) {
    $url = parse_url($url);
    $qarr = array();
    if (!empty($url['query'])) {
        parse_str($url['query'], $qarr);
    }

    // Very simple guessing. If it contains "case", then it's in case context, otherwise assume non-case.
    $caseid = '';
    $cid = '';
    $rmsg = '';
    if (strpos($url['path'], 'case') !== FALSE || strpos($url['query'], 'case') !== FALSE) { // depends if clean urls or not
        if (!empty($qarr['caseid'])) {
            $caseid = $qarr['caseid'];
        } elseif (!empty($qarr['id'])) {
            $caseid = $qarr['id'];
        }
        if (empty($caseid)) {
            $rmsg = ts('Current page appears to be case-related but unable to determine case id. Allowing email processor to file as it normally would.');
        }

    } else {
        if (!empty($qarr['cid'])) {
            $cid = $qarr['cid'];
        }
        if (empty($cid)) {
            // we don't actually care, since we'll just be letting the email processor do its thing, but leaving in the code since it might be a nice feature to redirect to a different contact
            //$rmsg = ts('Unable to determine desired contact from current page. Allowing email processor to file as it normally would.');
        }
    }

    return array($caseid, $cid, $rmsg);
}

function sendToProcessor($fname, $casehash, $newsubj) {
    $rmsg = '';

    $config = &CRM_Core_Config::singleton();
    $localdir_folder = $this->getLocalDir($config->emaildragdrop_localdir_processor);
    $localdir_folder .= (substr($localdir_folder, -1) == '/' ? '' : '/');

    $bytelist = array();
    $fp1 = fopen($fname . '.index', 'r');
    if ($fp1 === FALSE) {
        $rmsg = ts('Index file seems to have disappeared?') . "\n\n$fname";
    } else {
        while (($buf = fgets($fp1)) !== false) {
            $bytelist[] = rtrim($buf);
        }
        fclose($fp1);

        sort($bytelist, SORT_NUMERIC);
        // Now we have our list of start bytes in order, we can reassemble the files into the right order.

        $fp2 = fopen($localdir_folder . basename($fname), 'w');
        if ($fp2 === FALSE) {
            $rmsg = ts('Unable to create temp file.');
        } else {
            foreach ($bytelist as $b) {
                $fp1 = fopen($fname . '.' . $b, 'r');
                while (($buf = fgets($fp1)) !== false) {
                    if (substr($buf, 0, 5) == 'From ') {
                        // Skip since this messes things up.
                        continue;
                    }

                    if (substr($buf, 0, 9) == 'Subject: ') {
                        if (empty($newsubj)) {
                            $newsubj = substr($buf, 9); // use original subject
                        } else {
                            // make sure there's a newline on the end
                            $newsubj .= (substr($newsubj, -1) == "\n" ? "" : "\n");
                        }

                        if (empty($casehash)) {
                            $writeerr = fwrite($fp2, 'Subject: ' . $newsubj);
                        } else {
                            $writeerr = fwrite($fp2, 'Subject: [case #' . $casehash . '] ' . $newsubj);
                        }

                    } else {

                        $writeerr = fwrite($fp2, $buf);
                    }

                    if ($writerr === FALSE) {
                        $rmsg = ts('Error during file copy');
                        break;
                    }
                }

                fclose($fp1);
                unlink($fname . '.' . $b);
            }
            fclose($fp2);
            unlink($fname . '.index');
        }
    }

    return $rmsg;
}

function getLocalDir($ms_id) {
    $mailSetting = new CRM_Core_DAO_MailSettings();
    $mailSetting->domain_id = CRM_Core_Config::domainID();
    $mailSetting->id = $ms_id;
    $mailSetting->find();
    $d = '';
    if ($mailSetting->fetch()) {
        $d = $mailSetting->source;
    }
    return $d;
}

} // end class
