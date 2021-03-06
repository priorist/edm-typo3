<?php

namespace Priorist\EdmTypo3\Controller;

use Psr\Http\Message\ResponseInterface;

class EnrollmentController extends AbstractController
{
   public function newAction(): ResponseInterface
   {
      // Get 'eventId' parameter from URL
      if ($this->request->hasArgument('eventId')) {
         $eventId = $this->request->getArgument('eventId');

         try {
            // Assign event based on 'eventId' from EDM to view
            $event = $this->getClient()->event->findById($eventId);
            $this->view->assign('accessToken', $this->getClient()->getAccessToken()->getToken());

            // set last day of event
            if (!isset($event['last_day'])) {
               foreach ($event['dates'] as $key => $date) {
                  if ($key === array_key_last($event['dates'])) {
                     $event['last_day'] = $date['day'];
                  }
               }
            }

            if (isset($event['prices'])) {
               $priceCount = count($event['prices']);

               // sort prices ascending from lowest to highest amount
               usort($event['prices'], function ($item1, $item2) {
                  return $item1['amount'] <=> $item2['amount'];
               });

               $event['lowest_price'] = $event['prices'][0]['amount'];
               $event['price_count'] = $priceCount;
            }

            $this->view->assign('event', $event);
         } catch (\Throwable $e) {
            $this->view->assign('internalError', true);
         }

         // Get participantToken from Typo3 session
         $this->view->assign('participantToken', $this->session->get('participantToken'));
      } else {
         // no event id in URL, so no enrollment possible
         $this->view->assign('internalError', true);
      }

      return $this->htmlResponse();
   }
}
