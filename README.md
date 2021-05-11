# Mr.Milú Administration base
This module provides some administration functionality to help while developing a website.

## Configuration
There is a menu entry in *Manage > Configuration > Mr.Milú administration* where you can see the links to configure each functionality provided.

## Functionality
### Editors menu
You can select a simple and intuitive menu for editors. The roles which will see this menu instead of the default one are configured in `/admin/config/mrmilu-admin/editors-menu. By default, the links provider are

- **Content**: Links to content overview page and links to create nodes of content types with permission.
- **Taxonomy**: Link to the vocabulary overview page. All vocabularies are listed but user can only edit those with permissions.
- **Media**: Link to the media over view page.
- **Menus**: Link to a list of menus that can be edited by the user (It is recommended to install and configure [menu_admin_per_menu](https://www.drupal.org/project/menu_admin_per_menu) to be able to set permissions to specific menus.)

Each link will only be visible if user has access to view it.
#### Customization
Customize this menu with your own links is easy. Simply follow these steps:
1. Create a module called **your_project_admin**
2. Create a class `Drupal\your_module_admin\Plugin\Derivative\YourProjectEditorAdminToolbar` that extends `Drupal\mrmilu_admin_base\Plugin\Derivative\EditorAdminToolbar` and add or remove the links you want.
3. Create a `your_project_admin.module` file and place this code (modifying module name).
```php
<?php

use Drupal\your_project_admin\Render\Element\YourProjectEditorAdminToolbar;


/**
 * Implements hook_toolbar_alter().
 */
function your_project_admin_toolbar_alter(&$items) {
  $items['administration']['tray']['toolbar_administration']['#pre_render'] = [[YourProjectEditorAdminToolbar::class, 'preRenderTray']];
}
```
4. Clear caches
