<?php

namespace Drupal\mrmilu_admin_base\Render\Element;

use Drupal\admin_toolbar\Render\Element\AdminToolbar;
use Drupal\Core\Menu\MenuTreeParameters;

/**
 * Class EditorAdminToolbar.
 *
 * @package Drupal\mrmilu_admin_base\Render\Element
 */
class EditorAdminToolbar extends AdminToolbar {

  /**
   * Renders the toolbar's administration tray.
   *
   * This is a clone of core's toolbar_prerender_toolbar_administration_tray()
   * function, which uses setMaxDepth(4) instead of setTopLevelOnly().
   *
   * @param array $build
   *   A renderable array.
   *
   * @return array
   *   The updated renderable array.
   *
   * @see toolbar_prerender_toolbar_administration_tray()
   */
  public static function preRenderTray(array $build) {
    $currentUser = \Drupal::currentUser();
    $editorsMenuRoles = \Drupal::config('mrmilu_admin.editors_menu')->get('roles');
    if (!array_intersect($currentUser->getRoles(), $editorsMenuRoles)) {
      return parent::preRenderTray($build);
    }

    $menu_tree = \Drupal::service('toolbar.menu_tree');
    $parameters = new MenuTreeParameters();
    $parameters->setMaxDepth(4)->onlyEnabledLinks();
    $tree = $menu_tree->load('editor', $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ['callable' => 'toolbar_tools_menu_navigation_links'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);
    $build['administration_menu'] = $menu_tree->build($tree);
    return $build;
  }

}
