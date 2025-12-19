<?php

namespace Priorist\EdmTypo3\Controller;

use Psr\Http\Message\ResponseInterface;
use Priorist\EdmTypo3\Controller\AbstractController;

class UserListController extends AbstractController
{
    public function showFormAction(): ResponseInterface
    {
        // Assign content object to view
        $this->view->assign('data', $this->request->getAttribute('currentContentObject')->data);

        // Assign EDM access token to view
        $this->view->assign('accessToken', $this->getClient()->getAccessToken()->getToken());

        return $this->htmlResponse();
    }
}
