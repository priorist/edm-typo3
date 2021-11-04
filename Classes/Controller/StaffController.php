<?php

namespace Priorist\EdmTypo3\Controller;

class StaffController extends AbstractController
{
    /*
     * Get details of a staff member from EDM
     */
    public function detailAction() {
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
        } catch (\Throwable $e) {
            $this->view->assign('internalError', true);
            return;
        }
    }
}