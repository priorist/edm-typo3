<?php

namespace Priorist\EdmTypo3\Controller;

class LocationController extends AbstractController
{
   /**
    * List Locations
    */
   public function listAction()
   {
      try {
         // Retrieve locations from EDM
         $locations = $this->getClient()->getRestClient()->fetchCollection('event_locations', []);

         // Assign categories from EDM to view
         $this->view->assign('locations', $locations);
      } catch (\Throwable $e) {
         $this->view->assign('internalError', true);
         return;
      }
   }

   /**
    * Location Detail View
    */
   public function detailAction()
   {
      if ($this->request->hasArgument('locationId')) {
         $locationFilter = $this->request->getArgument('locationId');
         try {
            // Retrieve location from EDM
            $location = $this->getClient()->getRestClient()->fetchSingle('event_locations', $locationFilter, []);

            // Assign categories from EDM to view
            $this->view->assign('location', $location);
         } catch (\Throwable $e) {
            $this->view->assign('internalError', true);
            return;
         }
      } else {
         $this->view->assign('internalError', true);
         return;
      }
   }
}
