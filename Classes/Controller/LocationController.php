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
         // Get location IDs from Typo3 BE
         $locationIds = $this->settings['locationFilter']['locations'];

         if ($locationIds !== '0')
            $locationParams = [
               'id' => explode(',', $locationIds)
            ];

         // Retrieve locations from EDM
         $locations = $this->getClient()->getRestClient()->fetchCollection('event_locations', $locationParams ?? []);
         $preparedLocations = $this->prepareLocationData($locations->toArray()['results']);

         // Assign locations from EDM to view
         $this->view->assign('locations', $preparedLocations);
      } catch (Throwable $e) {
         var_dump($e->getMessage());
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

            // Assign location from EDM to view
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

   protected function prepareLocationData(array $locations): array
   {
      foreach ($locations as &$location) {
         // Add slug based on name if not available yet
         if (!isset($location['slug'])) {
            $location['slug'] = $this->slugify($location['name']);
         }
      }

      return $locations;
   }

   private function slugify($input, $separator = '-')
   {
      $replaceSet = [
         'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', ' ' => '-'
      ];

      $slug = strtr($input, $replaceSet);
      $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
      $slug = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $slug);
      $slug = strtolower(trim($slug, '-'));
      $slug = preg_replace('/[\/_|+ -]+/', $separator, $slug);
      return $slug;
   }
}
