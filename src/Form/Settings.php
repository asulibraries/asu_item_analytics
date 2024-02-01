<?php

namespace Drupal\asu_item_analytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form class for ASU Item Analytics settings.
 */
class Settings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asu_item_analytics_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['asu_item_analytics.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asu_item_analytics.settings');

    // Credentials file path.
    $form['credentials_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Credentials File Path'),
      '#default_value' => $config->get('credentials_path'),
      '#description' => $this->t('Enter the path to the credentials file.'),
    ];

    // Property ID.
    $form['property_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Property ID'),
      '#default_value' => $config->get('property_id'),
      '#description' => $this->t('Enter the Google Analytics property ID.'),
    ];

    // Event Name.
    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#default_value' => $config->get('event_name'),
      '#description' => $this->t('Enter the name of the event to track.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Validation.
   *
   * @todo add validation to ensure the credentials file exists.
   */

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('asu_item_analytics.settings');

    // Save form values to configuration.
    $config->set('credentials_path', $form_state->getValue('credentials_path'));
    $config->set('property_id', $form_state->getValue('property_id'));
    $config->set('event_name', $form_state->getValue('event_name'));
    $config->save();

    drupal_set_message($this->t('Configuration saved successfully.'));
  }

}
