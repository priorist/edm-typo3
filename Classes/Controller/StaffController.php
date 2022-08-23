<?php

namespace Priorist\EdmTypo3\Controller;

use Priorist\EDM\Client\Rest\ClientException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class StaffController extends AbstractController
{
    /*
     * Get details of a staff member from EDM
     */
    public function detailAction(): ResponseInterface
    {
        // Get staff member ID from Typo3 BE
        $staffId = $this->settings['staffId'];

        // Assign content object to view
        $this->view->assign('data', $this->configurationManager->getContentObject()->data);

        $staffParams = [];

        try {
            // Retrieve staff member from EDM
            $staff = $this->getClient()->getRestClient()->fetchSingle('staff_members', $staffId, $staffParams);

            // Assign staff member from EDM to view
            $this->view->assign('staff', $staff);
        } catch (ClientException $e) {
            if ($e->getCode() === 401) {
                $this->resetAccessToken();
                $this->view->assign('internalError', true);
            }
        } catch (Throwable $e) {
            $this->view->assign('internalError', true);
        }

        return $this->htmlResponse();
    }
}
