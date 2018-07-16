<?php

/**
 * @
 
 
 ile
 * Enables the user registration and login system.
 */

/**
 * Maximum length of username text field.
 */
define('USERNAME_MAX_LENGTH', 60);

/**
 * Maximum length of user e-mail text field.
 */
define('EMAIL_MAX_LENGTH', 254);

/**
 * Only administrators can create user accounts.
 */
define('USER_REGISTER_ADMINISTRATORS_ONLY', 0);

/**
 * Visitors can create their own accounts.
 */
define('USER_REGISTER_VISITORS', 1);

/**
 * Visitors can create accounts, but they don't become active without
 * administrative approval.
 */
define('USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL', 2);

/**
 * Implements hook_help().
 */
function user_help($path, $arg) {
  global $user;

  switch ($path) {
    case 'admin/help#user':
      $output = '';
      $output .= '<h3>' . t('About') . '</h3>';
      $output .= '<p>' . t('The User module allows users to register, log in, and log out. It also allows users with proper permissions to manage user roles (used to classify users) and permissions associated with those roles. For more information, see the online handbook entry for <a href="@user">User module</a>.', array('@user' => 'http://drupal.org/documentation/modules/user')) . '</p>';
      $output .= '<h3>' . t('Uses') . '</h3>';
      $output .= '<dl>';
      $output .= '<dt>' . t('Creating and managing users') . '</dt>';
      $output .= '<dd>' . t('The User module allows users with the appropriate <a href="@permissions">permissions</a> to create user accounts through the <a href="@people">People administration page</a>, where they can also assign users to one or more roles, and block or delete user accounts. If allowed, users without accounts (anonymous users) can create their own accounts on the <a href="@register">Create new account</a> page.', array('@permissions' => url('admin/people/permissions', array('fragment' => 'module-user')), '@people' => url('admin/people'), '@register' => url('user/register'))) . '</dd>';
      $output .= '<dt>' . t('User roles and permissions') . '</dt>';
      $output .= '<dd>' . t('<em>Roles</em> are used to group and classify users; each user can be assigned one or more roles. By default there are two roles: <em>anonymous user</em> (users that are not logged in) and <em>authenticated user</em> (users that are registered and logged in). Depending on choices you made when you installed Drupal, the installation process may have defined more roles, and you can create additional custom roles on the <a href="@roles">Roles page</a>. After creating roles, you can set permissions for each role on the <a href="@permissions_user">Permissions page</a>. Granting a permission allows users who have been assigned a particular role to perform an action on the site, such as viewing a particular type of content, editing or creating content, administering settings for a particular module, or using a particular function of the site (such as search).', array('@permissions_user' => url('admin/people/permissions'), '@roles' => url('admin/people/permissions/roles'))) . '</dd>';
      $output .= '<dt>' . t('Account settings') . '</dt>';
      $output .= '<dd>' . t('The <a href="@accounts">Account settings page</a> allows you to manage settings for the displayed name of the anonymous user role, personal contact forms, user registration, and account cancellation. On this page you can also manage settings for account personalization (including signatures and user pictures), and adapt the text for the e-mail messages that are sent automatically during the user registration process.', array('@accounts'  => url('admin/config/people/accounts'))) . '</dd>';
      $output .= '</dl>';
      return $output;
    case 'admin/people/create':
      return '<p>' . t("This web page allows administrators to register new users. Users' e-mail addresses and usernames must be unique.") . '</p>';
    case 'admin/people/permissions':
      return '<p>' . t('Permissions let you control what users can do and see on your site. You can define a specific set of permissions for each role. (See the <a href="@role">Roles</a> page to create a role). Two important roles to consider are Authenticated Users and Administrators. Any permissions granted to the Authenticated Users role will be given to any user who can log into your site. You can make any role the Administrator role for the site, meaning this will be granted all new permissions automatically. You can do this on the <a href="@settings">User Settings</a> page. You should be careful to ensure that only trusted users are given this access and level of control of your site.', array('@role' => url('admin/people/permissions/roles'), '@settings' => url('admin/config/people/accounts'))) . '</p>';
    case 'admin/people/permissions/roles':
      $output = '<p>' . t('Roles allow you to fine tune the security and administration of Drupal. A role defines a group of users that have certain privileges as defined on the <a href="@permissions">permissions page</a>. Examples of roles include: anonymous user, authenticated user, moderator, administrator and so on. In this area you will define the names and order of the roles on your site. It is recommended to order your roles from least permissive (anonymous user) to most permissive (administrator). To delete a role choose "edit role".', array('@permissions' => url('admin/people/permissions'))) . '</p>';
      $output .= '<p>' . t('By default, Drupal comes with two user roles:') . '</p>';
      $output .= '<ul>';
      $output .= '<li>' . t("Anonymous user: this role is used for users that don't have a user account or that are not authenticated.") . '</li>';
      $output .= '<li>' . t('Authenticated user: this role is automatically granted to all logged in users.') . '</li>';
      $output .= '</ul>';
      return $output;
    case 'admin/config/people/accounts/fields':
      return '<p>' . t('This form lets administrators add, edit, and arrange fields for storing user data.') . '</p>';
    case 'admin/config/people/accounts/display':
      return '<p>' . t('This form lets administrators configure how fields should be displayed when rendering a user profile page.') . '</p>';
    case 'admin/people/search':
      return '<p>' . t('Enter a simple pattern ("*" may be used as a wildcard match) to search for a username or e-mail address. For example, one may search for "br" and Drupal might return "brian", "brad", and "brenda@example.com".') . '</p>';
  }
}

/**
 * Invokes a user hook in every module.
 *
 * We cannot use module_invoke() for this, because the arguments need to
 * be passed by reference.
 *
 * @param $type
 *   A text string that controls which user hook to invoke.  Valid choices are:
 *   - cancel: Invokes hook_user_cancel().
 *   - insert: Invokes hook_user_insert().
 *   - login: Invokes hook_user_login().
 *   - presave: Invokes hook_user_presave().
 *   - update: Invokes hook_user_update().
 * @param $edit
 *   An associative array variable containing form values to be passed
 *   as the first parameter of the hook function.
 * @param $account
 *   The user account object to be passed as the second parameter of the hook
 *   function.
 * @param $category
 *   The category of user information being acted upon.
 */
function user_module_invoke($type, &$edit, $account, $category = NULL) {
  foreach (module_implements('user_' . $type) as $module) {
    $function = $module . '_user_' . $type;
    $function($edit, $account, $category);
  }
}

/**
 * Implements hook_theme().
 */
function user_theme() {
  return array(
    'user_picture' => array(
      'variables' => array('account' => NULL),
      'template' => 'user-picture',
    ),
    'user_profile' => array(
      'render element' => 'elements',
      'template' => 'user-profile',
      'file' => 'user.pages.inc',
    ),
    'user_profile_category' => array(
      'render element' => 'element',
      'template' => 'user-profile-category',
      'file' => 'user.pages.inc',
    ),
    'user_profile_item' => array(
      'render element' => 'element',
      'template' => 'user-profile-item',
      'file' => 'user.pages.inc',
    ),
    'user_list' => array(
      'variables' => array('users' => NULL, 'title' => NULL),
    ),
    'user_admin_permissions' => array(
      'render element' => 'form',
      'file' => 'user.admin.inc',
    ),
    'user_admin_roles' => array(
      'render element' => 'form',
      'file' => 'user.admin.inc',
    ),
    'user_permission_description' => array(
      'variables' => array('permission_item' => NULL, 'hide' => NULL),
      'file' => 'user.admin.inc',
    ),
    'user_signature' => array(
      'variables' => array('signature' => NULL),
    ),
  );
}

/**
 * Implements hook_entity_info().
 */
function user_entity_info() {
  $return = array(
    'user' => array(
      'label' => t('User'),
      'controller class' => 'UserController',
      'base table' => 'users',
      'uri callback' => 'user_uri',
      'label callback' => 'format_username',
      'fieldable' => TRUE,
      // $user->language is only the preferred user language for the user
      // interface textual elements. As it is not necessarily related to the
      // language assigned to fields, we do not define it as the entity language
      // key.
      'entity keys' => array(
        'id' => 'uid',
      ),
      'bundles' => array(
        'user' => array(
          'label' => t('User'),
          'admin' => array(
            'path' => 'admin/config/people/accounts',
            'access arguments' => array('administer users'),
          ),
        ),
      ),
      'view modes' => array(
        'full' => array(
          'label' => t('User account'),
          'custom settings' => FALSE,
        ),
      ),
    ),
  );
  return $return;
}

/**
 * Implements callback_entity_info_uri().
 */
function user_uri($user) {
  return array(
    'path' => 'user/' . $user->uid,
  );
}

/**
 * Implements hook_field_info_alter().
 */
function user_field_info_alter(&$info) {
  // Add the 'user_register_form' instance setting to all field types.
  foreach ($info as $field_type => &$field_type_info) {
    $field_type_info += array('instance_settings' => array());
    $field_type_info['instance_settings'] += array(
      'user_register_form' => FALSE,
    );
  }
}

/**
 * Implements hook_field_extra_fields().
 */
function user_field_extra_fields() {
  $return['user']['user'] = array(
    'form' => array(
      'account' => array(
        'label' => t('User name and password'),
        'description' => t('User module account form elements.'),
        'weight' => -10,
      ),
      'timezone' => array(
        'label' => t('Timezone'),
        'description' => t('User module timezone form element.'),
        'weight' => 6,
      ),
    ),
    'display' => array(
      'summary' => array(
        'label' => t('History'),
        'description' => t('User module history view element.'),
        'weight' => 5,
      ),
    ),
  );

  return $return;
}

/**
 * Fetches a user object based on an external authentication source.
 *
 * @param string $authname
 *   The external authentication username.
 *
 * @return
 *   A fully-loaded user object if the user is found or FALSE if not found.
 */
function user_external_load($authname) {
  $uid = db_query("SELECT uid FROM {authmap} WHERE authname = :authname", array(':authname' => $authname))->fetchField();

  if ($uid) {
    return user_load($uid);
  }
  else {
    return FALSE;
  }
}

/**
 * Load multiple users based on certain conditions.
 *
 * This function should be used whenever you need to load more than one user
 * from the database. Users are loaded into memory and will not require
 * database access if loaded again during the same page request.
 *
 * @param $uids
 *   An array of user IDs.
 * @param $conditions
 *   (deprecated) An associative array of conditions on the {users}
 *   table, where the keys are the database fields and the values are the
 *   values those fields must have. Instead, it is preferable to use
 *   EntityFieldQuery to retrieve a list of entity IDs loadable by
 *   this function.
 * @param $reset
 *   A boolean indicating that the internal cache should be reset. Use this if
 *   loading a user object which has been altered during the page request.
 *
 * @return
 *   An array of user objects, indexed by uid.
 *
 * @see entity_load()
 * @see user_load()
 * @see user_load_by_mail()
 * @see user_load_by_name()
 * @see EntityFieldQuery
 *
 * @todo Remove $conditions in Drupal 8.
 */
function user_load_multiple($uids = array(), $conditions = array(), $reset = FALSE) {
  return entity_load('user', $uids, $conditions, $reset);
}

/**
 * Controller class for users.
 *
 * This extends the DrupalDefaultEntityController class, adding required
 * special handling for user objects.
 */
class UserController extends DrupalDefaultEntityController {

  function attachLoad(&$queried_users, $revision_id = FALSE) {
    // Build an array of user picture IDs so that these can be fetched later.
    $picture_fids = array();
    foreach ($queried_users as $key => $record) {
      $picture_fids[] = $record->picture;
      $queried_users[$key]->data = unserialize($record->data);
      $queried_users[$key]->roles = array();
      if ($record->uid) {
        $queried_users[$record->uid]->roles[DRUPAL_AUTHENTICATED_RID] = 'authenticated user';
      }
      else {
        $queried_users[$record->uid]->roles[DRUPAL_ANONYMOUS_RID] = 'anonymous user';
      }
    }

    // Add any additional roles from the database.
    $result = db_query('SELECT r.rid, r.name, ur.uid FROM {role} r INNER JOIN {users_roles} ur ON ur.rid = r.rid WHERE ur.uid IN (:uids)', array(':uids' => array_keys($queried_users)));
    foreach ($result as $record) {
      $queried_users[$record->uid]->roles[$record->rid] = $record->name;
    }

    // Add the full file objects for user pictures if enabled.
    if (!empty($picture_fids) && variable_get('user_pictures', 0)) {
      $pictures = file_load_multiple($picture_fids);
      foreach ($queried_users as $account) {
        if (!empty($account->picture) && isset($pictures[$account->picture])) {
          $account->picture = $pictures[$account->picture];
        }
        else {
          $account->picture = NULL;
        }
      }
    }
    // Call the default attachLoad() method. This will add fields and call
    // hook_user_load().
    parent::attachLoad($queried_users, $revision_id);
  }
}

/**
 * Loads a user object.
 *
 * Drupal has a global $user object, which represents the currently-logged-in
 * user. So to avoid confusion and to avoid clobbering the global $user object,
 * it is a good idea to assign the result of this function to a different local
 * variable, generally $account. If you actually do want to act as the user you
 * are loading, it is essential to call drupal_save_session(FALSE); first.
 * See
 * @link http://drupal.org/node/218104 Safely impersonating another user @endlink
 * for more information.
 *
 * @param $uid
 *   Integer specifying the user ID to load.
 * @param $reset
 *   TRUE to reset the internal cache and load from the database; FALSE
 *   (default) to load from the internal cache, if set.
 *
 * @return
 *   A fully-loaded user object upon successful user load, or FALSE if the user
 *   cannot be loaded.
 *
 * @see user_load_multiple()
 */
function user_load($uid, $reset = FALSE) {
  $users = user_load_multiple(array($uid), array(), $reset);
  return reset($users);
}

/**
 * Fetch a user object by email address.
 *
 * @param $mail
 *   String with the account's e-mail address.
 * @return
 *   A fully-loaded $user object upon successful user load or FALSE if user
 *   cannot be loaded.
 *
 * @see user_load_multiple()
 */
function user_load_by_mail($mail) {
  $users = user_load_multiple(array(), array('mail' => $mail));
  return reset($users);
}

/**
 * Fetch a user object by account name.
 *
 * @param $name
 *   String with the account's user name.
 * @return
 *   A fully-loaded $user object upon successful user load or FALSE if user
 *   cannot be loaded.
 *
 * @see user_load_multiple()
 */
function user_load_by_name($name) {
  $users = user_load_multiple(array(), array('name' => $name));
  return reset($users);
}

/**
 * Save changes to a user account or add a new user.
 *
 * @param $account
 *   (optional) The user object to modify or add. If you want to modify
 *   an existing user account, you will need to ensure that (a) $account
 *   is an object, and (b) you have set $account->uid to the numeric
 *   user ID of the user account you wish to modify. If you
 *   want to create a new user account, you can set $account->is_new to
 *   TRUE or omit the $account->uid field.
 * @param $edit
 *   An array of fields and values to save. For example array('name'
 *   => 'My name'). Key / value pairs added to the $edit['data'] will be
 *   serialized and saved in the {users.data} column.
 * @param $category
 *   (optional) The category for storing profile information in.
 *
 * @return
 *   A fully-loaded $user object upon successful save or FALSE if the save failed.
 */
function user_save($account, $edit = array(), $category = 'account') {


  $transaction = db_transaction();
  	/*$name = substr($edit['name'],0,3);
  	$random_no = rand(1000,10000);
  	$newpass = $name.$random_no;
  	$edit['pass'] = $newpass;*/
  	
  	
  	
  try {
    if (isset($edit['pass']) && strlen(trim($edit['pass'])) > 0) {
      // Allow alternate password hashing schemes.
      require_once DRUPAL_ROOT . '/' . variable_get('password_inc', 'includes/password.inc');
      $edit['pass'] = user_hash_password(trim($edit['pass']));
      // Abort if the hashing failed and returned FALSE.
      
      if (!$edit['pass']) {
        return FALSE;
      }
    }
    else {
      // Avoid overwriting an existing password with a blank password.
      unset($edit['pass']);
    }
    if (isset($edit['mail'])) {
      $edit['mail'] = trim($edit['mail']);
    }

    // Load the stored entity, if any.
    if (!empty($account->uid) && !isset($account->original)) {
      $account->original = entity_load_unchanged('user', $account->uid);
    }

    if (empty($account)) {
      $account = new stdClass();
    }
    if (!isset($account->is_new)) {
      $account->is_new = empty($account->uid);
    }
    // Prepopulate $edit['data'] with the current value of $account->data.
    // Modules can add to or remove from this array in hook_user_presave().
    if (!empty($account->data)) {
      $edit['data'] = !empty($edit['data']) ? array_merge($account->data, $edit['data']) : $account->data;
    }

    // Invoke hook_user_presave() for all modules.
    user_module_invoke('presave', $edit, $account, $category);

    // Invoke presave operations of Field Attach API and Entity API. Those APIs
    // require a fully-fledged and updated entity object. Therefore, we need to
    // copy any new property values of $edit into it.
    foreach ($edit as $key => $value) {
      $account->$key = $value;
    }
    field_attach_presave('user', $account);
    module_invoke_all('entity_presave', $account, 'user');

    if (is_object($account) && !$account->is_new) {
      // Process picture uploads.
      if (!empty($account->picture->fid) && (!isset($account->original->picture->fid) || $account->picture->fid != $account->original->picture->fid)) {
        $picture = $account->picture;
        // If the picture is a temporary file move it to its final location and
        // make it permanent.
        if (!$picture->status) {
          $info = image_get_info($picture->uri);
          $picture_directory =  file_default_scheme() . '://' . variable_get('user_picture_path', 'pictures');

          // Prepare the pictures directory.
          file_prepare_directory($picture_directory, FILE_CREATE_DIRECTORY);
          $destination = file_stream_wrapper_uri_normalize($picture_directory . '/picture-' . $account->uid . '-' . REQUEST_TIME . '.' . $info['extension']);

          // Move the temporary file into the final location.
          if ($picture = file_move($picture, $destination, FILE_EXISTS_RENAME)) {
            $picture->status = FILE_STATUS_PERMANENT;
            $account->picture = file_save($picture);
            file_usage_add($picture, 'user', 'user', $account->uid);
          }
        }
        // Delete the previous picture if it was deleted or replaced.
        if (!empty($account->original->picture->fid)) {
          file_usage_delete($account->original->picture, 'user', 'user', $account->uid);
          file_delete($account->original->picture);
        }
      }
      elseif (isset($edit['picture_delete']) && $edit['picture_delete']) {
        file_usage_delete($account->original->picture, 'user', 'user', $account->uid);
        file_delete($account->original->picture);
      }
      // Save the picture object, if it is set. drupal_write_record() expects
      // $account->picture to be a FID.
      $picture = empty($account->picture) ? NULL : $account->picture;
      $account->picture = empty($account->picture->fid) ? 0 : $account->picture->fid;

      // Do not allow 'uid' to be changed.
      $account->uid = $account->original->uid;
      // Save changes to the user table.
      $success = drupal_write_record('users', $account, 'uid');
      // Restore the picture object.
      $account->picture = $picture;
      if ($success === FALSE) {
        // The query failed - better to abort the save than risk further
        // data loss.
        return FALSE;
      }

      // Reload user roles if provided.
      if ($account->roles != $account->original->roles) {
        db_delete('users_roles')
          ->condition('uid', $account->uid)
          ->execute();

        $query = db_insert('users_roles')->fields(array('uid', 'rid'));
        foreach (array_keys($account->roles) as $rid) {
          if (!in_array($rid, array(DRUPAL_ANONYMOUS_RID, DRUPAL_AUTHENTICATED_RID))) {
            $query->values(array(
              'uid' => $account->uid,
              'rid' => $rid,
            ));
          }
        }
        $query->execute();
      }

      // Delete a blocked user's sessions to kick them if they are online.
      if ($account->original->status != $account->status && $account->status == 0) {
        drupal_session_destroy_uid($account->uid);
      }

      // If the password changed, delete all open sessions and recreate
      // the current one.
      if ($account->pass != $account->original->pass) {
        drupal_session_destroy_uid($account->uid);
        if ($account->uid == $GLOBALS['user']->uid) {
          drupal_session_regenerate();
        }
      }

      // Save Field data.
      
      
      field_attach_update('user', $account);
	//$account->pass  =  $newpass;

	
      // Send emails after we have the new user object.
      if ($account->status != $account->original->status) {
    
     //print_r($account);exit;
        // The user's status is changing; conditionally send notification email.
        $op = $account->status == 1 ? 'status_activated' : 'status_blocked';
        _user_mail_notify($op, $account);
      }

      // Update $edit with any interim changes to $account.
      foreach ($account as $key => $value) {
        if (!property_exists($account->original, $key) || $value !== $account->original->$key) {
          $edit[$key] = $value;
        }
      }
      user_module_invoke('update', $edit, $account, $category);
      module_invoke_all('entity_update', $account, 'user');
    }
    else {
    
   // print_r($account);exit;
      // Allow 'uid' to be set by the caller. There is no danger of writing an
      // existing user as drupal_write_record will do an INSERT.
      if (empty($account->uid)) {
        $account->uid = db_next_id(db_query('SELECT MAX(uid) FROM {users}')->fetchField());
      }
      // Allow 'created' to be set by the caller.
      if (!isset($account->created)) {
        $account->created = REQUEST_TIME;
      }
      $success = drupal_write_record('users', $account);
      if ($success === FALSE) {
        // On a failed INSERT some other existing user's uid may be returned.
        // We must abort to avoid overwriting their account.
        return FALSE;
      }

      // Make sure $account is properly initialized.
      $account->roles[DRUPAL_AUTHENTICATED_RID] = 'authenticated user';

      field_attach_insert('user', $account);
      $edit = (array) $account;
      user_module_invoke('insert', $edit, $account, $category);
      module_invoke_all('entity_insert', $account, 'user');

      // Save user roles. Skip built-in roles, and ones that were already saved
      // to the database during hook calls.
      $rids_to_skip = array_merge(array(DRUPAL_ANONYMOUS_RID, DRUPAL_AUTHENTICATED_RID), db_query('SELECT rid FROM {users_roles} WHERE uid = :uid', array(':uid' => $account->uid))->fetchCol());
      if ($rids_to_save = array_diff(array_keys($account->roles), $rids_to_skip)) {
        $query = db_insert('users_roles')->fields(array('uid', 'rid'));
        foreach ($rids_to_save as $rid) {
          $query->values(array(
            'uid' => $account->uid,
            'rid' => $rid,
          ));
        }
        $query->execute();
      }
    }
    // Clear internal properties.
    unset($account->is_new);
    unset($account->original);
    // Clear the static loading cache.
    entity_get_controller('user')->resetCache(array($account->uid));

    return $account;
  }
  catch (Exception $e) {
    $transaction->rollback();
    watchdog_exception('user', $e);
    throw $e;
  }
}

/**
 * Verify the syntax of the given name.
 */
function user_validate_name($name) {
  if (!$name) {
    return t('You must enter a username.');
  }
  if (substr($name, 0, 1) == ' ') {
    return t('The username cannot begin with a space.');
  }
  if (substr($name, -1) == ' ') {
    return t('The username cannot end with a space.');
  }
  if (strpos($name, '  ') !== FALSE) {
    return t('The username cannot contain multiple spaces in a row.');
  }
  if (preg_match('/[^\x{80}-\x{F7} a-z0-9@_.\'-]/i', $name)) {
    return t('The username contains an illegal character.');
  }
  if (preg_match('/[\x{80}-\x{A0}' .         // Non-printable ISO-8859-1 + NBSP
                  '\x{AD}' .                // Soft-hyphen
                  '\x{2000}-\x{200F}' .     // Various space characters
                  '\x{2028}-\x{202F}' .     // Bidirectional text overrides
                  '\x{205F}-\x{206F}' .     // Various text hinting characters
                  '\x{FEFF}' .              // Byte order mark
                  '\x{FF01}-\x{FF60}' .     // Full-width latin
                  '\x{FFF9}-\x{FFFD}' .     // Replacement characters
                  '\x{0}-\x{1F}]/u',        // NULL byte and control characters
                  $name)) {
    return t('The username contains an illegal character.');
  }
  if (drupal_strlen($name) > USERNAME_MAX_LENGTH) {
    return t('The username %name is too long: it must be %max characters or less.', array('%name' => $name, '%max' => USERNAME_MAX_LENGTH));
  }
}

/**
 * Validates a user's email address.
 *
 * Checks that a user's email address exists and follows all standard
 * validation rules. Returns error messages when the address is invalid.
 *
 * @param $mail
 *   A user's email address.
 *
 * @return
 *   If the address is invalid, a human-readable error message is returned.
 *   If the address is valid, nothing is returned.
 */
function user_validate_mail($mail) {
  if (!$mail) {
    return t('You must enter an e-mail address.');
  }
  if (!valid_email_address($mail)) {
    return t('The e-mail address %mail is not valid.', array('%mail' => $mail));
  }
}

function confirmation_email($mail, $newmail)
{
	
	if($mail != $newmail)
	{
		return t('Confirm Email does not match.');
	}
	
 	

}


/**
 * Validates an image uploaded by a user.
 *
 * @see user_account_form()
 */
function user_validate_picture(&$form, &$form_state) {
  // If required, validate the uploaded picture.
  $validators = array(
    'file_validate_is_image' => array(),
    'file_validate_image_resolution' => array(variable_get('user_picture_dimensions', '85x85')),
    'file_validate_size' => array(variable_get('user_picture_file_size', '30') * 1024),
  );

  // Save the file as a temporary file.
  $file = file_save_upload('picture_upload', $validators);
  if ($file === FALSE) {
    form_set_error('picture_upload', t("Failed to upload the picture image; the %directory directory doesn't exist or is not writable.", array('%directory' => variable_get('user_picture_path', 'pictures'))));
  }
  elseif ($file !== NULL) {
    $form_state['values']['picture_upload'] = $file;
  }
}

/**
 * Generate a random alphanumeric password.
 */
function user_password($length = 10) {
  // This variable contains the list of allowable characters for the
  // password. Note that the number 0 and the letter 'O' have been
  // removed to avoid confusion between the two. The same is true
  // of 'I', 1, and 'l'.
  $allowable_characters = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';

  // Zero-based count of characters in the allowable list:
  $len = strlen($allowable_characters) - 1;

  // Declare the password as a blank string.
  $pass = '';

  // Loop the number of times specified by $length.
  for ($i = 0; $i < $length; $i++) {
    do {
      // Find a secure random number within the range needed.
      $index = ord(drupal_random_bytes(1));
    } while ($index > $len);

    // Each iteration, pick a random character from the
    // allowable string and append it to the password:
    $pass .= $allowable_characters[$index];
  }

  return $pass;
}

/**
 * Determine the permissions for one or more roles.
 *
 * @param $roles
 *   An array whose keys are the role IDs of interest, such as $user->roles.
 *
 * @return
 *   If $roles is a non-empty array, an array indexed by role ID is returned.
 *   Each value is an array whose keys are the permission strings for the given
 *   role ID. If $roles is empty nothing is returned.
 */
function user_role_permissions($roles = array()) {
  $cache = &drupal_static(__FUNCTION__, array());

  $role_permissions = $fetch = array();

  if ($roles) {
    foreach ($roles as $rid => $name) {
      if (isset($cache[$rid])) {
        $role_permissions[$rid] = $cache[$rid];
      }
      else {
        // Add this rid to the list of those needing to be fetched.
        $fetch[] = $rid;
        // Prepare in case no permissions are returned.
        $cache[$rid] = array();
      }
    }

    if ($fetch) {
      // Get from the database permissions that were not in the static variable.
      // Only role IDs with at least one permission assigned will return rows.
      $result = db_query("SELECT rid, permission FROM {role_permission} WHERE rid IN (:fetch)", array(':fetch' => $fetch));

      foreach ($result as $row) {
        $cache[$row->rid][$row->permission] = TRUE;
      }
      foreach ($fetch as $rid) {
        // For every rid, we know we at least assigned an empty array.
        $role_permissions[$rid] = $cache[$rid];
      }
    }
  }

  return $role_permissions;
}

/**
 * Determine whether the user has a given privilege.
 *
 * @param $string
 *   The permission, such as "administer nodes", being checked for.
 * @param $account
 *   (optional) The account to check, if not given use currently logged in user.
 *
 * @return
 *   Boolean TRUE if the user has the requested permission.
 *
 * All permission checks in Drupal should go through this function. This
 * way, we guarantee consistent behavior, and ensure that the superuser
 * can perform all actions.
 */
function user_access($string, $account = NULL) {
  global $user;

  if (!isset($account)) {
    $account = $user;
  }

  // User #1 has all privileges:
  if ($account->uid == 1) {
    return TRUE;
  }

  // To reduce the number of SQL queries, we cache the user's permissions
  // in a static variable.
  // Use the advanced drupal_static() pattern, since this is called very often.
  static $drupal_static_fast;
  if (!isset($drupal_static_fast)) {
    $drupal_static_fast['perm'] = &drupal_static(__FUNCTION__);
  }
  $perm = &$drupal_static_fast['perm'];
  if (!isset($perm[$account->uid])) {
    $role_permissions = user_role_permissions($account->roles);

    $perms = array();
    foreach ($role_permissions as $one_role) {
      $perms += $one_role;
    }
    $perm[$account->uid] = $perms;
  }

  return isset($perm[$account->uid][$string]);
}

/**
 * Checks for usernames blocked by user administration.
 *
 * @param $name
 *   A string containing a name of the user.
 *
 * @return
 *   Object with property 'name' (the user name), if the user is blocked;
 *   FALSE if the user is not blocked.
 */
function user_is_blocked($name) {
  return db_select('users')
    ->fields('users', array('name'))
    ->condition('name', db_like($name), 'LIKE')
    ->condition('status', 0)
    ->execute()->fetchObject();
}

/**
 * Checks if a user has a role.
 *
 * @param int $rid
 *   A role ID.
 *
 * @param object|null $account
 *   (optional) A user account. Defaults to the current user.
 *
 * @return bool
 *   TRUE if the user has the role, or FALSE if not.
 */
function user_has_role($rid, $account = NULL) {
  if (!$account) {
    $account = $GLOBALS['user'];
  }

  return isset($account->roles[$rid]);
}

/**
 * Implements hook_permission().
 */
function user_permission() {
  return array(
    'administer permissions' =>  array(
      'title' => t('Administer permissions'),
      'restrict access' => TRUE,
    ),
    'administer users' => array(
      'title' => t('Administer users'),
      'restrict access' => TRUE,
    ),
    'access user profiles' => array(
      'title' => t('View user profiles'),
    ),
    'change own username' => array(
      'title' => t('Change own username'),
    ),
    'cancel account' => array(
      'title' => t('Cancel own user account'),
      'description' => t('Note: content may be kept, unpublished, deleted or transferred to the %anonymous-name user depending on the configured <a href="@user-settings-url">user settings</a>.', array('%anonymous-name' => variable_get('anonymous', t('Anonymous')), '@user-settings-url' => url('admin/config/people/accounts'))),
    ),
    'select account cancellation method' => array(
      'title' => t('Select method for cancelling own account'),
      'restrict access' => TRUE,
    ),
  );
}

/**
 * Implements hook_file_download().
 *
 * Ensure that user pictures (avatars) are always downloadable.
 */
function user_file_download($uri) {
  if (strpos(file_uri_target($uri), variable_get('user_picture_path', 'pictures') . '/picture-') === 0) {
    $info = image_get_info($uri);
    return array('Content-Type' => $info['mime_type']);
  }
}

/**
 * Implements hook_file_move().
 */
function user_file_move($file, $source) {
  // If a user's picture is replaced with a new one, update the record in
  // the users table.
  if (isset($file->fid) && isset($source->fid) && $file->fid != $source->fid) {
    db_update('users')
      ->fields(array(
        'picture' => $file->fid,
      ))
      ->condition('picture', $source->fid)
      ->execute();
  }
}

/**
 * Implements hook_file_delete().
 */
function user_file_delete($file) {
  // Remove any references to the file.
  db_update('users')
    ->fields(array('picture' => 0))
    ->condition('picture', $file->fid)
    ->execute();
}

/**
 * Implements hook_search_info().
 */
function user_search_info() {
  return array(
    'title' => 'Users',
  );
}

/**
 * Implements hook_search_access().
 */
function user_search_access() {
  return user_access('access user profiles');
}

/**
 * Implements hook_search_execute().
 */
function user_search_execute($keys = NULL, $conditions = NULL) {
  $find = array();
  // Escape for LIKE matching.
  $keys = db_like($keys);
  // Replace wildcards with MySQL/PostgreSQL wildcards.
  $keys = preg_replace('!\*+!', '%', $keys);
  $query = db_select('users')->extend('PagerDefault');
  $query->fields('users', array('uid'));
  if (user_access('administer users')) {
    // Administrators can also search in the otherwise private email field,
    // and they don't need to be restricted to only active users.
    $query->fields('users', array('mail'));
    $query->condition(db_or()->
      condition('name', '%' . $keys . '%', 'LIKE')->
      condition('mail', '%' . $keys . '%', 'LIKE'));
  }
  else {
    // Regular users can only search via usernames, and we do not show them
    // blocked accounts.
    $query->condition('name', '%' . $keys . '%', 'LIKE')
      ->condition('status', 1);
  }
  $uids = $query
    ->limit(15)
    ->execute()
    ->fetchCol();
  $accounts = user_load_multiple($uids);

  $results = array();
  foreach ($accounts as $account) {
    $result = array(
      'title' => format_username($account),
      'link' => url('user/' . $account->uid, array('absolute' => TRUE)),
    );
    if (user_access('administer users')) {
      $result['title'] .= ' (' . $account->mail . ')';
    }
    $results[] = $result;
  }

  return $results;
}

/**
 * Implements hook_element_info().
 */
function user_element_info() {
  $types['user_profile_category'] = array(
    '#theme_wrappers' => array('user_profile_category'),
  );
  $types['user_profile_item'] = array(
    '#theme' => 'user_profile_item',
  );
  return $types;
}

/**
 * Implements hook_user_view().
 */
function user_user_view($account) {


 global $user;
 global $base_url;
$uid = $account->uid;

  //drupal_set_message(t($html));
 
  //$block->content = $html;
  //return $block;
 
 
 
 
   $string = $base_url.'/user/'.$account->uid.'/edit-profile';
   
$studentid = $account->field_student_id['und']['0']['value'];
$country = $account->field_country['und']['0']['tid'];
$prof_role = $account->field_professional_role['und']['0']['tid'];




if(empty($studentid) &&  $prof_role != '394' && (country == '362' || country == '311' || country == '387'))
{
  drupal_set_message(t('Please add your student ID: <a href="'.$string.'">Go to your profile.</a>'), 'error');
 

	//drupal_set_message(t('Please add your student ID %string.', array('%string' => $string)), 'error');
}


  $account->content['user_picture'] = array(
    '#markup' => theme('user_picture', array('account' => $account)),
    '#weight' => -10,
  );
  if (!isset($account->content['summary'])) {
    $account->content['summary'] = array();
  }
  $account->content['summary'] += array(
    '#type' => 'user_profile_category',
    '#attributes' => array('class' => array('user-member')),
    '#weight' => 5,
    '#title' => t('History'),
  );
  $account->content['summary']['member_for'] = array(
    '#type' => 'user_profile_item',
    '#title' => t('Member for'),
    '#markup' => format_interval(REQUEST_TIME - $account->created),
  );
}

/**
 * Helper function to add default user account fields to user registration and edit form.
 *
 * @see user_account_form_validate()
 * @see user_validate_current_pass()
 * @see user_validate_picture()
 * @see user_validate_mail()
 */
function user_account_form(&$form, &$form_state) {

  global $user;

  $account = $form['#user'];
  $register = ($form['#user']->uid > 0 ? FALSE : TRUE);

  $admin = user_access('administer users');

  $form['#validate'][] = 'user_account_form_validate';

  // Account information.
  $form['account'] = array(
    '#type'   => 'container',
    '#weight' => -10,
  );
  
  
  // Only show name field on registration form or user can change own username.
  $form['account']['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#maxlength' => USERNAME_MAX_LENGTH,
    '#description' => t('Spaces are allowed; punctuation is not allowed except for periods, hyphens, apostrophes, and underscores.'),
    '#required' => TRUE,
    '#attributes' => array('class' => array('username')),
    '#default_value' => (!$register ? $account->name : ''),
    '#access' => ($register || ($user->uid == $account->uid && user_access('change own username')) || $admin),
    '#weight' => -10,
  );

     $form['account']['mail'] = array(
    '#type' => 'textfield',
    '#title' => t('E-mail address'),
    '#maxlength' => EMAIL_MAX_LENGTH,
    '#description' => t('A valid e-mail address. All e-mails from the system will be sent to this address. The e-mail address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by e-mail.'),
    '#required' => TRUE,
    '#default_value' => (!$register ? $account->mail : ''),
  );

  if ($register) {
  $form['account']['email'] = array(
    '#type' => 'textfield',
    '#title' => t('Confirm E-mail address'),
    '#maxlength' => EMAIL_MAX_LENGTH,
    //'#description' => t('Provide the same validation as password fields.'),
    '#attributes' => array('class' => array('confirmemail')),
    '#required' => TRUE,
    
    
  );
 }
  
  // Display password field only for existing users or when user is allowed to
  // assign a password during registration.
  //if (!$register) {
    $form['account']['pass'] = array(
    '#type' => 'password_confirm',
      '#size' => 25,
      '#description' => t('Remember this password as this is used to login and access the system.'),
    );
    // To skip the current password field, the user must have logged in via a
    // one-time link and have the token in the URL.
    $pass_reset = isset($_SESSION['pass_reset_' . $account->uid]) && isset($_GET['pass-reset-token']) && ($_GET['pass-reset-token'] == $_SESSION['pass_reset_' . $account->uid]);
    $protected_values = array();
    $current_pass_description = '';
    // The user may only change their own password without their current
    // password if they logged in via a one-time login link.
    if (!$pass_reset) {
      $protected_values['mail'] = $form['account']['mail']['#title'];
      $protected_values['pass'] = t('Password');
      $request_new = l(t('Request new password'), 'user/password', array('attributes' => array('title' => t('Request new password via e-mail.'))));
      $current_pass_description = t('Enter your current password to change the %mail or %pass. !request_new.', array('%mail' => $protected_values['mail'], '%pass' => $protected_values['pass'], '!request_new' => $request_new));
    }
    // The user must enter their current password to change to a new one.
    if (!$register) {
    if ($user->uid == $account->uid) {
      $form['account']['current_pass_required_values'] = array(
        '#type' => 'value',
        '#value' => $protected_values,
      );
      $form['account']['current_pass'] = array(
        '#type' => 'password',
        '#title' => t('Current password'),
        '#size' => 25,
        '#access' => !empty($protected_values),
        '#description' => $current_pass_description,
        '#weight' => -5,
        // Do not let web browsers remember this password, since we are trying
        // to confirm that the person submitting the form actually knows the
        // current one.
        '#attributes' => array('autocomplete' => 'off'),
      );
      $form['#validate'][] = 'user_validate_current_pass';
    }
  }
  elseif (!variable_get('user_email_verification', TRUE) || $admin) {
    $form['account']['pass'] = array(
      '#type' => 'password_confirm',
      '#size' => 25,
      '#description' => t('Provide a password for the new account in both fields.'),
      '#required' => TRUE,
    );
  }

  if ($admin) {
    $status = isset($account->status) ? $account->status : 1;
  }
  else {
    $status = $register ? variable_get('user_register', USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL) == USER_REGISTER_VISITORS : $account->status;
  }
  $form['account']['status'] = array(
    '#type' => 'radios',
    '#title' => t('Status'),
    '#default_value' => $status,
    '#options' => array(t('Blocked'), t('Active')),
    '#access' => $admin,
  );

  $roles = array_map('check_plain', user_roles(TRUE));
  // The disabled checkbox subelement for the 'authenticated user' role
  // must be generated separately and added to the checkboxes element,
  // because of a limitation in Form API not supporting a single disabled
  // checkbox within a set of checkboxes.
  // @todo This should be solved more elegantly. See issue #119038.
  $checkbox_authenticated = array(
    '#type' => 'checkbox',
    '#title' => $roles[DRUPAL_AUTHENTICATED_RID],
    '#default_value' => TRUE,
    '#disabled' => TRUE,
  );
  unset($roles[DRUPAL_AUTHENTICATED_RID]);
  $form['account']['roles'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Roles'),
    '#default_value' => (!$register && !empty($account->roles) ? array_keys(array_filter($account->roles)) : array()),
    '#options' => $roles,
    '#access' => $roles && user_access('administer permissions'),
    DRUPAL_AUTHENTICATED_RID => $checkbox_authenticated,
  );

  $form['account']['notify'] = array(
    '#type' => 'checkbox',
    '#title' => t('Notify user of new account'),
    '#access' => $register && $admin,
  );

  // Signature.
  $form['signature_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Signature settings'),
    '#weight' => 1,
    '#access' => (!$register && variable_get('user_signatures', 0)),
  );

  $form['signature_settings']['signature'] = array(
    '#type' => 'text_format',
    '#title' => t('Signature'),
    '#default_value' => isset($account->signature) ? $account->signature : '',
    '#description' => t('Your signature will be publicly displayed at the end of your comments.'),
    '#format' => isset($account->signature_format) ? $account->signature_format : NULL,
  );

  // Picture/avatar.
  $form['picture'] = array(
    '#type' => 'fieldset',
    '#title' => t('Picture'),
    '#weight' => 1,
    '#access' => (!$register && variable_get('user_pictures', 0)),
  );
  $form['picture']['picture'] = array(
    '#type' => 'value',
    '#value' => isset($account->picture) ? $account->picture : NULL,
  );
  $form['picture']['picture_current'] = array(
    '#markup' => theme('user_picture', array('account' => $account)),
  );
  $form['picture']['picture_delete'] = array(
    '#type' => 'checkbox',
    '#title' => t('Delete picture'),
    '#access' => !empty($account->picture->fid),
    '#description' => t('Check this box to delete your current picture.'),
  );
  $form['picture']['picture_upload'] = array(
    '#type' => 'file',
    '#title' => t('Upload picture'),
    '#size' => 48,
    '#description' => t('Your virtual face or picture. Pictures larger than @dimensions pixels will be scaled down.', array('@dimensions' => variable_get('user_picture_dimensions', '85x85'))) . ' ' . filter_xss_admin(variable_get('user_picture_guidelines', '')),
  );
  $form['#validate'][] = 'user_validate_picture';
}

/**
 * Form validation handler for the current password on the user_account_form().
 *
 * @see user_account_form()
 */
function user_validate_current_pass(&$form, &$form_state) {
  $account = $form['#user'];
  foreach ($form_state['values']['current_pass_required_values'] as $key => $name) {
    // This validation only works for required textfields (like mail) or
    // form values like password_confirm that have their own validation
    // that prevent them from being empty if they are changed.
    if ((strlen(trim($form_state['values'][$key])) > 0) && ($form_state['values'][$key] != $account->$key)) {
      require_once DRUPAL_ROOT . '/' . variable_get('password_inc', 'includes/password.inc');
      $current_pass_failed = strlen(trim($form_state['values']['current_pass'])) == 0 || !user_check_password($form_state['values']['current_pass'], $account);
      if ($current_pass_failed) {
        form_set_error('current_pass', t("Your current password is missing or incorrect; it's required to change the %name.", array('%name' => $name)));
        form_set_error($key);
      }
      // We only need to check the password once.
      break;
    }
  }
}

/**
 * Form validation handler for user_account_form().
 *
 * @see user_account_form()
 */
function user_account_form_validate($form, &$form_state) {
  //print_r($form_state);exit;
  $count= $form_state['values']['mail'];
  
  //echo $count['required'];exit;
  
  if($form_state['values']['field_country'] != 362)
  {
      //$form['field_email']['und']['0']['#required'] = TRUE;  

  	$form['#edit-field-student-id-und-0-value']['#required'] = TRUE;
  }
  
  if ($form['#user_category'] == 'account' || $form['#user_category'] == 'register') {

    $account = $form['#user'];
    // Validate new or changing username.
    if (isset($form_state['values']['name'])) {
      if ($error = user_validate_name($form_state['values']['name'])) {
        form_set_error('name', $error);
      }
      elseif ((bool) db_select('users')->fields('users', array('uid'))->condition('uid', $account->uid, '<>')->condition('name', db_like($form_state['values']['name']), 'LIKE')->range(0, 1)->execute()->fetchField()) {
        form_set_error('name', t('The name %name is already taken.', array('%name' => $form_state['values']['name'])));
      }
    }

    // Trim whitespace from mail, to prevent confusing 'e-mail not valid'
    // warnings often caused by cutting and pasting.
    $mail = trim($form_state['values']['mail']);
    form_set_value($form['account']['mail'], $mail, $form_state);

    // Validate the e-mail address, and check if it is taken by an existing user.
    if ($error = user_validate_mail($form_state['values']['mail'])) {
      form_set_error('mail', $error);
    }
    elseif ((bool) db_select('users')->fields('users', array('uid'))->condition('uid', $account->uid, '<>')->condition('mail', db_like($form_state['values']['mail']), 'LIKE')->range(0, 1)->execute()->fetchField()) {
      // Format error message dependent on whether the user is logged in or not.
      if ($GLOBALS['user']->uid) {
        form_set_error('mail', t('The e-mail address %email is already taken.', array('%email' => $form_state['values']['mail'])));
      }
      else {
        form_set_error('mail', t('The e-mail address %email is already registered. <a href="@password">Have you forgotten your password?</a>', array('%email' => $form_state['values']['mail'], '@password' => url('user/password'))));
      }
      
      
      
      
    }
    
    	if($form['#user_category'] != 'account')
  	{
	  	if($error = confirmation_email($form_state['values']['mail'], $form_state['values']['email']))
	    	{
	    		form_set_error('field_email', $error);
	    
	    	}
  	}
    
    

    // Make sure the signature isn't longer than the size of the database field.
    // Signatures are disabled by default, so make sure it exists first.
    if (isset($form_state['values']['signature'])) {
      // Move text format for user signature into 'signature_format'.
      $form_state['values']['signature_format'] = $form_state['values']['signature']['format'];
      // Move text value for user signature into 'signature'.
      $form_state['values']['signature'] = $form_state['values']['signature']['value'];

      $user_schema = drupal_get_schema('users');
      if (drupal_strlen($form_state['values']['signature']) > $user_schema['fields']['signature']['length']) {
        form_set_error('signature', t('The signature is too long: it must be %max characters or less.', array('%max' => $user_schema['fields']['signature']['length'])));
      }
    }
  }
  
  
  
}

/**
 * Implements hook_user_presave().
 */
function user_user_presave(&$edit, $account, $category) {
  if ($category == 'account' || $category == 'register') {
    if (!empty($edit['picture_upload'])) {
      $edit['picture'] = $edit['picture_upload'];
    }
    // Delete picture if requested, and if no replacement picture was given.
    elseif (!empty($edit['picture_delete'])) {
      $edit['picture'] = NULL;
    }
  }

  // Filter out roles with empty values to avoid granting extra roles when
  // processing custom form submissions.
  if (isset($edit['roles'])) {
    $edit['roles'] = array_filter($edit['roles']);
  }

  // Move account cancellation information into $user->data.
  foreach (array('user_cancel_method', 'user_cancel_notify') as $key) {
    if (isset($edit[$key])) {
      $edit['data'][$key] = $edit[$key];
    }
  }
}

/**
 * Implements hook_user_categories().
 */
function user_user_categories() {
  return array(array(
    'name' => 'account',
    'title' => t('Account settings'),
    'weight' => 1,
  ));
}

function user_login_block($form) {
  $form['#action'] = url(current_path(), array('query' => drupal_get_destination(), 'external' => FALSE));
  $form['#id'] = 'user-login-form';
  $form['#validate'] = user_login_default_validators();
  $form['#submit'][] = 'user_login_submit';
  $form['name'] = array('#type' => 'textfield',
    '#title' => t('Username'),
    '#maxlength' => USERNAME_MAX_LENGTH,
    '#size' => 15,
    '#required' => TRUE,
  );
  $form['pass'] = array('#type' => 'password',
    '#title' => t('Password'),
    '#size' => 15,
    '#required' => TRUE,
  );
  $form['actions'] = array('#type' => 'actions');
  
  $items = array();
  if (variable_get('user_register', USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL)) {
    $items[] = l(t('Create new account'), 'user/register', array('attributes' => array('title' => t('Create a new user account.'))));
  }
  $items[] = l(t('Request new password'), 'user/password', array('attributes' => array('title' => t('Request new password via e-mail.'))));
  $form['links'] = array('#markup' => theme('item_list', array('items' => $items)));
  return $form;
}

/**
 * Implements hook_block_info().
 */
function user_block_info() {
  global $user;

  $blocks['login']['info'] = t('User login');
  // Not worth caching.
  $blocks['login']['cache'] = DRUPAL_NO_CACHE;

  $blocks['new']['info'] = t('Who\'s new');
  $blocks['new']['properties']['administrative'] = TRUE;

  // Too dynamic to cache.
  $blocks['online']['info'] = t('Who\'s online');
  $blocks['online']['cache'] = DRUPAL_NO_CACHE;
  $blocks['online']['properties']['administrative'] = TRUE;

  return $blocks;
}

/**
 * Implements hook_block_configure().
 */
function user_block_configure($delta = '') {
  global $user;

  switch ($delta) {
    case 'new':
      $form['user_block_whois_new_count'] = array(
        '#type' => 'select',
        '#title' => t('Number of users to display'),
        '#default_value' => variable_get('user_block_whois_new_count', 5),
        '#options' => drupal_map_assoc(array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)),
      );
      return $form;

    case 'online':
      $period = drupal_map_assoc(array(30, 60, 120, 180, 300, 600, 900, 1800, 2700, 3600, 5400, 7200, 10800, 21600, 43200, 86400), 'format_interval');
      $form['user_block_seconds_online'] = array('#type' => 'select', '#title' => t('User activity'), '#default_value' => variable_get('user_block_seconds_online', 900), '#options' => $period, '#description' => t('A user is considered online for this long after they have last viewed a page.'));
      $form['user_block_max_list_count'] = array('#type' => 'select', '#title' => t('User list length'), '#default_value' => variable_get('user_block_max_list_count', 10), '#options' => drupal_map_assoc(array(0, 5, 10, 15, 20, 25, 30, 40, 50, 75, 100)), '#description' => t('Maximum number of currently online users to display.'));
      return $form;
  }
}

/**
 * Implements hook_block_save().
 */
function user_block_save($delta = '', $edit = array()) {
  global $user;

  switch ($delta) {
    case 'new':
      variable_set('user_block_whois_new_count', $edit['user_block_whois_new_count']);
      break;

    case 'online':
      variable_set('user_block_seconds_online', $edit['user_block_seconds_online']);
      variable_set('user_block_max_list_count', $edit['user_block_max_list_count']);
      break;
  }
}

/**
 * Implements hook_block_view().
 */
function user_block_view($delta = '') {
  global $user;

  $block = array();

  switch ($delta) {
    case 'login':
      // For usability's sake, avoid showing two login forms on one page.
      if (!$user->uid && !(arg(0) == 'user' && !is_numeric(arg(1)))) {

        $block['subject'] = t('User login');
        $block['content'] = drupal_get_form('user_login_block');
      }
      return $block;

    case 'new':
      if (user_access('access content')) {
        // Retrieve a list of new users who have subsequently accessed the site successfully.
        $items = db_query_range('SELECT uid, name FROM {users} WHERE status <> 0 AND access <> 0 ORDER BY created DESC', 0, variable_get('user_block_whois_new_count', 5))->fetchAll();
        $output = theme('user_list', array('users' => $items));

        $block['subject'] = t('Who\'s new');
        $block['content'] = $output;
      }
      return $block;

    case 'online':
      if (user_access('access content')) {
        // Count users active within the defined period.
        $interval = REQUEST_TIME - variable_get('user_block_seconds_online', 900);

        // Perform database queries to gather online user lists. We use s.timestamp
        // rather than u.access because it is much faster.
        $authenticated_count = db_query("SELECT COUNT(DISTINCT s.uid) FROM {sessions} s WHERE s.timestamp >= :timestamp AND s.uid > 0", array(':timestamp' => $interval))->fetchField();

        $output = '<p>' . format_plural($authenticated_count, 'There is currently 1 user online.', 'There are currently @count users online.') . '</p>';

        // Display a list of currently online users.
        $max_users = variable_get('user_block_max_list_count', 10);
        if ($authenticated_count && $max_users) {
          $items = db_query_range('SELECT u.uid, u.name, MAX(s.timestamp) AS max_timestamp FROM {users} u INNER JOIN {sessions} s ON u.uid = s.uid WHERE s.timestamp >= :interval AND s.uid > 0 GROUP BY u.uid, u.name ORDER BY max_timestamp DESC', 0, $max_users, array(':interval' => $interval))->fetchAll();
          $output .= theme('user_list', array('users' => $items));
        }

        $block['subject'] = t('Who\'s online');
        $block['content'] = $output;
      }
      return $block;
  }
}

/**
 * Process variables for user-picture.tpl.php.
 *
 * The $variables array contains the following arguments:
 * - $account: A user, node or comment object with 'name', 'uid' and 'picture'
 *   fields.
 *
 * @see user-picture.tpl.php
 */
function template_preprocess_user_picture(&$variables) {
  $variables['user_picture'] = '';
  if (variable_get('user_pictures', 0)) {
    $account = $variables['account'];
    if (!empty($account->picture)) {
      // @TODO: Ideally this function would only be passed file objects, but
      // since there's a lot of legacy code that JOINs the {users} table to
      // {node} or {comments} and passes the results into this function if we
      // a numeric value in the picture field we'll assume it's a file id
      // and load it for them. Once we've got user_load_multiple() and
      // comment_load_multiple() functions the user module will be able to load
      // the picture files in mass during the object's load process.
      if (is_numeric($account->picture)) {
        $account->picture = file_load($account->picture);
      }
      if (!empty($account->picture->uri)) {
        $filepath = $account->picture->uri;
      }
    }
    elseif (variable_get('user_picture_default', '')) {
      $filepath = variable_get('user_picture_default', '');
    }
    if (isset($filepath)) {
      $alt = t("@user's picture", array('@user' => format_username($account)));
      // If the image does not have a valid Drupal scheme (for eg. HTTP),
      // don't load image styles.
      if (module_exists('image') && file_valid_uri($filepath) && $style = variable_get('user_picture_style', '')) {
        $variables['user_picture'] = theme('image_style', array('style_name' => $style, 'path' => $filepath, 'alt' => $alt, 'title' => $alt));
      }
      else {
        $variables['user_picture'] = theme('image', array('path' => $filepath, 'alt' => $alt, 'title' => $alt));
      }
      if (!empty($account->uid) && user_access('access user profiles')) {
        $attributes = array('attributes' => array('title' => t('View user profile.')), 'html' => TRUE);
        $variables['user_picture'] = l($variables['user_picture'], "user/$account->uid", $attributes);
      }
    }
  }
}

/**
 * Returns HTML for a list of users.
 *
 * @param $variables
 *   An associative array containing:
 *   - users: An array with user objects. Should contain at least the name and
 *     uid.
 *   - title: (optional) Title to pass on to theme_item_list().
 *
 * @ingroup themeable
 */
function theme_user_list($variables) {
  $users = $variables['users'];
  $title = $variables['title'];
  $items = array();

  if (!empty($users)) {
    foreach ($users as $user) {
      $items[] = theme('username', array('account' => $user));
    }
  }
  return theme('item_list', array('items' => $items, 'title' => $title));
}

/**
 * Determines if the current user is anonymous.
 *
 * @return bool
 *   TRUE if the user is anonymous, FALSE if the user is authenticated.
 */
function user_is_anonymous() {
  // Menu administrators can see items for anonymous when administering.
  return !$GLOBALS['user']->uid || !empty($GLOBALS['menu_admin']);
}

/**
 * Determines if the current user is logged in.
 *
 * @return bool
 *   TRUE if the user is logged in, FALSE if the user is anonymous.
 */
function user_is_logged_in() {
  return (bool) $GLOBALS['user']->uid;
}

/**
 * Determines if the current user has access to the user registration page.
 *
 * @return bool
 *   TRUE if the user is not already logged in and can register for an account.
 */
function user_register_access() {
  return user_is_anonymous() && variable_get('user_register', USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL);
}

/**
 * User view access callback.
 *
 * @param $account
 *   Can either be a full user object or a $uid.
 */
function user_view_access($account) {
  $uid = is_object($account) ? $account->uid : (int) $account;

  // Never allow access to view the anonymous user account.
  if ($uid) {
    // Admins can view all, users can view own profiles at all times.
    if ($GLOBALS['user']->uid == $uid || user_access('administer users')) {
      return TRUE;
    }
    elseif (user_access('access user profiles')) {
      // At this point, load the complete account object.
      if (!is_object($account)) {
        $account = user_load($uid);
      }
      return (is_object($account) && $account->status);
    }
  }
  return FALSE;
}

/**
 * Access callback for user account editing.
 */
function user_edit_access($account) {
  return (($GLOBALS['user']->uid == $account->uid) || user_access('administer users')) && $account->uid > 0;
}

/**
 * Menu access callback; limit access to account cancellation pages.
 *
 * Limit access to users with the 'cancel account' permission or administrative
 * users, and prevent the anonymous user from cancelling the account.
 */
function user_cancel_access($account) {
  return ((($GLOBALS['user']->uid == $account->uid) && user_access('cancel account')) || user_access('administer users')) && $account->uid > 0;
}

/**
 * Implements hook_menu().
 */
function user_menu() {
  $items['user/autocomplete'] = array(
    'title' => 'User autocomplete',
    'page callback' => 'user_autocomplete',
    'access callback' => 'user_access',
    'access arguments' => array('access user profiles'),
    'type' => MENU_CALLBACK,
    'file' => 'user.pages.inc',
  );

  // Registration and login pages.
  $items['user'] = array(
    'title' => 'User account',
    'title callback' => 'user_menu_title',
    'page callback' => 'user_page',
    'access callback' => TRUE,
    'file' => 'user.pages.inc',
    'weight' => -10,
    'menu_name' => 'user-menu',
  );

  $items['user/login'] = array(
    'title' => 'Log in',
    'access callback' => 'user_is_anonymous',
    'type' => MENU_DEFAULT_LOCAL_TASK,
  );

  $items['user/register'] = array(
    'title' => 'Create new account',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('user_register_form'),
    'access callback' => 'user_register_access',
    'type' => MENU_LOCAL_TASK,
  );

  $items['user/password'] = array(
    'title' => 'Request new password',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('user_pass'),
    'access callback' => TRUE,
    'type' => MENU_LOCAL_TASK,
    'file' => 'user.pages.inc',
  );
  $items['user/reset/%/%/%'] = array(
    'title' => 'Reset password',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('user_pass_reset', 2, 3, 4),
    'access callback' => TRUE,
    'type' => MENU_CALLBACK,
    'file' => 'user.pages.inc',
  );

  $items['user/logout'] = array(
    'title' => 'Log out',
    'access callback' => 'user_is_logged_in',
    'page callback' => 'user_logout',
    'weight' => 10,
    'menu_name' => 'user-menu',
    'file' => 'user.pages.inc',
  );

  // User listing pages.
  $items['admin/people'] = array(
    'title' => 'People',
    'description' => 'Manage user accounts, roles, and permissions.',
    'page callback' => 'user_admin',
    'page arguments' => array('list'),
    'access arguments' => array('administer users'),
    'position' => 'left',
    'weight' => -4,
    'file' => 'user.admin.inc',
  );
  $items['admin/people/people'] = array(
    'title' => 'List',
    'description' => 'Find and manage people interacting with your site.',
    'access arguments' => array('administer users'),
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'weight' => -10,
    'file' => 'user.admin.inc',
  );

  // Permissions and role forms.
  $items['admin/people/permissions'] = array(
    'title' => 'Permissions',
    'description' => 'Determine access to features by selecting permissions for roles.',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('user_admin_permissions'),
    'access arguments' => array('administer permissions'),
    'file' => 'user.admin.inc',
    'type' => MENU_LOCAL_TASK,
  );
  $items['admin/people/permissions/list'] = array(
    'title' => 'Permissions',
    'description' => 'Determine access to features by selecting permissions for roles.',
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'weight' => -8,
  );
  $items['admin/people/permissions/roles'] = array(
    'title' => 'Roles',
    'description' => 'List, edit, or add user roles.',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('user_admin_roles'),
    'access arguments' => array('administer permissions'),
    'file' => 'user.admin.inc',
    'type' => MENU_LOCAL_TASK,
    'weight' => -5,
  );
  $items['admin/people/permissions/roles/edit/%user_role'] = array(
    'title' => 'Edit role',
    'page arguments' => array('user_admin_role', 5),
    'access callback' => 'user_role_edit_access',
    'access arguments' => array(5),
  );
  $items['admin/people/permissions/roles/delete/%user_role'] = array(
    'title' => 'Delete role',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('user_admin_role_delete_confirm', 5),
    'access callback' => 'user_role_edit_access',
    'access arguments' => array(5),
    'file' => 'user.admin.inc',
  );

  $items['admin/people/create'] = array(
    'title' => 'Add user',
    'page callback' => 'user_admin',
    'page arguments' => array('create'),
    'access arguments' => array('administer users'),
    'type' => MENU_LOCAL_ACTION,
    'file' => 'user.admin.inc',
  );

  // Administration pages.
  $items['admin/config/people'] = array(
    'title' => 'People',
    'description' => 'Configure user accounts.',
    'position' => 'left',
    'weight' => -20,
    'page callback' => 'system_admin_menu_block_page',
    'access arguments' => array('access administration pages'),
    'file' => 'system.admin.inc',
    'file path' => drupal_get_path('module', 'system'),
  );
  $items['admin/config/people/accounts'] = array(
    'title' => 'Account settings',
    'description' => 'Configure default behavior of users, including registration requirements, e-mails, fields, and user pictures.',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('user_admin_settings'),
    'access arguments' => array('administer users'),
    'file' => 'user.admin.inc',
    'weight' => -10,
  );
  $items['admin/config/people/accounts/settings'] = array(
    'title' => 'Settings',
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'weight' => -10,
  );

  $items['user/%user'] = array(
    'title' => 'My account',
    'title callback' => 'user_page_title',
    'title arguments' => array(1),
    'page callback' => 'user_view_page',
    'page arguments' => array(1),
    'access callback' => 'user_view_access',
    'access arguments' => array(1),
    // By assigning a different menu name, this item (and all registered child
    // paths) are no longer considered as children of 'user'. When accessing the
    // user account pages, the preferred menu link that is used to build the
    // active trail (breadcrumb) will be found in this menu (unless there is
    // more specific link), so the link to 'user' will not be in the breadcrumb.
    'menu_name' => 'navigation',
  );

  $items['user/%user/view'] = array(
    'title' => 'View',
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'weight' => -10,
  );

  $items['user/%user/cancel'] = array(
    'title' => 'Cancel account',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('user_cancel_confirm_form', 1),
    'access callback' => 'user_cancel_access',
    'access arguments' => array(1),
    'file' => 'user.pages.inc',
  );

  $items['user/%user/cancel/confirm/%/%'] = array(
    'title' => 'Confirm account cancellation',
    'page callback' => 'user_cancel_confirm',
    'page arguments' => array(1, 4, 5),
    'access callback' => 'user_cancel_access',
    'access arguments' => array(1),
    'file' => 'user.pages.inc',
  );

  $items['user/%user/edit'] = array(
    'title' => 'Edit',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('user_profile_form', 1),
    'access callback' => 'user_edit_access',
    'access arguments' => array(1),
    'type' => MENU_LOCAL_TASK,
    'file' => 'user.pages.inc',
  );
  
  $items['user/updateTerms'] = array(
    'page callback' => 'user_get_ajax', // Render HTML.
    'type' => MENU_CALLBACK,
    'access arguments' => array('access content'),
    'delivery callback' => 'user_ajax_callback',  // Magic goes here.
  );
  
  

  $items['user/%user_category/edit/account'] = array(
    'title' => 'Account',
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'load arguments' => array('%map', '%index'),
  );

  if (($categories = _user_categories()) && (count($categories) > 1)) {
    foreach ($categories as $key => $category) {
      // 'account' is already handled by the MENU_DEFAULT_LOCAL_TASK.
      if ($category['name'] != 'account') {
        $items['user/%user_category/edit/' . $category['name']] = array(
          'title callback' => 'check_plain',
          'title arguments' => array($category['title']),
          'page callback' => 'drupal_get_form',
          'page arguments' => array('user_profile_form', 1, 3),
          'access callback' => isset($category['access callback']) ? $category['access callback'] : 'user_edit_access',
          'access arguments' => isset($category['access arguments']) ? $category['access arguments'] : array(1),
          'type' => MENU_LOCAL_TASK,
          'weight' => $category['weight'],
          'load arguments' => array('%map', '%index'),
          'tab_parent' => 'user/%/edit',
          'file' => 'user.pages.inc',
        );
      }
    }
  }
  return $items;
}

function user_get_ajax(){

echo 'dddddddddddddddddddddddd';

}


/**
 * Implements hook_menu_site_status_alter().
 */
function user_menu_site_status_alter(&$menu_site_status, $path) {
  if ($menu_site_status == MENU_SITE_OFFLINE) {
    // If the site is offline, log out unprivileged users.
    if (user_is_logged_in() && !user_access('access site in maintenance mode')) {
      module_load_include('pages.inc', 'user', 'user');
      user_logout();
    }

    if (user_is_anonymous()) {
      switch ($path) {
        case 'user':
          // Forward anonymous user to login page.
          drupal_goto('user/login');
        case 'user/login':
        case 'user/password':
          // Disable offline mode.
          $menu_site_status = MENU_SITE_ONLINE;
          break;
        default:
          if (strpos($path, 'user/reset/') === 0) {
            // Disable offline mode.
            $menu_site_status = MENU_SITE_ONLINE;
          }
          break;
      }
    }
  }
  if (user_is_logged_in()) {
    if ($path == 'user/login') {
      // If user is logged in, redirect to 'user' instead of giving 403.
      drupal_goto('user');
    }
    if ($path == 'user/register') {
      // Authenticated user should be redirected to user edit page.
      drupal_goto('user/' . $GLOBALS['user']->uid . '/edit');
    }
  }
}

/**
 * Implements hook_menu_link_alter().
 */
function user_menu_link_alter(&$link) {
  // The path 'user' must be accessible for anonymous users, but only visible
  // for authenticated users. Authenticated users should see "My account", but
  // anonymous users should not see it at all. Therefore, invoke
  // user_translated_menu_link_alter() to conditionally hide the link.
  if ($link['link_path'] == 'user' && isset($link['module']) && $link['module'] == 'system') {
    $link['options']['alter'] = TRUE;
  }

  // Force the Logout link to appear on the top-level of 'user-menu' menu by
  // default (i.e., unless it has been customized).
  if ($link['link_path'] == 'user/logout' && isset($link['module']) && $link['module'] == 'system' && empty($link['customized'])) {
    $link['plid'] = 0;
  }
}

/**
 * Implements hook_translated_menu_link_alter().
 */
function user_translated_menu_link_alter(&$link) {
  // Hide the "User account" link for anonymous users.
  if ($link['link_path'] == 'user' && $link['module'] == 'system' && !$GLOBALS['user']->uid) {
    $link['hidden'] = 1;
  }
}

/**
 * Implements hook_admin_paths().
 */
function user_admin_paths() {
  $paths = array(
    'user/*/cancel' => TRUE,
    'user/*/edit' => TRUE,
    'user/*/edit/*' => TRUE,
  );
  return $paths;
}

/**
 * Returns $arg or the user ID of the current user if $arg is '%' or empty.
 *
 * Deprecated. Use %user_uid_optional instead.
 *
 * @todo D8: Remove.
 */
function user_uid_only_optional_to_arg($arg) {
  return user_uid_optional_to_arg($arg);
}

/**
 * Load either a specified or the current user account.
 *
 * @param $uid
 *   An optional user ID of the user to load. If not provided, the current
 *   user's ID will be used.
 * @return
 *   A fully-loaded $user object upon successful user load, FALSE if user
 *   cannot be loaded.
 *
 * @see user_load()
 * @todo rethink the naming of this in Drupal 8.
 */
function user_uid_optional_load($uid = NULL) {
  if (!isset($uid)) {
    $uid = $GLOBALS['user']->uid;
  }
  return user_load($uid);
}

/**
 * Return a user object after checking if any profile category in the path exists.
 */
function user_category_load($uid, &$map, $index) {
  static $user_categories, $accounts;

  // Cache $account - this load function will get called for each profile tab.
  if (!isset($accounts[$uid])) {
    $accounts[$uid] = user_load($uid);
  }
  $valid = TRUE;
  if ($account = $accounts[$uid]) {
    // Since the path is like user/%/edit/category_name, the category name will
    // be at a position 2 beyond the index corresponding to the % wildcard.
    $category_index = $index + 2;
    // Valid categories may contain slashes, and hence need to be imploded.
    $category_path = implode('/', array_slice($map, $category_index));
    if ($category_path) {
      // Check that the requested category exists.
      $valid = FALSE;
      if (!isset($user_categories)) {
        $user_categories = _user_categories();
      }
      foreach ($user_categories as $category) {
        if ($category['name'] == $category_path) {
          $valid = TRUE;
          // Truncate the map array in case the category name had slashes.
          $map = array_slice($map, 0, $category_index);
          // Assign the imploded category name to the last map element.
          $map[$category_index] = $category_path;
          break;
        }
      }
    }
  }
  return $valid ? $account : FALSE;
}

/**
 * Returns $arg or the user ID of the current user if $arg is '%' or empty.
 *
 * @todo rethink the naming of this in Drupal 8.
 */
function user_uid_optional_to_arg($arg) {
  // Give back the current user uid when called from eg. tracker, aka.
  // with an empty arg. Also use the current user uid when called from
  // the menu with a % for the current account link.
  return empty($arg) || $arg == '%' ? $GLOBALS['user']->uid : $arg;
}

/**
 * Menu item title callback for the 'user' path.
 *
 * Anonymous users should see "User account", but authenticated users are
 * expected to see "My account".
 */
function user_menu_title() {
  return user_is_logged_in() ? t('My account') : t('User account');
}

/**
 * Menu item title callback - use the user name.
 */
function user_page_title($account) {
  return is_object($account) ? format_username($account) : '';
}

/**
 * Discover which external authentication module(s) authenticated a username.
 *
 * @param $authname
 *   A username used by an external authentication module.
 * @return
 *   An associative array with module as key and username as value.
 */
function user_get_authmaps($authname = NULL) {
  $authmaps = db_query("SELECT module, authname FROM {authmap} WHERE authname = :authname", array(':authname' => $authname))->fetchAllKeyed();
  return count($authmaps) ? $authmaps : 0;
}

/**
 * Save mappings of which external authentication module(s) authenticated
 * a user. Maps external usernames to user ids in the users table.
 *
 * @param $account
 *   A user object.
 * @param $authmaps
 *   An associative array with a compound key and the username as the value.
 *   The key is made up of 'authname_' plus the name of the external authentication
 *   module.
 * @see user_external_login_register()
 */
function user_set_authmaps($account, $authmaps) {
  foreach ($authmaps as $key => $value) {
    $module = explode('_', $key, 2);
    if ($value) {
      db_merge('authmap')
        ->key(array(
          'uid' => $account->uid,
          'module' => $module[1],
        ))
        ->fields(array('authname' => $value))
        ->execute();
    }
    else {
      db_delete('authmap')
        ->condition('uid', $account->uid)
        ->condition('module', $module[1])
        ->execute();
    }
  }
}

/**
 * Form builder; the main user login form.
 *
 * @ingroup forms
 */
function user_login($form, &$form_state) {
  global $user;

  // If we are already logged on, go to the user page instead.
  if ($user->uid) {
    drupal_goto('user/' . $user->uid);
  }

  // Display login form:
  $form['name'] = array('#type' => 'textfield',
    '#title' => t('Username'),
    '#size' => 60,
    '#maxlength' => USERNAME_MAX_LENGTH,
    '#required' => TRUE,
  );

  $form['name']['#description'] = t('Enter your @s username.', array('@s' => variable_get('site_name', 'Drupal')));
  $form['pass'] = array('#type' => 'password',
    '#title' => t('Password'),
    '#description' => t('Enter the password that accompanies your username.'),
    '#required' => TRUE,
  );
  $form['#validate'] = user_login_default_validators();
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Log in'));

  return $form;
}

/**
 * Set up a series for validators which check for blocked users,
 * then authenticate against local database, then return an error if
 * authentication fails. Distributed authentication modules are welcome
 * to use hook_form_alter() to change this series in order to
 * authenticate against their user database instead of the local users
 * table. If a distributed authentication module is successful, it
 * should set $form_state['uid'] to a user ID.
 *
 * We use three validators instead of one since external authentication
 * modules usually only need to alter the second validator.
 *
 * @see user_login_name_validate()
 * @see user_login_authenticate_validate()
 * @see user_login_final_validate()
 * @return array
 *   A simple list of validate functions.
 */
function user_login_default_validators() {
  return array('user_login_name_validate', 'user_login_authenticate_validate', 'user_login_final_validate');
}

/**
 * A FAPI validate handler. Sets an error if supplied username has been blocked.
 */
function user_login_name_validate($form, &$form_state) {
  if (!empty($form_state['values']['name']) && user_is_blocked($form_state['values']['name'])) {
    // Blocked in user administration.
    form_set_error('name', t('The username %name has not been activated or is blocked.', array('%name' => $form_state['values']['name'])));
  }
}

/**
 * A validate handler on the login form. Check supplied username/password
 * against local users table. If successful, $form_state['uid']
 * is set to the matching user ID.
 */
function user_login_authenticate_validate($form, &$form_state) {
  $password = trim($form_state['values']['pass']);
  if (!empty($form_state['values']['name']) && strlen(trim($password)) > 0) {
    // Do not allow any login from the current user's IP if the limit has been
    // reached. Default is 50 failed attempts allowed in one hour. This is
    // independent of the per-user limit to catch attempts from one IP to log
    // in to many different user accounts.  We have a reasonably high limit
    // since there may be only one apparent IP for all users at an institution.
    if (!flood_is_allowed('failed_login_attempt_ip', variable_get('user_failed_login_ip_limit', 50), variable_get('user_failed_login_ip_window', 3600))) {
      $form_state['flood_control_triggered'] = 'ip';
      return;
    }
    $account = db_query("SELECT * FROM {users} WHERE name = :name AND status = 1", array(':name' => $form_state['values']['name']))->fetchObject();
    if ($account) {
      if (variable_get('user_failed_login_identifier_uid_only', FALSE)) {
        // Register flood events based on the uid only, so they apply for any
        // IP address. This is the most secure option.
        $identifier = $account->uid;
      }
      else {
        // The default identifier is a combination of uid and IP address. This
        // is less secure but more resistant to denial-of-service attacks that
        // could lock out all users with public user names.
        $identifier = $account->uid . '-' . ip_address();
      }
      $form_state['flood_control_user_identifier'] = $identifier;

      // Don't allow login if the limit for this user has been reached.
      // Default is to allow 5 failed attempts every 6 hours.
      if (!flood_is_allowed('failed_login_attempt_user', variable_get('user_failed_login_user_limit', 5), variable_get('user_failed_login_user_window', 21600), $identifier)) {
        $form_state['flood_control_triggered'] = 'user';
        return;
      }
    }
    // We are not limited by flood control, so try to authenticate.
    // Set $form_state['uid'] as a flag for user_login_final_validate().
    $form_state['uid'] = user_authenticate($form_state['values']['name'], $password);
  }
}

/**
 * The final validation handler on the login form.
 *
 * Sets a form error if user has not been authenticated, or if too many
 * logins have been attempted. This validation function should always
 * be the last one.
 */
function user_login_final_validate($form, &$form_state) {
  if (empty($form_state['uid'])) {
    // Always register an IP-based failed login event.
    flood_register_event('failed_login_attempt_ip', variable_get('user_failed_login_ip_window', 3600));
    // Register a per-user failed login event.
    if (isset($form_state['flood_control_user_identifier'])) {
      flood_register_event('failed_login_attempt_user', variable_get('user_failed_login_user_window', 21600), $form_state['flood_control_user_identifier']);
    }

    if (isset($form_state['flood_control_triggered'])) {
      if ($form_state['flood_control_triggered'] == 'user') {
        form_set_error('name', format_plural(variable_get('user_failed_login_user_limit', 5), 'Sorry, there has been more than one failed login attempt for this account. It is temporarily blocked. Try again later or <a href="@url">request a new password</a>.', 'Sorry, there have been more than @count failed login attempts for this account. It is temporarily blocked. Try again later or <a href="@url">request a new password</a>.', array('@url' => url('user/password'))));
      }
      else {
        // We did not find a uid, so the limit is IP-based.
        form_set_error('name', t('Sorry, too many failed login attempts from your IP address. This IP address is temporarily blocked. Try again later or <a href="@url">request a new password</a>.', array('@url' => url('user/password'))));
      }
    }
    else {
      // Use $form_state['input']['name'] here to guarantee that we send
      // exactly what the user typed in. $form_state['values']['name'] may have
      // been modified by validation handlers that ran earlier than this one.
      $query = isset($form_state['input']['name']) ? array('name' => $form_state['input']['name']) : array();
      form_set_error('name', t('Sorry, unrecognized username or password. <a href="@password">Have you forgotten your password?</a>', array('@password' => url('user/password', array('query' => $query)))));
      watchdog('user', 'Login attempt failed for %user.', array('%user' => $form_state['values']['name']));
    }
  }
  elseif (isset($form_state['flood_control_user_identifier'])) {
    // Clear past failures for this user so as not to block a user who might
    // log in and out more than once in an hour.
    flood_clear_event('failed_login_attempt_user', $form_state['flood_control_user_identifier']);
  }
}

/**
 * Try to validate the user's login credentials locally.
 *
 * @param $name
 *   User name to authenticate.
 * @param $password
 *   A plain-text password, such as trimmed text from form values.
 * @return
 *   The user's uid on success, or FALSE on failure to authenticate.
 */
function user_authenticate($name, $password) {
  $uid = FALSE;
  if (!empty($name) && strlen(trim($password)) > 0) {
    $account = user_load_by_name($name);
    if ($account) {
      // Allow alternate password hashing schemes.
      require_once DRUPAL_ROOT . '/' . variable_get('password_inc', 'includes/password.inc');
      if (user_check_password($password, $account)) {
        // Successful authentication.
        $uid = $account->uid;

        // Update user to new password scheme if needed.
        if (user_needs_new_hash($account)) {
          user_save($account, array('pass' => $password));
        }
      }
    }
  }
  return $uid;
}

/**
 * Finalize the login process. Must be called when logging in a user.
 *
 * The function records a watchdog message about the new session, saves the
 * login timestamp, calls hook_user_login(), and generates a new session.
 *
 * @param array $edit
 *   The array of form values submitted by the user.
 *
 * @see hook_user_login()
 */
function user_login_finalize(&$edit = array()) {
  global $user;
  watchdog('user', 'Session opened for %name.', array('%name' => $user->name));
  // Update the user table timestamp noting user has logged in.
  // This is also used to invalidate one-time login links.
  $user->login = REQUEST_TIME;
  db_update('users')
    ->fields(array('login' => $user->login))
    ->condition('uid', $user->uid)
    ->execute();

  // Regenerate the session ID to prevent against session fixation attacks.
  // This is called before hook_user in case one of those functions fails
  // or incorrectly does a redirect which would leave the old session in place.
  drupal_session_regenerate();
  
 // $termsCheked = db_query('SELECT field_terms_and_conditions_value FROM {field_data_field_terms_and_conditions} WHERE entity_id = :uid', array(':uid' => $user->uid))->fetchField();
  //print_r($termsCheked);exit;
  //if($termsCheked == 1){
 
 // drupal_goto('termsConditions');
  
 
  
 // }else{

  user_module_invoke('login', $edit, $user);
  //}
}

/**
 * Submit handler for the login form. Load $user object and perform standard login
 * tasks. The user is then redirected to the My Account page. Setting the
 * destination in the query string overrides the redirect.
 */
function user_login_submit($form, &$form_state) {

//exit;
  global $user;
  
  
  $user = user_load($form_state['uid']);
  
 
  $form_state['redirect'] = 'user/' . $user->uid;

  user_login_finalize($form_state);
  
}

/**
 * Helper function for authentication modules. Either logs in or registers
 * the current user, based on username. Either way, the global $user object is
 * populated and login tasks are performed.
 */
function user_external_login_register($name, $module) {
  $account = user_external_load($name);
  if (!$account) {
    // Register this new user.
    $userinfo = array(
      'name' => $name,
      'pass' => user_password(),
      'init' => $name,
      'status' => 1,
      'access' => REQUEST_TIME
    );
    $account = user_save(drupal_anonymous_user(), $userinfo);
    // Terminate if an error occurred during user_save().
    if (!$account) {
      drupal_set_message(t("Error saving user account."), 'error');
      return;
    }
    user_set_authmaps($account, array("authname_$module" => $name));
  }

  // Log user in.
  $form_state['uid'] = $account->uid;
  user_login_submit(array(), $form_state);
}

/**
 * Generates a unique URL for a user to login and reset their password.
 *
 * @param object $account
 *   An object containing the user account, which must contain at least the
 *   following properties:
 *   - uid: The user ID number.
 *   - login: The UNIX timestamp of the user's last login.
 *
 * @return
 *   A unique URL that provides a one-time log in for the user, from which
 *   they can change their password.
 */
function user_pass_reset_url($account) {
  $timestamp = REQUEST_TIME;
  return url("user/reset/$account->uid/$timestamp/" . user_pass_rehash($account->pass, $timestamp, $account->login, $account->uid), array('absolute' => TRUE));
}

/**
 * Generates a URL to confirm an account cancellation request.
 *
 * @param object $account
 *   The user account object, which must contain at least the following
 *   properties:
 *   - uid: The user ID number.
 *   - pass: The hashed user password string.
 *   - login: The UNIX timestamp of the user's last login.
 *
 * @return
 *   A unique URL that may be used to confirm the cancellation of the user
 *   account.
 *
 * @see user_mail_tokens()
 * @see user_cancel_confirm()
 */
function user_cancel_url($account) {
  $timestamp = REQUEST_TIME;
  return url("user/$account->uid/cancel/confirm/$timestamp/" . user_pass_rehash($account->pass, $timestamp, $account->login, $account->uid), array('absolute' => TRUE));
}

/**
 * Creates a unique hash value for use in time-dependent per-user URLs.
 *
 * This hash is normally used to build a unique and secure URL that is sent to
 * the user by email for purposes such as resetting the user's password. In
 * order to validate the URL, the same hash can be generated again, from the
 * same information, and compared to the hash value from the URL. The URL
 * normally contains both the time stamp and the numeric user ID. The login
 * timestamp and hashed password are retrieved from the database as necessary.
 * For a usage example, see user_cancel_url() and user_cancel_confirm().
 *
 * @param string $password
 *   The hashed user account password value.
 * @param int $timestamp
 *   A UNIX timestamp, typically REQUEST_TIME.
 * @param int $login
 *   The UNIX timestamp of the user's last login.
 * @param int $uid
 *   The user ID of the user account.
 *
 * @return
 *   A string that is safe for use in URLs and SQL statements.
 */
function user_pass_rehash($password, $timestamp, $login, $uid) {
  // Backwards compatibility: Try to determine a $uid if one was not passed.
  // (Since $uid is a required parameter to this function, a PHP warning will
  // be generated if it's not provided, which is an indication that the calling
  // code should be updated. But the code below will try to generate a correct
  // hash in the meantime.)
  if (!isset($uid)) {
    $uids = db_query_range('SELECT uid FROM {users} WHERE pass = :password AND login = :login AND uid > 0', 0, 2, array(':password' => $password, ':login' => $login))->fetchCol();
    // If exactly one user account matches the provided password and login
    // timestamp, proceed with that $uid.
    if (count($uids) == 1) {
      $uid = reset($uids);
    }
    // Otherwise there is no safe hash to return, so return a random string
    // that will never be treated as a valid token.
    else {
      return drupal_random_key();
    }
  }

  return drupal_hmac_base64($timestamp . $login . $uid, drupal_get_hash_salt() . $password);
}

/**
 * Cancel a user account.
 *
 * Since the user cancellation process needs to be run in a batch, either
 * Form API will invoke it, or batch_process() needs to be invoked after calling
 * this function and should define the path to redirect to.
 *
 * @param $edit
 *   An array of submitted form values.
 * @param $uid
 *   The user ID of the user account to cancel.
 * @param $method
 *   The account cancellation method to use.
 *
 * @see _user_cancel()
 */
function user_cancel($edit, $uid, $method) {
  global $user;

  $account = user_load($uid);

  if (!$account) {
    drupal_set_message(t('The user account %id does not exist.', array('%id' => $uid)), 'error');
    watchdog('user', 'Attempted to cancel non-existing user account: %id.', array('%id' => $uid), WATCHDOG_ERROR);
    return;
  }

  // Initialize batch (to set title).
  $batch = array(
    'title' => t('Cancelling account'),
    'operations' => array(),
  );
  batch_set($batch);

  // Modules use hook_user_delete() to respond to deletion.
  if ($method != 'user_cancel_delete') {
    // Allow modules to add further sets to this batch.
    module_invoke_all('user_cancel', $edit, $account, $method);
  }

  // Finish the batch and actually cancel the account.
  $batch = array(
    'title' => t('Cancelling user account'),
    'operations' => array(
      array('_user_cancel', array($edit, $account, $method)),
    ),
  );

  // After cancelling account, ensure that user is logged out.
  if ($account->uid == $user->uid) {
    // Batch API stores data in the session, so use the finished operation to
    // manipulate the current user's session id.
    $batch['finished'] = '_user_cancel_session_regenerate';
  }

  batch_set($batch);

  // Batch processing is either handled via Form API or has to be invoked
  // manually.
}

/**
 * Implements callback_batch_operation().
 *
 * Last step for cancelling a user account.
 *
 * Since batch and session API require a valid user account, the actual
 * cancellation of a user account needs to happen last.
 *
 * @see user_cancel()
 */
function _user_cancel($edit, $account, $method) {
  global $user;

  switch ($method) {
    case 'user_cancel_block':
    case 'user_cancel_block_unpublish':
    default:
      // Send account blocked notification if option was checked.
      if (!empty($edit['user_cancel_notify'])) {
        _user_mail_notify('status_blocked', $account);
      }
      user_save($account, array('status' => 0));
      drupal_set_message(t('%name has been disabled.', array('%name' => $account->name)));
      watchdog('user', 'Blocked user: %name %email.', array('%name' => $account->name, '%email' => '<' . $account->mail . '>'), WATCHDOG_NOTICE);
      break;

    case 'user_cancel_reassign':
    case 'user_cancel_delete':
      // Send account canceled notification if option was checked.
      if (!empty($edit['user_cancel_notify'])) {
        _user_mail_notify('status_canceled', $account);
      }
      user_delete($account->uid);
      drupal_set_message(t('%name has been deleted.', array('%name' => $account->name)));
      watchdog('user', 'Deleted user: %name %email.', array('%name' => $account->name, '%email' => '<' . $account->mail . '>'), WATCHDOG_NOTICE);
      break;
  }

  // After cancelling account, ensure that user is logged out. We can't destroy
  // their session though, as we might have information in it, and we can't
  // regenerate it because batch API uses the session ID, we will regenerate it
  // in _user_cancel_session_regenerate().
  if ($account->uid == $user->uid) {
    $user = drupal_anonymous_user();
  }

  // Clear the cache for anonymous users.
  cache_clear_all();
}

/**
 * Implements callback_batch_finished().
 *
 * Finished batch processing callback for cancelling a user account.
 *
 * @see user_cancel()
 */
function _user_cancel_session_regenerate() {
  // Regenerate the users session instead of calling session_destroy() as we
  // want to preserve any messages that might have been set.
  drupal_session_regenerate();
}

/**
 * Delete a user.
 *
 * @param $uid
 *   A user ID.
 */
function user_delete($uid) {
  user_delete_multiple(array($uid));
}

/**
 * Delete multiple user accounts.
 *
 * @param $uids
 *   An array of user IDs.
 */
function user_delete_multiple(array $uids) {
  if (!empty($uids)) {
    $accounts = user_load_multiple($uids, array());

    $transaction = db_transaction();
    try {
      foreach ($accounts as $uid => $account) {
        module_invoke_all('user_delete', $account);
        module_invoke_all('entity_delete', $account, 'user');
        field_attach_delete('user', $account);
        drupal_session_destroy_uid($account->uid);
      }

      db_delete('users')
        ->condition('uid', $uids, 'IN')
        ->execute();
      db_delete('users_roles')
        ->condition('uid', $uids, 'IN')
        ->execute();
      db_delete('authmap')
        ->condition('uid', $uids, 'IN')
        ->execute();
    }
    catch (Exception $e) {
      $transaction->rollback();
      watchdog_exception('user', $e);
      throw $e;
    }
    entity_get_controller('user')->resetCache();
  }
}

/**
 * Page callback wrapper for user_view().
 */
function user_view_page($account) {
  // An administrator may try to view a non-existent account,
  // so we give them a 404 (versus a 403 for non-admins).
  return is_object($account) ? user_view($account) : MENU_NOT_FOUND;
}

/**
 * Generate an array for rendering the given user.
 *
 * When viewing a user profile, the $page array contains:
 *
 * - $page['content']['Profile Category']:
 *   Profile categories keyed by their human-readable names.
 * - $page['content']['Profile Category']['profile_machine_name']:
 *   Profile fields keyed by their machine-readable names.
 * - $page['content']['user_picture']:
 *   User's rendered picture.
 * - $page['content']['summary']:
 *   Contains the default "History" profile data for a user.
 * - $page['content']['#account']:
 *   The user account of the profile being viewed.
 *
 * To theme user profiles, copy modules/user/user-profile.tpl.php
 * to your theme directory, and edit it as instructed in that file's comments.
 *
 * @param $account
 *   A user object.
 * @param $view_mode
 *   View mode, e.g. 'full'.
 * @param $langcode
 *   (optional) A language code to use for rendering. Defaults to the global
 *   content language of the current request.
 *
 * @return
 *   An array as expected by drupal_render().
 */
function user_view($account, $view_mode = 'full', $langcode = NULL) {
  if (!isset($langcode)) {
    $langcode = $GLOBALS['language_content']->language;
  }

  // Retrieve all profile fields and attach to $account->content.
  user_build_content($account, $view_mode, $langcode);

  $build = $account->content;
  // We don't need duplicate rendering info in account->content.
  unset($account->content);

  $build += array(
    '#theme' => 'user_profile',
    '#account' => $account,
    '#view_mode' => $view_mode,
    '#language' => $langcode,
  );

  // Allow modules to modify the structured user.
  $type = 'user';
  drupal_alter(array('user_view', 'entity_view'), $build, $type);

  return $build;
}

/**
 * Builds a structured array representing the profile content.
 *
 * @param $account
 *   A user object.
 * @param $view_mode
 *   View mode, e.g. 'full'.
 * @param $langcode
 *   (optional) A language code to use for rendering. Defaults to the global
 *   content language of the current request.
 */
function user_build_content($account, $view_mode = 'full', $langcode = NULL) {
  if (!isset($langcode)) {
    $langcode = $GLOBALS['language_content']->language;
  }

  // Remove previously built content, if exists.
  $account->content = array();

  // Allow modules to change the view mode.
  $view_mode = key(entity_view_mode_prepare('user', array($account->uid => $account), $view_mode, $langcode));

  // Build fields content.
  field_attach_prepare_view('user', array($account->uid => $account), $view_mode, $langcode);
  entity_prepare_view('user', array($account->uid => $account), $langcode);
  $account->content += field_attach_view('user', $account, $view_mode, $langcode);

  // Populate $account->content with a render() array.
  module_invoke_all('user_view', $account, $view_mode, $langcode);
  module_invoke_all('entity_view', $account, 'user', $view_mode, $langcode);

  // Make sure the current view mode is stored if no module has already
  // populated the related key.
  $account->content += array('#view_mode' => $view_mode);
}

/**
 * Implements hook_mail().
 */
function user_mail($key, &$message, $params) {
//print_r($key);exit;
  $language = $message['language'];
  $variables = array('user' => $params['account']);
 
  $message['subject'] .= _user_mail_text($key . '_subject', $language, $variables);
  $message['body'][] = _user_mail_text($key . '_body', $language, $variables);
}

/**
 * Returns a mail string for a variable name.
 *
 * Used by user_mail() and the settings forms to retrieve strings.
 */
function _user_mail_text($key, $language = NULL, $variables = array(), $replace = TRUE) {
  $langcode = isset($language) ? $language->language : NULL;

  if ($admin_setting = variable_get('user_mail_' . $key, FALSE)) {
    // An admin setting overrides the default string.
    $text = $admin_setting;
  }
  else {
    // No override, return default string.
    switch ($key) {
      case 'register_no_approval_required_subject':
        $text = t('Account details for [user:name] at [site:name]', array(), array('langcode' => $langcode));
        break;
      case 'register_no_approval_required_body':
        $text = t("[user:name],

Thank you for registering at [site:name]. You may now log in by clicking this link or copying and pasting it to your browser:

[user:one-time-login-url]

This link can only be used once to log in and will lead you to a page where you can set your password.

After setting your password, you will be able to log in at [site:login-url] in the future using:

username: [user:name]
password: Your password

--  [site:name] team", array(), array('langcode' => $langcode));
        break;

      case 'register_admin_created_subject':
        $text = t('An administrator created an account for you at [site:name]', array(), array('langcode' => $langcode));
        break;
      case 'register_admin_created_body':
        $text = t("[user:name],

A site administrator at [site:name] has created an account for you. You may now log in by clicking this link or copying and pasting it to your browser:

[user:one-time-login-url]

This link can only be used once to log in and will lead you to a page where you can set your password.

After setting your password, you will be able to log in at [site:login-url] in the future using:

username: [user:name]
password: Your password

--  [site:name] team", array(), array('langcode' => $langcode));
        break;

      case 'register_pending_approval_subject':
      case 'register_pending_approval_admin_subject':
        $text = t('Account details for [user:name] at [site:name] (pending admin approval)', array(), array('langcode' => $langcode));
        break;
      case 'register_pending_approval_body':
        $text = t("[user:name],

Thank you for registering at [site:name]. Your application for an account is currently pending approval. Once it has been approved, you will receive another e-mail containing information about how to log in, set your password, and other details.


--  [site:name] team", array(), array('langcode' => $langcode));
        break;
      case 'register_pending_approval_admin_body':
        $text = t("[user:name] has applied for an account.

[user:edit-url]", array(), array('langcode' => $langcode));
        break;

      case 'password_reset_subject':
        $text = t('Replacement login information for [user:name] at [site:name]', array(), array('langcode' => $langcode));
        break;
      case 'password_reset_body':
        $text = t("[user:name],

A request to reset the password for your account has been made at [site:name].

You may now log in by clicking this link or copying and pasting it to your browser:

[user:one-time-login-url]

This link can only be used once to log in and will lead you to a page where you can set your password. It expires after one day and nothing will happen if it's not used.

--  [site:name] team", array(), array('langcode' => $langcode));
        break;

      case 'status_activated_subject':
        $text = t('Account details for [user:name] at [site:name] (approved)', array(), array('langcode' => $langcode));
        break;
      case 'status_activated_body':
        $text = t("[user:name],

Your account at [site:name] has been activated.

You may now log in by clicking this link or copying and pasting it into your browser:

[user:one-time-login-url]

This link can only be used once to log in and will lead you to a page where you can set your password.

After setting your password, you will be able to log in at [site:login-url] in the future using:

username: [user:name]
password: [user:pass]

--  [site:name] team", array(), array('langcode' => $langcode));
        break;

      case 'status_blocked_subject':
        $text = t('Account details for [user:name] at [site:name] (blocked)', array(), array('langcode' => $langcode));
        break;
      case 'status_blocked_body':
        $text = t("[user:name],

Your account on [site:name] has been blocked.

--  [site:name] team", array(), array('langcode' => $langcode));
        break;

      case 'cancel_confirm_subject':
        $text = t('Account cancellation request for [user:name] at [site:name]', array(), array('langcode' => $langcode));
        break;
      case 'cancel_confirm_body':
        $text = t("[user:name],

A request to cancel your account has been made at [site:name].

You may now cancel your account on [site:url-brief] by clicking this link or copying and pasting it into your browser:

[user:cancel-url]

NOTE: The cancellation of your account is not reversible.

This link expires in one day and nothing will happen if it is not used.

--  [site:name] team", array(), array('langcode' => $langcode));
        break;

      case 'status_canceled_subject':
        $text = t('Account details for [user:name] at [site:name] (canceled)', array(), array('langcode' => $langcode));
        break;
      case 'status_canceled_body':
        $text = t("[user:name],

Your account on [site:name] has been canceled.

--  [site:name] team", array(), array('langcode' => $langcode));
        break;
    }
  }

  if ($replace) {
    // We do not sanitize the token replacement, since the output of this
    // replacement is intended for an e-mail message, not a web browser.
    return token_replace($text, $variables, array('language' => $language, 'callback' => 'user_mail_tokens', 'sanitize' => FALSE, 'clear' => TRUE));
  }

  return $text;
}

/**
 * Token callback to add unsafe tokens for user mails.
 *
 * This function is used by the token_replace() call at the end of
 * _user_mail_text() to set up some additional tokens that can be
 * used in email messages generated by user_mail().
 *
 * @param $replacements
 *   An associative array variable containing mappings from token names to
 *   values (for use with strtr()).
 * @param $data
 *   An associative array of token replacement values. If the 'user' element
 *   exists, it must contain a user account object with the following
 *   properties:
 *   - login: The UNIX timestamp of the user's last login.
 *   - pass: The hashed account login password.
 * @param $options
 *   Unused parameter required by the token_replace() function.
 */
function user_mail_tokens(&$replacements, $data, $options) {
  if (isset($data['user'])) {
    $replacements['[user:one-time-login-url]'] = user_pass_reset_url($data['user']);
    $replacements['[user:cancel-url]'] = user_cancel_url($data['user']);
  }
}

/*** Administrative features ***********************************************/

/**
 * Retrieve an array of roles matching specified conditions.
 *
 * @param $membersonly
 *   Set this to TRUE to exclude the 'anonymous' role.
 * @param $permission
 *   A string containing a permission. If set, only roles containing that
 *   permission are returned.
 *
 * @return
 *   An associative array with the role id as the key and the role name as
 *   value.
 */
function user_roles($membersonly = FALSE, $permission = NULL) {
  $query = db_select('role', 'r');
  $query->addTag('translatable');
  $query->fields('r', array('rid', 'name'));
  $query->orderBy('weight');
  $query->orderBy('name');
  if (!empty($permission)) {
    $query->innerJoin('role_permission', 'p', 'r.rid = p.rid');
    $query->condition('p.permission', $permission);
  }
  $result = $query->execute();

  $roles = array();
  foreach ($result as $role) {
    switch ($role->rid) {
      // We only translate the built in role names
      case DRUPAL_ANONYMOUS_RID:
        if (!$membersonly) {
          $roles[$role->rid] = t($role->name);
        }
        break;
      case DRUPAL_AUTHENTICATED_RID:
        $roles[$role->rid] = t($role->name);
        break;
      default:
        $roles[$role->rid] = $role->name;
    }
  }

  return $roles;
}

/**
 * Fetches a user role by role ID.
 *
 * @param $rid
 *   An integer representing the role ID.
 *
 * @return
 *   A fully-loaded role object if a role with the given ID exists, or FALSE
 *   otherwise.
 *
 * @see user_role_load_by_name()
 */
function user_role_load($rid) {
  return db_select('role', 'r')
    ->fields('r')
    ->condition('rid', $rid)
    ->execute()
    ->fetchObject();
}

/**
 * Fetches a user role by role name.
 *
 * @param $role_name
 *   A string representing the role name.
 *
 * @return
 *   A fully-loaded role object if a role with the given name exists, or FALSE
 *   otherwise.
 *
 * @see user_role_load()
 */
function user_role_load_by_name($role_name) {
  return db_select('role', 'r')
    ->fields('r')
    ->condition('name', $role_name)
    ->execute()
    ->fetchObject();
}

/**
 * Save a user role to the database.
 *
 * @param $role
 *   A role object to modify or add. If $role->rid is not specified, a new
 *   role will be created.
 * @return
 *   Status constant indicating if role was created or updated.
 *   Failure to write the user role record will return FALSE. Otherwise.
 *   SAVED_NEW or SAVED_UPDATED is returned depending on the operation
 *   performed.
 */
function user_role_save($role) {
  if ($role->name) {
    // Prevent leading and trailing spaces in role names.
    $role->name = trim($role->name);
  }
  if (!isset($role->weight)) {
    // Set a role weight to make this new role last.
    $query = db_select('role');
    $query->addExpression('MAX(weight)');
    $role->weight = $query->execute()->fetchField() + 1;
  }

  // Let modules modify the user role before it is saved to the database.
  module_invoke_all('user_role_presave', $role);

  if (!empty($role->rid) && $role->name) {
    $status = drupal_write_record('role', $role, 'rid');
    module_invoke_all('user_role_update', $role);
  }
  else {
    $status = drupal_write_record('role', $role);
    module_invoke_all('user_role_insert', $role);
  }

  // Clear the user access cache.
  drupal_static_reset('user_access');
  drupal_static_reset('user_role_permissions');

  return $status;
}

/**
 * Delete a user role from database.
 *
 * @param $role
 *   A string with the role name, or an integer with the role ID.
 */
function user_role_delete($role) {
  if (is_int($role)) {
    $role = user_role_load($role);
  }
  else {
    $role = user_role_load_by_name($role);
  }

  // If this is the administrator role, delete the user_admin_role variable.
  if ($role->rid == variable_get('user_admin_role')) {
    variable_del('user_admin_role');
  }

  db_delete('role')
    ->condition('rid', $role->rid)
    ->execute();
  db_delete('role_permission')
    ->condition('rid', $role->rid)
    ->execute();
  // Update the users who have this role set:
  db_delete('users_roles')
    ->condition('rid', $role->rid)
    ->execute();

  module_invoke_all('user_role_delete', $role);

  // Clear the user access cache.
  drupal_static_reset('user_access');
  drupal_static_reset('user_role_permissions');
}

/**
 * Menu access callback for user role editing.
 */
function user_role_edit_access($role) {
  // Prevent the system-defined roles from being altered or removed.
  if ($role->rid == DRUPAL_ANONYMOUS_RID || $role->rid == DRUPAL_AUTHENTICATED_RID) {
    return FALSE;
  }

  return user_access('administer permissions');
}

/**
 * Determine the modules that permissions belong to.
 *
 * @return
 *   An associative array in the format $permission => $module.
 */
function user_permission_get_modules() {
  $permissions = array();
  foreach (module_implements('permission') as $module) {
    $perms = module_invoke($module, 'permission');
    foreach ($perms as $key => $value) {
      $permissions[$key] = $module;
    }
  }
  return $permissions;
}

/**
 * Change permissions for a user role.
 *
 * This function may be used to grant and revoke multiple permissions at once.
 * For example, when a form exposes checkboxes to configure permissions for a
 * role, the form submit handler may directly pass the submitted values for the
 * checkboxes form element to this function.
 *
 * @param $rid
 *   The ID of a user role to alter.
 * @param $permissions
 *   An associative array, where the key holds the permission name and the value
 *   determines whether to grant or revoke that permission. Any value that
 *   evaluates to TRUE will cause the permission to be granted. Any value that
 *   evaluates to FALSE will cause the permission to be revoked.
 *   @code
 *     array(
 *       'administer nodes' => 0,                // Revoke 'administer nodes'
 *       'administer blocks' => FALSE,           // Revoke 'administer blocks'
 *       'access user profiles' => 1,            // Grant 'access user profiles'
 *       'access content' => TRUE,               // Grant 'access content'
 *       'access comments' => 'access comments', // Grant 'access comments'
 *     )
 *   @endcode
 *   Existing permissions are not changed, unless specified in $permissions.
 *
 * @see user_role_grant_permissions()
 * @see user_role_revoke_permissions()
 */
function user_role_change_permissions($rid, array $permissions = array()) {
  // Grant new permissions for the role.
  $grant = array_filter($permissions);
  if (!empty($grant)) {
    user_role_grant_permissions($rid, array_keys($grant));
  }
  // Revoke permissions for the role.
  $revoke = array_diff_assoc($permissions, $grant);
  if (!empty($revoke)) {
    user_role_revoke_permissions($rid, array_keys($revoke));
  }
}

/**
 * Grant permissions to a user role.
 *
 * @param $rid
 *   The ID of a user role to alter.
 * @param $permissions
 *   A list of permission names to grant.
 *
 * @see user_role_change_permissions()
 * @see user_role_revoke_permissions()
 */
function user_role_grant_permissions($rid, array $permissions = array()) {
  $modules = user_permission_get_modules();
  // Grant new permissions for the role.
  foreach ($permissions as $name) {
    db_merge('role_permission')
      ->key(array(
        'rid' => $rid,
        'permission' => $name,
      ))
      ->fields(array(
        'module' => $modules[$name],
      ))
      ->execute();
  }

  // Clear the user access cache.
  drupal_static_reset('user_access');
  drupal_static_reset('user_role_permissions');
}

/**
 * Revoke permissions from a user role.
 *
 * @param $rid
 *   The ID of a user role to alter.
 * @param $permissions
 *   A list of permission names to revoke.
 *
 * @see user_role_change_permissions()
 * @see user_role_grant_permissions()
 */
function user_role_revoke_permissions($rid, array $permissions = array()) {
  // Revoke permissions for the role.
  db_delete('role_permission')
    ->condition('rid', $rid)
    ->condition('permission', $permissions, 'IN')
    ->execute();

  // Clear the user access cache.
  drupal_static_reset('user_access');
  drupal_static_reset('user_role_permissions');
}

/**
 * Implements hook_user_operations().
 */
function user_user_operations($form = array(), $form_state = array()) {
  $operations = array(
    'unblock' => array(
      'label' => t('Unblock the selected users'),
      'callback' => 'user_user_operations_unblock',
    ),
    'block' => array(
      'label' => t('Block the selected users'),
      'callback' => 'user_user_operations_block',
    ),
    'cancel' => array(
      'label' => t('Cancel the selected user accounts'),
    ),
  );

  if (user_access('administer permissions')) {
    $roles = user_roles(TRUE);
    unset($roles[DRUPAL_AUTHENTICATED_RID]);  // Can't edit authenticated role.

    $add_roles = array();
    foreach ($roles as $key => $value) {
      $add_roles['add_role-' . $key] = $value;
    }

    $remove_roles = array();
    foreach ($roles as $key => $value) {
      $remove_roles['remove_role-' . $key] = $value;
    }

    if (count($roles)) {
      $role_operations = array(
        t('Add a role to the selected users') => array(
          'label' => $add_roles,
        ),
        t('Remove a role from the selected users') => array(
          'label' => $remove_roles,
        ),
      );

      $operations += $role_operations;
    }
  }

  // If the form has been posted, we need to insert the proper data for
  // role editing if necessary.
  if (!empty($form_state['submitted'])) {
    $operation_rid = explode('-', $form_state['values']['operation']);
    $operation = $operation_rid[0];
    if ($operation == 'add_role' || $operation == 'remove_role') {
      $rid = $operation_rid[1];
      if (user_access('administer permissions')) {
        $operations[$form_state['values']['operation']] = array(
          'callback' => 'user_multiple_role_edit',
          'callback arguments' => array($operation, $rid),
        );
      }
      else {
        watchdog('security', 'Detected malicious attempt to alter protected user fields.', array(), WATCHDOG_WARNING);
        return;
      }
    }
  }

  return $operations;
}

/**
 * Callback function for admin mass unblocking users.
 */
function user_user_operations_unblock($accounts) {
  $accounts = user_load_multiple($accounts);
  foreach ($accounts as $account) {
    // Skip unblocking user if they are already unblocked.
    if ($account !== FALSE && $account->status == 0) {
      user_save($account, array('status' => 1));
    }
  }
}

/**
 * Callback function for admin mass blocking users.
 */
function user_user_operations_block($accounts) {
  $accounts = user_load_multiple($accounts);
  foreach ($accounts as $account) {
    // Skip blocking user if they are already blocked.
    if ($account !== FALSE && $account->status == 1) {
      // For efficiency manually save the original account before applying any
      // changes.
      $account->original = clone $account;
      user_save($account, array('status' => 0));
    }
  }
}

/**
 * Callback function for admin mass adding/deleting a user role.
 */
function user_multiple_role_edit($accounts, $operation, $rid) {
  // The role name is not necessary as user_save() will reload the user
  // object, but some modules' hook_user() may look at this first.
  $role_name = db_query('SELECT name FROM {role} WHERE rid = :rid', array(':rid' => $rid))->fetchField();

  switch ($operation) {
    case 'add_role':
      $accounts = user_load_multiple($accounts);
      foreach ($accounts as $account) {
        // Skip adding the role to the user if they already have it.
        if ($account !== FALSE && !isset($account->roles[$rid])) {
          $roles = $account->roles + array($rid => $role_name);
          // For efficiency manually save the original account before applying
          // any changes.
          $account->original = clone $account;
          user_save($account, array('roles' => $roles));
        }
      }
      break;
    case 'remove_role':
      $accounts = user_load_multiple($accounts);
      foreach ($accounts as $account) {
        // Skip removing the role from the user if they already don't have it.
        if ($account !== FALSE && isset($account->roles[$rid])) {
          $roles = array_diff($account->roles, array($rid => $role_name));
          // For efficiency manually save the original account before applying
          // any changes.
          $account->original = clone $account;
          user_save($account, array('roles' => $roles));
        }
      }
      break;
  }
}

function user_multiple_cancel_confirm($form, &$form_state) {
  $edit = $form_state['input'];

  $form['accounts'] = array('#prefix' => '<ul>', '#suffix' => '</ul>', '#tree' => TRUE);
  $accounts = user_load_multiple(array_keys(array_filter($edit['accounts'])));
  foreach ($accounts as $uid => $account) {
    // Prevent user 1 from being canceled.
    if ($uid <= 1) {
      continue;
    }
    $form['accounts'][$uid] = array(
      '#type' => 'hidden',
      '#value' => $uid,
      '#prefix' => '<li>',
      '#suffix' => check_plain($account->name) . "</li>\n",
    );
  }

  // Output a notice that user 1 cannot be canceled.
  if (isset($accounts[1])) {
    $redirect = (count($accounts) == 1);
    $message = t('The user account %name cannot be cancelled.', array('%name' => $accounts[1]->name));
    drupal_set_message($message, $redirect ? 'error' : 'warning');
    // If only user 1 was selected, redirect to the overview.
    if ($redirect) {
      drupal_goto('admin/people');
    }
  }

  $form['operation'] = array('#type' => 'hidden', '#value' => 'cancel');

  module_load_include('inc', 'user', 'user.pages');
  $form['user_cancel_method'] = array(
    '#type' => 'item',
    '#title' => t('When cancelling these accounts'),
  );
  $form['user_cancel_method'] += user_cancel_methods();
  // Remove method descriptions.
  foreach (element_children($form['user_cancel_method']) as $element) {
    unset($form['user_cancel_method'][$element]['#description']);
  }

  // Allow to send the account cancellation confirmation mail.
  $form['user_cancel_confirm'] = array(
    '#type' => 'checkbox',
    '#title' => t('Require e-mail confirmation to cancel account.'),
    '#default_value' => FALSE,
    '#description' => t('When enabled, the user must confirm the account cancellation via e-mail.'),
  );
  // Also allow to send account canceled notification mail, if enabled.
  $form['user_cancel_notify'] = array(
    '#type' => 'checkbox',
    '#title' => t('Notify user when account is canceled.'),
    '#default_value' => FALSE,
    '#access' => variable_get('user_mail_status_canceled_notify', FALSE),
    '#description' => t('When enabled, the user will receive an e-mail notification after the account has been cancelled.'),
  );

  return confirm_form($form,
                      t('Are you sure you want to cancel these user accounts?'),
                      'admin/people', t('This action cannot be undone.'),
                      t('Cancel accounts'), t('Cancel'));
}

/**
 * Submit handler for mass-account cancellation form.
 *
 * @see user_multiple_cancel_confirm()
 * @see user_cancel_confirm_form_submit()
 */
function user_multiple_cancel_confirm_submit($form, &$form_state) {
  global $user;

  if ($form_state['values']['confirm']) {
    foreach ($form_state['values']['accounts'] as $uid => $value) {
      // Prevent programmatic form submissions from cancelling user 1.
      if ($uid <= 1) {
        continue;
      }
      // Prevent user administrators from deleting themselves without confirmation.
      if ($uid == $user->uid) {
        $admin_form_state = $form_state;
        unset($admin_form_state['values']['user_cancel_confirm']);
        $admin_form_state['values']['_account'] = $user;
        user_cancel_confirm_form_submit(array(), $admin_form_state);
      }
      else {
        user_cancel($form_state['values'], $uid, $form_state['values']['user_cancel_method']);
      }
    }
  }
  $form_state['redirect'] = 'admin/people';
}

/**
 * Retrieve a list of all user setting/information categories and sort them by weight.
 */
function _user_categories() {
  $categories = module_invoke_all('user_categories');
  usort($categories, '_user_sort');

  return $categories;
}

function _user_sort($a, $b) {
  $a = (array) $a + array('weight' => 0, 'title' => '');
  $b = (array) $b + array('weight' => 0, 'title' => '');
  return $a['weight'] < $b['weight'] ? -1 : ($a['weight'] > $b['weight'] ? 1 : ($a['title'] < $b['title'] ? -1 : 1));
}

/**
 * List user administration filters that can be applied.
 */
function user_filters() {
  // Regular filters
  $filters = array();
  $roles = user_roles(TRUE);
  unset($roles[DRUPAL_AUTHENTICATED_RID]); // Don't list authorized role.
  if (count($roles)) {
    $filters['role'] = array(
      'title' => t('role'),
      'field' => 'ur.rid',
      'options' => array(
        '[any]' => t('any'),
      ) + $roles,
    );
  }

  $options = array();
  foreach (module_implements('permission') as $module) {
    $function = $module . '_permission';
    if ($permissions = $function()) {
      asort($permissions);
      foreach ($permissions as $permission => $description) {
        $options[t('@module module', array('@module' => $module))][$permission] = t($permission);
      }
    }
  }
  ksort($options);
  $filters['permission'] = array(
    'title' => t('permission'),
    'options' => array(
      '[any]' => t('any'),
    ) + $options,
  );

  $filters['status'] = array(
    'title' => t('status'),
    'field' => 'u.status',
    'options' => array(
      '[any]' => t('any'),
      1 => t('active'),
      0 => t('blocked'),
    ),
  );
  return $filters;
}

/**
 * Extends a query object for user administration filters based on session.
 *
 * @param $query
 *   Query object that should be filtered.
 */
function user_build_filter_query(SelectQuery $query) {
  $filters = user_filters();
  // Extend Query with filter conditions.
  foreach (isset($_SESSION['user_overview_filter']) ? $_SESSION['user_overview_filter'] : array() as $filter) {
    list($key, $value) = $filter;
    // This checks to see if this permission filter is an enabled permission for
    // the authenticated role. If so, then all users would be listed, and we can
    // skip adding it to the filter query.
    if ($key == 'permission') {
      $account = new stdClass();
      $account->uid = 'user_filter';
      $account->roles = array(DRUPAL_AUTHENTICATED_RID => 1);
      if (user_access($value, $account)) {
        continue;
      }
      $users_roles_alias = $query->join('users_roles', 'ur', '%alias.uid = u.uid');
      $permission_alias = $query->join('role_permission', 'p', $users_roles_alias . '.rid = %alias.rid');
      $query->condition($permission_alias . '.permission', $value);
    }
    elseif ($key == 'role') {
      $users_roles_alias = $query->join('users_roles', 'ur', '%alias.uid = u.uid');
      $query->condition($users_roles_alias . '.rid' , $value);
    }
    else {
      $query->condition($filters[$key]['field'], $value);
    }
  }
}

/**
 * Implements hook_comment_view().
 */
function user_comment_view($comment) {
  if (variable_get('user_signatures', 0) && !empty($comment->signature)) {
    // @todo This alters and replaces the original object value, so a
    //   hypothetical process of loading, viewing, and saving will hijack the
    //   stored data. Consider renaming to $comment->signature_safe or similar
    //   here and elsewhere in Drupal 8.
    $comment->signature = check_markup($comment->signature, $comment->signature_format, '', TRUE);
  }
  else {
    $comment->signature = '';
  }
}

/**
 * Returns HTML for a user signature.
 *
 * @param $variables
 *   An associative array containing:
 *   - signature: The user's signature.
 *
 * @ingroup themeable
 */
function theme_user_signature($variables) {
  $signature = $variables['signature'];
  $output = '';

  if ($signature) {
    $output .= '<div class="clear">';
    $output .= '<div>—</div>';
    $output .= $signature;
    $output .= '</div>';
  }

  return $output;
}

/**
 * Get the language object preferred by the user. This user preference can
 * be set on the user account editing page, and is only available if there
 * are more than one languages enabled on the site. If the user did not
 * choose a preferred language, or is the anonymous user, the $default
 * value, or if it is not set, the site default language will be returned.
 *
 * @param $account
 *   User account to look up language for.
 * @param $default
 *   Optional default language object to return if the account
 *   has no valid language.
 */
function user_preferred_language($account, $default = NULL) {
  $language_list = language_list();
  if (!empty($account->language) && isset($language_list[$account->language])) {
    return $language_list[$account->language];
  }
  else {
    return $default ? $default : language_default();
  }
}

/**
 * Conditionally create and send a notification email when a certain
 * operation happens on the given user account.
 *
 * @see user_mail_tokens()
 * @see drupal_mail()
 *
 * @param $op
 *   The operation being performed on the account. Possible values:
 *   - 'register_admin_created': Welcome message for user created by the admin.
 *   - 'register_no_approval_required': Welcome message when user
 *     self-registers.
 *   - 'register_pending_approval': Welcome message, user pending admin
 *     approval.
 *   - 'password_reset': Password recovery request.
 *   - 'status_activated': Account activated.
 *   - 'status_blocked': Account blocked.
 *   - 'cancel_confirm': Account cancellation request.
 *   - 'status_canceled': Account canceled.
 *
 * @param $account
 *   The user object of the account being notified. Must contain at
 *   least the fields 'uid', 'name', and 'mail'.
 * @param $language
 *   Optional language to use for the notification, overriding account language.
 *
 * @return
 *   The return value from drupal_mail_system()->mail(), if ends up being
 *   called.
 */
function _user_mail_notify($op, $account, $language = NULL) {
//print_r($op);exit;
  // By default, we always notify except for canceled and blocked.
  $default_notify = ($op != 'status_canceled' && $op != 'status_blocked');
  $notify = variable_get('user_mail_' . $op . '_notify', $default_notify);

  if ($notify) {
  
    $params['account'] = $account;
    //print_r($params);exit;
    $language = $language ? $language : user_preferred_language($account);
    $mail = drupal_mail('user', $op, $account->mail, $language, $params);
    if ($op == 'register_pending_approval') {
      // If a user registered requiring admin approval, notify the admin, too.
      // We use the site default language for this.
      drupal_mail('user', 'register_pending_approval_admin', variable_get('site_mail', ini_get('sendmail_from')), language_default(), $params);
    }
  }
  //print_r($mail);exit;
  return empty($mail) ? NULL : $mail['result'];
}

/**
 * Form element process handler for client-side password validation.
 *
 * This #process handler is automatically invoked for 'password_confirm' form
 * elements to add the JavaScript and string translations for dynamic password
 * validation.
 *
 * @see system_element_info()
 */
function user_form_process_password_confirm($element) {
  global $user;

  $js_settings = array(
    'password' => array(
      'strengthTitle' => t('Password strength:'),
      'hasWeaknesses' => t('To make your password stronger:'),
      'tooShort' => t('Make it at least 6 characters'),
      'addLowerCase' => t('Add lowercase letters'),
      'addUpperCase' => t('Add uppercase letters'),
      'addNumbers' => t('Add numbers'),
      'addPunctuation' => t('Add punctuation'),
      'sameAsUsername' => t('Make it different from your username'),
      'confirmSuccess' => t('yes'),
      'confirmFailure' => t('no'),
      'weak' => t('Weak'),
      'fair' => t('Fair'),
      'good' => t('Good'),
      'strong' => t('Strong'),
      'confirmTitle' => t('Passwords match:'),
      'username' => (isset($user->name) ? $user->name : ''),
    ),
  );

  $element['#attached']['js'][] = drupal_get_path('module', 'user') . '/user.js';
  $element['#attached']['js'][] = array('data' => $js_settings, 'type' => 'setting');

  return $element;
}

/**
 * Implements hook_node_load().
 */
function user_node_load($nodes, $types) {
  // Build an array of all uids for node authors, keyed by nid.
  $uids = array();
  foreach ($nodes as $nid => $node) {
    $uids[$nid] = $node->uid;
  }

  // Fetch name, picture, and data for these users.
  $user_fields = db_query("SELECT uid, name, picture, data FROM {users} WHERE uid IN (:uids)", array(':uids' => $uids))->fetchAllAssoc('uid');

  // Add these values back into the node objects.
  foreach ($uids as $nid => $uid) {
    $nodes[$nid]->name = $user_fields[$uid]->name;
    $nodes[$nid]->picture = $user_fields[$uid]->picture;
    $nodes[$nid]->data = $user_fields[$uid]->data;
  }
}

/**
 * Implements hook_image_style_delete().
 */
function user_image_style_delete($style) {
  // If a style is deleted, update the variables.
  // Administrators choose a replacement style when deleting.
  user_image_style_save($style);
}

/**
 * Implements hook_image_style_save().
 */
function user_image_style_save($style) {
  // If a style is renamed, update the variables that use it.
  if (isset($style['old_name']) && $style['old_name'] == variable_get('user_picture_style', '')) {
    variable_set('user_picture_style', $style['name']);
  }
}

/**
 * Implements hook_action_info().
 */
function user_action_info() {
  return array(
    'user_block_user_action' => array(
      'label' => t('Block current user'),
      'type' => 'user',
      'configurable' => FALSE,
      'triggers' => array('any'),
    ),
  );
}

/**
 * Blocks a specific user or the current user, if one is not specified.
 *
 * @param $entity
 *   (optional) An entity object; if it is provided and it has a uid property,
 *   the user with that ID is blocked.
 * @param $context
 *   (optional) An associative array; if no user ID is found in $entity, the
 *   'uid' element of this array determines the user to block.
 *
 * @ingroup actions
 */
function user_block_user_action(&$entity, $context = array()) {
  // First priority: If there is a $entity->uid, block that user.
  // This is most likely a user object or the author if a node or comment.
  if (isset($entity->uid)) {
    $uid = $entity->uid;
  }
  elseif (isset($context['uid'])) {
    $uid = $context['uid'];
  }
  // If neither of those are valid, then block the current user.
  else {
    $uid = $GLOBALS['user']->uid;
  }
  $account = user_load($uid);
  $account = user_save($account, array('status' => 0));
  watchdog('action', 'Blocked user %name.', array('%name' => $account->name));
}

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Add a checkbox for the 'user_register_form' instance settings on the 'Edit
 * field instance' form.
 */
function user_form_field_ui_field_edit_form_alter(&$form, &$form_state, $form_id) {

  $instance = $form['#instance'];

  if ($instance['entity_type'] == 'user' && !$form['#field']['locked']) {
    $form['instance']['settings']['user_register_form'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display on user registration form.'),
      '#description' => t("This is compulsory for 'required' fields."),
      // Field instances created in D7 beta releases before the setting was
      // introduced might be set as 'required' and 'not shown on user_register
      // form'. We make sure the checkbox comes as 'checked' for those.
      '#default_value' => $instance['settings']['user_register_form'] || $instance['required'],
      // Display just below the 'required' checkbox.
      '#weight' => $form['instance']['required']['#weight'] + .1,
      // Disabled when the 'required' checkbox is checked.
      '#states' => array(
        'enabled' => array('input[name="instance[required]"]' => array('checked' => FALSE)),
      ),
      // Checked when the 'required' checkbox is checked. This is done through
      // a custom behavior, since the #states system would also synchronize on
      // uncheck.
      '#attached' => array(
        'js' => array(drupal_get_path('module', 'user') . '/user.js'),
      ),
    );

    array_unshift($form['#submit'], 'user_form_field_ui_field_edit_form_submit');
  }
}

/**
 * Additional submit handler for the 'Edit field instance' form.
 *
 * Make sure the 'user_register_form' setting is set for required fields.
 */
function user_form_field_ui_field_edit_form_submit($form, &$form_state) {

  $instance = $form_state['values']['instance'];

  if (!empty($instance['required'])) {
    form_set_value($form['instance']['settings']['user_register_form'], 1, $form_state);
  }
}


/*function user_register_alter(&$form, &$form_state, $form_id) {

 	 $form['account']['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#maxlength' => USERNAME_MAX_LENGTH,
    '#description' => t('Spaces are allowed; punctuation is not allowed except for periods, hyphens, apostrophes, and underscores.'),
    '#required' => TRUE,
    '#attributes' => array('class' => array('username')),
    '#default_value' => (!$register ? $account->name : ''),
    '#access' => ($register || ($user->uid == $account->uid && user_access('change own username')) || $admin),
    '#weight' => -10,
  );
 
 }*/

/**
 * Form builder; the user registration form.
 *
 * @ingroup forms
 * @see user_account_form()
 * @see user_account_form_validate()
 * @see user_register_submit()
 */
 
 
 
function user_register_form($form, &$form_state) {
  global $user;

  $admin = user_access('administer users');
  // Pass access information to the submit handler. Running an access check
  // inside the submit function interferes with form processing and breaks
  // hook_form_alter().
  $form['administer_users'] = array(
    '#type' => 'value',
    '#value' => $admin,
  );

  // If we aren't admin but already logged on, go to the user page instead.
  if (!$admin && $user->uid) {
    drupal_goto('user/' . $user->uid);
  }

  $form['#user'] = drupal_anonymous_user();
  $form['#user_category'] = 'register';

  $form['#attached']['library'][] = array('system', 'jquery.cookie');
  $form['#attributes']['class'][] = 'user-info-from-cookie';

  // Start with the default user account fields.
  user_account_form($form, $form_state);

  // Attach field widgets, and hide the ones where the 'user_register_form'
  // setting is not on.
  $langcode = entity_language('user', $form['#user']);
  field_attach_form('user', $form['#user'], $form, $form_state, $langcode);
  foreach (field_info_instances('user', 'user') as $field_name => $instance) {
    if (empty($instance['settings']['user_register_form'])) {
      $form[$field_name]['#access'] = FALSE;
    }
  }

  if ($admin) {
    // Redirect back to page which initiated the create request;
    // usually admin/people/create.
    $form_state['redirect'] = $_GET['q'];
  }

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create new account'),
  );

  $form['#validate'][] = 'user_register_validate';
  // Add the final user registration form submit handler.
  $form['#submit'][] = 'user_register_submit';

  return $form;
}

/**
 * Validation function for the user registration form.
 */
 
 
 

 
 
 
 
function user_register_validate($form, &$form_state) {
  entity_form_field_validate('user', $form, $form_state);
}

/**
 * Submit handler for the user registration form.
 *
 * This function is shared by the installation form and the normal registration form,
 * which is why it can't be in the user.pages.inc file.
 *
 * @see user_register_form()
 */
function user_register_submit($form, &$form_state) {

  $admin = $form_state['values']['administer_users'];

  /*if (!variable_get('user_email_verification', TRUE) || $admin) {
    $pass = $form_state['values']['pass'];
    echo "1";
  }
  else {
  
    $pass = user_password();
  }*/
  $pass = $form_state['values']['pass'];
  
  $notify = !empty($form_state['values']['notify']);

  // Remove unneeded values.
  form_state_values_clean($form_state);

  $form_state['values']['pass'] = $pass;
  $form_state['values']['init'] = $form_state['values']['mail'];

  $account = $form['#user'];

  entity_form_submit_build_entity('user', $account, $form, $form_state);

  // Populate $edit with the properties of $account, which have been edited on
  // this form by taking over all values, which appear in the form values too.
  $edit = array_intersect_key((array) $account, $form_state['values']);
  $account = user_save($account, $edit);

  // Terminate if an error occurred during user_save().
  if (!$account) {
    drupal_set_message(t("Error saving user account."), 'error');
    $form_state['redirect'] = '';
    return;
  }
  $form_state['user'] = $account;
  $form_state['values']['uid'] = $account->uid;

  watchdog('user', 'New user: %name (%email).', array('%name' => $form_state['values']['name'], '%email' => $form_state['values']['mail']), WATCHDOG_NOTICE, l(t('edit'), 'user/' . $account->uid . '/edit'));

  // Add plain text password into user account to generate mail tokens.
  $account->password = $pass;

  // New administrative account without notification.
  $uri = entity_uri('user', $account);
  if ($admin && !$notify) {
    drupal_set_message(t('Created a new user account for <a href="@url">%name</a>. No e-mail has been sent.', array('@url' => url($uri['path'], $uri['options']), '%name' => $account->name)));
  }
  // No e-mail verification required; log in user immediately.
  elseif (!$admin && !variable_get('user_email_verification', TRUE) && $account->status) {
    _user_mail_notify('register_no_approval_required', $account);
    $form_state['uid'] = $account->uid;
    user_login_submit(array(), $form_state);
    drupal_set_message(t('Registration successful. You are now logged in.'));
    $form_state['redirect'] = '';
  }
  // No administrator approval required.
  elseif ($account->status || $notify) {
    $op = $notify ? 'register_admin_created' : 'register_no_approval_required';
    _user_mail_notify($op, $account);
    if ($notify) {
      drupal_set_message(t('A welcome message with further instructions has been e-mailed to the new user <a href="@url">%name</a>.', array('@url' => url($uri['path'], $uri['options']), '%name' => $account->name)));
    }
    else {
      drupal_set_message(t('A welcome message with further instructions has been sent to your e-mail address.'));
      $form_state['redirect'] = '';
    }
  }
  // Administrator approval required.
  else {
    _user_mail_notify('register_pending_approval', $account);
    drupal_set_message(t('Thank you for applying for an account. Your account is currently pending approval by the site administrator.<br />In the meantime, a welcome message with further instructions has been sent to your e-mail address.'));
    $form_state['redirect'] = '';
  }
}

/**
 * Implements hook_modules_installed().
 */
function user_modules_installed($modules) {
  // Assign all available permissions to the administrator role.
  $rid = variable_get('user_admin_role', 0);
  if ($rid) {
    $permissions = array();
    foreach ($modules as $module) {
      if ($module_permissions = module_invoke($module, 'permission')) {
        $permissions = array_merge($permissions, array_keys($module_permissions));
      }
    }
    if (!empty($permissions)) {
      user_role_grant_permissions($rid, $permissions);
    }
  }
}

/**
 * Implements hook_modules_uninstalled().
 */
function user_modules_uninstalled($modules) {
   db_delete('role_permission')
     ->condition('module', $modules, 'IN')
     ->execute();
}

/**
 * Helper function to rewrite the destination to avoid redirecting to login page after login.
 *
 * Third-party authentication modules may use this function to determine the
 * proper destination after a user has been properly logged in.
 */
function user_login_destination() {
  $destination = drupal_get_destination();
  if ($destination['destination'] == 'user/login') {
    $destination['destination'] = 'user';
  }
  return $destination;
}

/**
 * Saves visitor information as a cookie so it can be reused.
 *
 * @param $values
 *   An array of key/value pairs to be saved into a cookie.
 */
function user_cookie_save(array $values) {
  foreach ($values as $field => $value) {
    // Set cookie for 365 days.
    setrawcookie('Drupal.visitor.' . $field, rawurlencode($value), REQUEST_TIME + 31536000, '/');
  }
}

/**
 * Delete a visitor information cookie.
 *
 * @param $cookie_name
 *   A cookie name such as 'homepage'.
 */
function user_cookie_delete($cookie_name) {
  setrawcookie('Drupal.visitor.' . $cookie_name, '', REQUEST_TIME - 3600, '/');
}

/**
 * Implements hook_rdf_mapping().
 */
function user_rdf_mapping() {
  return array(
    array(
      'type' => 'user',
      'bundle' => RDF_DEFAULT_BUNDLE,
      'mapping' => array(
        'rdftype' => array('sioc:UserAccount'),
        'name' => array(
          'predicates' => array('foaf:name'),
        ),
        'homepage' => array(
          'predicates' => array('foaf:page'),
          'type' => 'rel',
        ),
      ),
    ),
  );
}

/**
 * Implements hook_file_download_access().
 */
function user_file_download_access($field, $entity_type, $entity) {
  if ($entity_type == 'user') {
    return user_view_access($entity);
  }
}

/**
 * Implements hook_system_info_alter().
 *
 * Drupal 7 ships with two methods to add additional fields to users: Profile
 * module, a legacy module dating back from 2002, and Field API integration
 * with users. While Field API support for users currently provides less end
 * user features, the inefficient data storage mechanism of Profile module, as
 * well as its lack of consistency with the rest of the entity / field based
 * systems in Drupal 7, make this a sub-optimal solution to those who were not
 * using it in previous releases of Drupal.
 *
 * To prevent new Drupal 7 sites from installing Profile module, and
 * unwittingly ending up with two completely different and incompatible methods
 * of extending users, only make the Profile module available if the profile_*
 * tables are present.
 *
 * @todo: Remove in D8, pending upgrade path.
 */
function user_system_info_alter(&$info, $file, $type) {
  if ($type == 'module' && $file->name == 'profile' && db_table_exists('profile_field')) {
    $info['hidden'] = FALSE;
  }
}