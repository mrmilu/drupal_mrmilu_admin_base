<?php

namespace Drupal\mrmilu_admin_base\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\RoleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class EditorsMenuConfigurationForm extends ConfigFormBase {

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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mrmilu_admin.editors_menu'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mrmilu_admin_base_editors_menu_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Allow to select all roles except admin roles and anonymous
    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    unset($roles[RoleInterface::ANONYMOUS_ID]);
    $roleOptions = [];
    foreach ($roles as $roleId => $rol) {
      if (!$rol->isAdmin()) {
        $roleOptions[$roleId] = $rol->label();
      }
    }
    $form['roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Roles'),
      '#options' => $roleOptions,
      '#default_value' => $this->config('mrmilu_admin.editors_menu')->get('roles'),
      '#description' => $this->t('Roles whose admin menu will be replaced with the editors menu.'),
    ];

    $visibleElementsOptions = [];
    if ($this->moduleHandler->moduleExists('node')) {
      $visibleElementsOptions['node'] = $this->t('Content');
    }
    if ($this->moduleHandler->moduleExists('taxonomy')) {
      $visibleElementsOptions['taxonomy'] = $this->t('Taxonomy');
    }
    if ($this->moduleHandler->moduleExists('media')) {
      $visibleElementsOptions['media'] = $this->t('Media');
    }
    if ($this->moduleHandler->moduleExists('menu_ui')) {
      $visibleElementsOptions['menus'] = $this->t('Menus');
    }
    $form['visible_elements'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Visible elements'),
      '#options' => $visibleElementsOptions,
      '#default_value' => $this->config('mrmilu_admin.editors_menu')->get('visible_elements'),
      '#description' => $this->t('Mr.Milú Administration Base module provides some links by default. You can choose which ones will be visible.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('mrmilu_admin.editors_menu')
      ->set('roles', $form_state->getValue('roles'))
      ->set('visible_elements', $form_state->getValue('visible_elements'))
      ->save();
    $this->messenger()->addStatus($this->t('The configuration options have been saved. Changes take effect after clear caches.'));
  }
}
