<?php

namespace Priorist\EdmTypo3\Controller;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class LocationController extends AbstractController
{
   /**
    * List Locations
    */
   public function listAction(): ResponseInterface
   {
      try {
         // Retrieve locations from EDM
         $locations = $this->getClient()->getRestClient()->fetchCollection('event_locations', []);

         // Assign categories from EDM to view
         $this->view->assign('locations', $locations);
      } catch (Throwable $e) {
         fwrite(STDERR, $e);
         $this->view->assign('internalError', true);
      }

      return $this->htmlResponse();
   }

   /**
    * Location Detail View
    */
   public function detailAction(): ResponseInterface
   {
      if ($this->request->hasArgument('locationId')) {
         $locationFilter = $this->request->getArgument('locationId');
         try {
            // Retrieve location from EDM
            $location = $this->getClient()->getRestClient()->fetchSingle('event_locations', $locationFilter, []);

            // Assign categories from EDM to view
            $this->view->assign('location', $location);
         } catch (Throwable $e) {
            fwrite(STDERR, $e);
            $this->view->assign('internalError', true);
         }
      } else {
         $this->view->assign('internalError', true);
      }

      return $this->htmlResponse();
   }
}
