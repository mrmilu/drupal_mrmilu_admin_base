<?php

namespace Drupal\mrmilu_admin_base\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\system\Entity\Menu;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates automatic links for editors menu
 */
class EditorExtraLinks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $links = [];

    if ($this->moduleHandler->moduleExists('node')) {
      $links['node'] = [
        'title' => $this->t('Content'),
        'route_name' => 'system.admin_content',
        'weight' => 0
      ] + $base_plugin_definition;
      $links['node.add'] = [
        'title' => $this->t('Add content'),
        'route_name' => 'node.add_page',
        'parent' => $base_plugin_definition['id'] . ':node',
      ] + $base_plugin_definition;
      // Adds node links for each content type.
      foreach ($this->entityTypeManager->getStorage('node_type')->loadMultiple() as $type) {
        $links['node.add.' . $type->id()] = [
          'title' => $this->t($type->label()),
          'route_name' => 'node.add',
          'parent' => $base_plugin_definition['id'] . ':node.add',
          'route_parameters' => ['node_type' => $type->id()],
        ] + $base_plugin_definition;
      }
    }

    if ($this->moduleHandler->moduleExists('taxonomy')) {
      $links['taxonomy'] = [
        'title' => $this->t('Taxonomy'),
        'route_name' => 'entity.taxonomy_vocabulary.collection',
        'weight' => 1
      ] + $base_plugin_definition;
    }

    if ($this->moduleHandler->moduleExists('media')) {
      $links['media_page'] = [
        'title' => $this->t('Media'),
        'route_name' => 'view.media.media_page_list',
        'weight' => 2
      ] + $base_plugin_definition;
      $links['add_media'] = [
        'title' => $this->t('Add media'),
        'route_name' => 'entity.media.add_page',
        'parent' => $base_plugin_definition['id'] . ':media_page',
      ] + $base_plugin_definition;
      // Adds links for each media type.
      foreach ($this->entityTypeManager->getStorage('media_type')->loadMultiple() as $type) {
        $links['media.add.' . $type->id()] = [
          'title' => $type->label(),
          'route_name' => 'entity.media.add_form',
          'parent' => $base_plugin_definition['id'] . ':add_media',
          'route_parameters' => ['media_type' => $type->id()],
        ] + $base_plugin_definition;
      }
    }

    if ($this->moduleHandler->moduleExists('menu_ui')) {
      // Load all menus by default. You should install install menu_admin_per_menu module
      $allowed_menus = $this->entityTypeManager->getStorage('menu')->loadByProperties([]);
      uasort($allowed_menus, [Menu::class, 'sort']);
      $links['menus'] = [
        'title' => $this->t('Menus'),
        'route_name' => 'entity.menu.collection',
        'weight' => 4,
      ] + $base_plugin_definition;

      $weight = 0;
      foreach ($allowed_menus as $menu_id => $menu) {
        $links['entity.menu.edit_form.' . $menu_id] = [
          'title' => $menu->label(),
          'route_name' => 'entity.menu.edit_form',
          'parent' => $base_plugin_definition['id'] . ':menus',
          'route_parameters' => ['menu' => $menu_id],
          'weight' => $weight,
        ] + $base_plugin_definition;
        $weight++;
      }
    }

    return $links;
  }
}
