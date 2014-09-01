# To install:
1. Set up your extensions directory. See [http://wiki.civicrm.org/confluence/display/CRMDOC/Extensions](http://wiki.civicrm.org/confluence/display/CRMDOC/Extensions).
2. Download this project (either using the github.com download link or git clone) and extract it into your extensions directory. The tree should have a folder called com.physicianhealth.emaildragdrop directly under the extensions directory, so if you see this README directly under your extensions directory it's one level too high.
3. Follow the instructions at the above page including the note where it says Manual Installation of Native Extensions.

# To configure:
1. Create a LocalDir mail account in CiviCRM under Administer - CiviMail - Mail Accounts, choosing LocalDir for the protocol and Email-to-Activity Processing where it says "Used For".
2. The Source folder can be anywhere, but note:
  1. The folder is on the server. Local here means server.
  2. If you also run the Email Processor via cron using the CLI, note that emails filed using this extension will have the access level of the web server, which may be different than the user that cron runs under. This will create problems because the folders under sites/default/files/civicrm/custom/CiviMail.XXX by default probably won't get created so that both users can write to them. One option is to set your cron task so that it runs as the web server user.
3. There should be a new menu item under Administer - CiviMail - Email Drag and Drop. If not go to Administer - Administration Console and you should see it there under CiviMail. Here you tell it which Mail Account to use (the one you just created above).
4. Create something somewhere on your site that displays output like the following. For example in Drupal you can create a sidebar block with this content that shows on every civicrm page.<code><div class="emaildragdrop">Drag emails here</div></code>
5. You can also add a class "no-questions-asked" to the div. This tells it not to pop up a box asking if you want to change the email subject before filing. Theoretically you could also have two divs in the block, one with and one without.<code><div class="emaildragdrop no-questions-asked">Drag emails here (no questions)</div></code>
6. The file in the css folder has some default styling for this div. Override as desired.

# Usage Notes and Limitations
* It does not work with WebMail (i.e. reading mail through your browser). It must be dragged from a desktop email program (e.g. Outlook, Thunderbird).
* On a Mac, it does not seem to work directly from email to browser (tried with all combinations of safari, firefox, applemail, and thunderbird). A workaround is to first drag the email to the desktop, then drag from the desktop into the browser div area, then delete the file from your desktop. If anybody knows how to fix this please let me know - the technical problem seems to be that the JS dataTransfer object which should contain the email doesn't.
* This has only been tested with CiviCRM 4.2.1 and Drupal (actually only Drupal 6, but that shouldn't matter).
* Also not tested when using clean urls.
* For anyone using 4.2 with other hooks, note that you may need to backport CRM-11212 since enabling extensions might cause those customizations to stop working.
