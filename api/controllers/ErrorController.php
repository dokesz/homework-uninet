<?php

class ErrorController extends Phalcon\MVC\Controller
{
    function unauthorizedAction()
    {
        return $this
            ->response
            ->setStatusCode(401, "Unauthorized")
            ->setJsonContent(["error" => "Unauthorized"]);
    }

    function forbiddenAction()
    {
        return $this
            ->response
            ->setStatusCode(403, "Forbidden")
            ->setJsonContent(["error" => "Forbidden"]);
    }
}
