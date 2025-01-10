<?php

namespace Priorist\EdmTypo3\Controller;

use Priorist\EDM\Client\Rest\ClientException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

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
            if (isset($event['dates']) && !isset($event['last_day'])) {
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
         } catch (ClientException $e) {
            if ($e->getCode() === 401) {
               $this->resetAccessToken();
               $this->view->assign('internalError', true);
            }
         } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());
            $this->view->assign('internalError', true);
         }

         // Get participantToken from Typo3 session
         // $this->view->assign('participantToken', $this->session->get('participantToken'));
      } else if (isset($_GET['code']) && isset($_GET['state'])) {
         // OAuth redirect from EDM
         $this->view->assign('oAuthRedirect', true);
      } else {
         // no event id in URL, so no enrollment possible
         $this->logger->error('No event id in URL. Enrollment not possible.');
         $this->view->assign('internalError', true);
      }

      return $this->htmlResponse();
   }
}
