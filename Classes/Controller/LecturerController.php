<?php

namespace Priorist\EdmTypo3\Controller;

class LecturerController extends AbstractController
{
  /**
   * List Lecturers
   */
  public function listAction()
  {
    try {
      // Retrieve lecturers from EDM
      $lecturers = $this->getClient()->getRestClient()->fetchCollection('lecturers', []);

      // Assign categories from EDM to view
      $this->view->assign('lecturers', $lecturers);
    } catch (\Throwable $e) {
      $this->view->assign('internalError', true);
      return;
    }
  }

  /**
   * Lecturer Detail View
   */
  public function detailAction()
  {
    if ($this->request->hasArgument('lecturerId')) {
      $lecturerFilter = $this->request->getArgument('lecturerId');
      try {
        // Retrieve lecturer from EDM
        $lecturer = $this->getClient()->getRestClient()->fetchSingle('lecturers', $lecturerFilter, []);

        $eventParams = [
          'id' => $lecturer['events'],
          'serializer_format' => 'website_list'
        ];

        $events = $this->getClient()->getRestClient()->fetchCollection('events', $eventParams);
        $eventArray = $events->toArray();
        $events = $eventArray['results'];

        foreach ($events as $key => &$event) {
          $event['id'] = $lecturer['events'][$key];

          usort($event['prices'], function ($item1, $item2) {
            return $item1['amount'] <=> $item2['amount'];
          });
        }

        // Assign lecturer and events from EDM to view
        $this->view->assign('lecturer', $lecturer);
        $this->view->assign('events', $events);
      } catch (\Throwable $e) {
        var_dump($e->getMessage());
        $this->view->assign('internalError', true);
        return;
      }
    } else {
      $this->view->assign('internalError', true);
      return;
    }
  }
}
