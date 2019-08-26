<?php

namespace Drupal\zoho_crm_example\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\Messenger;
use Drupal\zoho_crm_integration\Service\ZohoCRMAuthService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use zcrmsdk\crm\crud\ZCRMModule;

/**
 * Class MyZohoFormController.
 */
class ZohoCrmExampleController extends ControllerBase {

  /**
   * The Zoho CRM Auth service.
   *
   * @var Drupal\zoho_crm_integration\Service\ZohoCRMAuthService
   */
  protected $authService;

  /**
   * The Drupal messenger service.
   *
   * @var Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Controller Constructor.
   *
   * @param \Drupal\zoho_crm_integration\Service\ZohoCRMAuthService $auth_service
   *   The module handler service.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   Drupal Messenger service.
   */
  public function __construct(ZohoCRMAuthService $auth_service, Messenger $messenger) {
    $this->authService = $auth_service;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('zoho_crm_integration.auth'),
      $container->get('messenger')
    );
  }

  /**
   * Revoke the refresh token.
   */
  public function leads() {
    $this->authService->initialize();
    $data = [];
    $rows = [];

    try {
      $zcrmModuleIns = ZCRMModule::getInstance("Leads");
      $bulkAPIResponse = $zcrmModuleIns->getRecords();
      $recordsArray = $bulkAPIResponse->getData();
      if ($recordsArray) {
        $rows = array_keys($recordsArray[0]->getData());
        $data = $this->prepareLeadsData($recordsArray);
      }

      return [
        '#theme' => 'table',
        '#header' => $rows,
        '#rows' => $data,
        '#responsive' => TRUE,
      ];
    }
    catch (\Exception $e) {
      $this->messenger->addMessage($this->t('We could not get your leads or you do not have content yet.'), 'status');
    }

    return [
      '#markup' => 'No content found.',
    ];
  }

  /**
   * Get Leads list of fields to use on a Table theme.
   *
   * @param array $leads
   *   Array of ZCRMRecord objects.
   *
   * @return array
   *   List of Leads fields.
   */
  private function prepareLeadsData(array $leads) {
    $data = [];

    foreach ($leads as $lead) {
      $data[] = $lead->getData();
    }

    return $data;
  }

}
