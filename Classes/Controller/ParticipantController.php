<?php

namespace Priorist\EdmTypo3\Controller;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

// TODO: needs refactoring!!!

class ParticipantController extends AbstractController
{
    /**
     * Login
     */
    public function loginAction(): ResponseInterface
    {
        // Assign content object to view
        $this->view->assign('data', $this->configurationManager->getContentObject()->data);

        try {
            $participant = $this->getClient()->getUser();
        } catch (Exception $e) {
            $participant = null;
        }

        // Assign participant to FE if it already exists and return
        if ($participant !== null) {
            $this->assignParticipant($participant);
            $this->assignParticipant($participant, true);

            $participantToken = $this->getClient()->getAccessToken();
            $this->assignParticipantToken($participantToken);
            $this->assignParticipantToken($participantToken, true);
        }

        // Get values of login form inputs
        $arguments = $this->request->getArguments();
        $loginUsername = trim($arguments['login']['username']);
        $loginPassword = trim($arguments['login']['password']);


        // Login participant in EDM Client based on his entered login credentials
        try {
            $participantToken = $this->getClient()->logIn($loginUsername, $loginPassword);
        } catch (InvalidArgumentException $e) {
            fwrite(STDERR, $e);
            // If EDM Login is not successful, set 'invalidLogin' to true, assign it to FE and return
            $this->view->assign('invalidLogin', true);
        } catch (Exception $e) {
            fwrite(STDERR, $e);
            // If another exception occurs, set 'internalError' to true, assign it to FE and return
            $this->view->assign('internalError', true);
        }

        // Virtual Typo3 Login with fixed user
        $participant = $this->loginParticipant($participantToken);

        // Assign participant to FE
        $this->assignParticipant($participant);
        $this->assignParticipant($participant, true);

        $settings = $this->settings;
        $loginPageId = $settings['pageuids']['login'];

        $this->redirectParticipant($loginPageId);

        return $this->htmlResponse();
    }

    /**
     * Status
     */
    public function statusAction(): ResponseInterface
    {
        try {
            $participant = $this->getClient()->getUser();
        } catch (Exception $e) {
            fwrite(STDERR, $e);
            $participant = null;
        }

        if ($participant !== null) {
            $participantData = [];
            $participantData['first_name'] = $participant->get('first_name');
            $participantData['last_name'] = $participant->get('last_name');

            $this->view->assign('participantData', $participantData);
        }

        $this->view->assign('participant', $participant);

        return $this->htmlResponse();
    }

    /**
     * Logout
     */
    public function logoutAction(): ResponseInterface
    {
        $settings = $this->settings;
        $logoutPageId = $settings['pageuids']['logout'];

        $this->logoutParcticipant();

        $this->redirectParticipant($logoutPageId);

        return $this->htmlResponse();
    }

    /**
     * Actual Typo3 login with fix user credentials
     *
     * @param $participantToken passed accessToken from EDM to be stored in the session
     *
     * @return object information about the participant from EDM
     */
    public function loginParticipant($participantToken)
    {
        $settings = $this->settings;
        $feUsername = $settings['feUsername'];

        // This is dirty, but needs to be done by hand atm
        // TODO: does not work with Typo3 v10, needs to be replaced with Authentication Service
        /* $reflection = new \ReflectionClass($GLOBALS['TSFE']->fe_user);
        $setSessionCookieMethod = $reflection->getMethod('setSessionCookie');
        $setSessionCookieMethod->setAccessible(TRUE);
        $setSessionCookieMethod->invoke($GLOBALS['TSFE']->fe_user);

		$GLOBALS['TSFE']->fe_user->checkPid = 0;
		$info = $GLOBALS['TSFE']->fe_user->getAuthInfoArray();
		$user = $GLOBALS['TSFE']->fe_user->fetchUserRecord($info['db_user'], $feUsername);

		$GLOBALS['TSFE']->fe_user->createUserSession($user);
		$GLOBALS['TSFE']->fe_user->setAndSaveSessionData('dummy', TRUE);
        $GLOBALS['TSFE']->loginUser = 1; */

        // Assign participantToken to FE
        $this->assignParticipantToken($participantToken);
        $this->assignParticipantToken($participantToken, true);

        // Store participantToken in Typo3 session
        $this->session->set('participantToken', $participantToken);

        return $this->getClient()->getUser();
    }

    /**
     * Actual Typo3 logout and removal of session data
     */
    public function logoutParcticipant()
    {
        $GLOBALS['TSFE']->fe_user->logoff();
        $this->session->removeData();
    }

    /**
     * Actual Typo3 login with fixed user credentials
     *
     * @param int $pageId ID of page that should be redirected to
     */
    public function redirectParticipant(int $pageId)
    {
        $uri = $this->uriBuilder->setTargetPageUid($pageId)->build();
        $this->redirectToUri($uri, 0, 404);
    }

    /**
     * Assign participant information to FE
     *
     * @param $participant object containing information about participant
     * @param bool $json whether the actual object or JSON should be assigned to the view
     */
    public function assignParticipant($participant, bool $json = false)
    {
        $value = $participant->toArray();
        $key = 'participant';

        if ($json === true) {
            $assignAsJson = $this->assignAsJson($key, $value);
            $key = $assignAsJson[0];
            $value = $assignAsJson[1];
        }

        $this->view->assign($key, $value);
    }

    /**
     * Assign participantToken information to FE
     *
     * @param $participantToken object containing information about participantToken
     * @param bool $json whether the actual object or JSON should be assigned to the view
     */
    public function assignParticipantToken($participantToken, bool $json = false)
    {
        $value = [
            'accessToken' => $participantToken->getToken(),
            'hasExpired' => $participantToken->hasExpired(),
            'refreshToken' => $participantToken->getRefreshToken()
        ];
        $key = 'participantToken';

        if ($json === true) {
            $assignAsJson = $this->assignAsJson($key, $value);
            $key = $assignAsJson[0];
            $value = $assignAsJson[1];
        }

        $this->view->assign($key, $value);
    }

    /**
     * Encode array information as JSON for further FE usage in JavaScript
     *
     * @param string $key name of the fluid variable assigned to FE
     * @param array $value value that will be assigned to the variable
     *
     * @return array
     */
    public function assignAsJson(string $key, array $value): array
    {
        $key .= "Json";
        $value = json_encode($value);

        return [$key, $value];
    }
}
