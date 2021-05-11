<?php

namespace Drupal\mrmilu_admin_base\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * Constructs a new form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
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

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('mrmilu_admin.editors_menu')->set('roles', $form_state->getValue('roles'))
      ->save();
    $this->messenger()->addStatus($this->t('The configuration options have been saved. Changes take effect after clear caches.'));
  }
}
