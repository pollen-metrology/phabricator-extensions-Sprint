<?php
/**
 * @author Michael Peters
 * @author Christopher Johnson
 * @license GPL version 3
 */

final class BurndownActionMenuEventListener extends PhabricatorEventListener {

  public function register() {
    $this->listen(PhabricatorEventType::TYPE_UI_DIDRENDERACTIONS);
  }

  public function handleEvent(PhutilEvent $event) {
    switch ($event->getType()) {
      case PhabricatorEventType::TYPE_UI_DIDRENDERACTIONS:
        $this->handleActionsEvent($event);
      break;
    }
  }

  private function handleActionsEvent(PhutilEvent $event) {
    $object = $event->getValue('object');

    $actions = null;
    if ($object instanceof PhabricatorProject &&
      $this->isSprint($object) !== false) {
      $actions = $this->renderUserItems($event);
    }

    $this->addActionMenuItems($event, $actions);
  }

  protected function isSprint($object) {
    $validator = new SprintValidator();
    $issprint = call_user_func(array($validator, 'checkForSprint'),
        array($validator, 'isSprint'), $object->getPHID());
    return $issprint;
  }

  private function renderUserItems(PhutilEvent $event) {
    if (!$this->canUseApplication($event->getUser())) {
      return null;
    }
    $enable_phragile = PhabricatorEnv::getEnvConfig('sprint.enable-phragile');
    $project = $event->getValue('object');
    $projectid = $project->getId();

    $view_uri = '/project/sprint/view/'.$projectid;
    $board_uri = '/project/sprint/board/'.$projectid;
    $phragile_uri = 'https://phragile.wmflabs.org/sprints/'.$projectid;

    $burndown = id(new PhabricatorActionView())
        ->setIcon('fa-bar-chart-o')
        ->setName(pht('View Burndown'))
        ->setHref($view_uri);

    $board = id(new PhabricatorActionView())
        ->setIcon('fa-columns')
        ->setName(pht('View Sprint Board'))
        ->setHref($board_uri);

    $phragile = null;
    if ($enable_phragile) {
      $phragile = id(new PhabricatorActionView())
          ->setIcon('fa-pie-chart')
          ->setName(pht('View in Phragile'))
          ->setHref($phragile_uri);
    }


    return array ($burndown, $board, $phragile);
  }



}
