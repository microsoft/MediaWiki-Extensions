# Azure Active Directory Role-Based Access Control

## Requirements
- PHP 7.4 or above
- MediaWiki 1.35 or above

## Configuration

1. [Declare new App roles](https://docs.microsoft.com/en-us/azure/active-directory/develop/howto-add-app-roles-in-azure-ad-apps#declare-roles-for-an-application) in the App Registration: 

   * **Administrator**
     * Display name: Administrator
     * Allowed member types: Users/Groups
     * Value: Wiki.Administrator
     * Description: Identify users as an administrator who by default can delete and restore pages, block and unblock users, edit sitewide CSS/JS, etc. (MediaWiki sysop and interface-admin group combined together as this Administrator group)
   * **Bureaucrat**
     * Display name: Bureaucrat
     * Allowed member types: Both(Users/Groups + Applications)
     * Value: Wiki.Bureaucrat
     * Description: Identify users who by default can change other users' rights (original MediaWiki bureaucrat group)
   * **Contributor**
     * Display name: Contributor
     * Allowed member types: Both(Users/Groups + Applications)
     * Value: Wiki.Contributor
     * Description: Identify users who have edit privilege in the Wiki

   * **Reader**
     * Display name: Reader
     * Allowed member types: Both(Users/Groups + Applications)
     * Value: Wiki.Reader
     * Description: Identify users who only have read privilege in the Wiki

2. Migrate users and [assign users and groups to app roles](https://docs.microsoft.com/en-us/azure/active-directory/develop/howto-add-app-roles-in-azure-ad-apps#assign-app-roles-to-applications) in Enterprise App Registration

   If you enable this extension for your wiki, you must migrate all the users in "sysop" and "interface-admin", "bureaucrat" to the proper groups in AAD as shown below. Otherwise, after you enable this extension and the existing users login, the user's group will be deleted since the AAD group will be the source of truth. 
  
   * create a new user group named ```"<Wiki_name> Administrator"``` in AAD and **add all users in "sysop" and "interface-admin" group to this new group** 
   * create a new user group named ```"<Wiki_name> Bureaucrat"``` in AAD and **add all users in "bureaucrat" group to this new group**
   * create a new user group named ```"<Wiki_name> Contributor"``` in AAD

    Then, assign the corresponding app roles to the groups. e.g., assign "Administrator" to ```"<Wiki_name> Administrator"``` etc.
   
3. To configure this extension, you may set any of the following in your `LocalSettings.php`:

    **IMPORTANT**: Please DON'T start this step if you haven't completed the above steps (a) since the current user's existing group in the database could be flushed if you enable this extension first!

    ``` php
    ## RBAC Extension
    wfLoadExtension( 'Microsoft/RBAC' );
    // Disables write access (page editing and creation) by default, creates a group named "contributor", and grants it write access.
    $wgGroupPermissions['user']['edit'] = false;
    $wgGroupPermissions['user']['createpage'] = false;
    # Start with assigning the default permissions from group "user", and then make the necessary permission update for new group "contributor"
    $wgContributorUserGroup = 'contributor';
    $wgGroupPermissions[ $wgContributorUserGroup ] = $wgGroupPermissions['user'];
    $wgGroupPermissions[ $wgContributorUserGroup ]['edit'] = true;
    $wgGroupPermissions[ $wgContributorUserGroup ]['createpage'] = true;
    $wgUserRolePermissionGroupMapping = [
        'Wiki.Administrator' => 'sysop;interface-admin',
        'Wiki.Bureaucrat' => 'bureaucrat',
        'Wiki.Contributor' => $wgContributorUserGroup
    ];
    ```

## Something you should be aware of 

### RBAC is session-based

This extension is session-based, that is, the user's groups will be updated only for new session (restart the browser or re-login the wiki after the session expires).

### [AAD nested groups](https://docs.microsoft.com/en-us/azure/active-directory/enterprise-users/groups-saasapps)

AAD nested group memberships are not supported for group-based assignment to applications at this time. So don't expect the nested groups will have the permission as the direct members.  
