<?php

namespace SURFnet\OATHBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;

class OathController extends FOSRestController
{
    /**
     * @Get("/oath/challenge/{type}")
     * @ApiDoc(
     *  section="OATH",
     *  description="Get an OATH challenge",
     *  requirements={
     *    {"name"="type", "dataType"="string", "description"="The type of oath algorithm to use (possible values: ocra, hotp, totp)"}
     *  },
     *  statusCodes={
     *      200="Success, challenge is in the body",
     *      404="Unknown type",
     *      500="General error, something went wrong",
     *  },
     * )
     */
    public function getChallengeAction($type)
    {
        $storage = $this->getStorage();
        $responseCode = 200;
        try {
            $data = $storage->getValue($key);
        } catch (\Exception $e) {
            $data = array('error' => $e->getMessage());
            $responseCode = $e->getCode() ?: 500;
        }
        $view = $this->view($data, $responseCode);
        return $this->handleView($view);
    }

    /**
     * @Get("/oath/validate/{type}")
     * @ApiDoc(
     *  section="OATH",
     *  description="Validate a challenge against a response",
     *  requirements={
     *    {"name"="type", "dataType"="string", "description"="The type of oath algorithm to use (possible values: ocra, hotp, totp)"}
     *  },
     *  parameters={
     *    {"name"="challenge", "dataType"="string", "required"=true, "description"="The original challenge generated by GET /oath/challenge/{type}"},
     *    {"name"="response", "dataType"="string", "required"=true, "description"="The response to validate the challenge against"},
     *    {"name"="userId", "dataType"="string", "required"=false, "description"="The user id (required for ocra)"},
     *    {"name"="sessionKey", "dataType"="string", "required"=false, "description"="The session key (required for ocra)"}
     *  },
     *  statusCodes={
     *      200="Success",
     *      404="Unknown type",
     *      500="General error, something went wrong",
     *  },
     * )
     */
    public function validateChallengeAction($type)
    {
        $storage = $this->getStorage();
        $request = $this->get('request_stack')->getCurrentRequest();

        $responseCode = 200;
        try {
            $data = $storage->storeValue($key, $request->get('value'), (int)$request->get('expire', 0));
        } catch (\Exception $e) {
            $data = array('error' => $e->getMessage());
            $responseCode = $e->getCode() ?: 500;
        }
        $view = $this->view($data, $responseCode);
        return $this->handleView($view);
    }

    /**
     * Create the storage class using the storage factory and return the class
     *
     * @return mixed
     */
    protected function getStorage()
    {
        $storageFactory = $this->get('surfnet_oath.storage.factory');
        $config = $this->container->getParameter('surfnet_oath');
        return $storageFactory->createStorage($config['storage']['type'], $config['storage']['options']);
    }
}