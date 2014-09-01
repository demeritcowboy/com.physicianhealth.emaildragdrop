<?php

require_once 'emaildragdrop.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function emaildragdrop_civicrm_config(&$config) {
  _emaildragdrop_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function emaildragdrop_civicrm_xmlMenu(&$files) {
  _emaildragdrop_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function emaildragdrop_civicrm_install() {
  return _emaildragdrop_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function emaildragdrop_civicrm_uninstall() {
  return _emaildragdrop_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function emaildragdrop_civicrm_enable() {
  return _emaildragdrop_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function emaildragdrop_civicrm_disable() {
  return _emaildragdrop_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function emaildragdrop_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _emaildragdrop_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function emaildragdrop_civicrm_managed(&$entities) {
  return _emaildragdrop_civix_civicrm_managed($entities);
}

function emaildragdrop_civicrm_alterContent( &$content, $context, $tplName, &$object ) {
    $config = &CRM_Core_Config::singleton();
    $content .= '<link rel="stylesheet" type="text/css" href="' . $config->extensionsURL . '/com.physicianhealth.emaildragdrop/css/emaildragdrop.css" />';
    $content .= '<script type="text/javascript" src="' . $config->extensionsURL . '/com.physicianhealth.emaildragdrop/js/emaildragdrop.js"></script>';
}
