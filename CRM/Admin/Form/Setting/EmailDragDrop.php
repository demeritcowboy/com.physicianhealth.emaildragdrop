<?php

class CRM_Admin_Form_Setting_EmailDragDrop extends CRM_Admin_Form_Setting
{
    public function buildQuickForm( ) {
        CRM_Utils_System::setTitle(ts('Settings - Email Drag and Drop'));

        // Might be a better way to do this?
        $ms = new CRM_Admin_Page_MailSettings();
        $ms->browse();
        $allAccounts = $ms->getTemplate()->get_template_vars('rows');
        $aList = array();
        foreach ($allAccounts as $a) {
            if ($a['protocol'] == 'Localdir') {
                $aList[$a['id']] = htmlspecialchars($a['name'] . ' (' . $a['source'] . ')');
            }
        }

        if (empty($aList)) {
            $this->assign('noLocaldirAccounts', 1);
        } else {
            asort($aList);

            $this->addElement('select',
                              'emaildragdrop_localdir_processor',
                              ts('Localdir mail account to be used'),
                              array('' => ts('- select -')) + $aList
                             );
        }

        parent::buildQuickForm();
    }
}
