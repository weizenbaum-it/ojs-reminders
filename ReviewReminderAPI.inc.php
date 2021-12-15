<?php

use Slim\Http\Request;
use Slim\Http\Response;

import('lib.pkp.classes.handler.APIHandler');
class ReviewReminderAPI extends APIHandler {
    public function __construct()
    {
        $roles = [ROLE_ID_SITE_ADMIN, ROLE_ID_MANAGER];
        $this->_handlerPath = 'reviewReminders';
        $this->_endpoints = [
            "POST" => [
                [
                    "pattern" => $this->getEndpointPattern(),
                    "handler" => [$this, "add"],
                    "roles" => $roles
                ]
            ],
            "PUT" => [
                [
                    "pattern" => $this->getEndpointPattern() . "/{reviewReminderId}",
                    "handler" => [$this, "edit"],
                    "roles" => $roles
                ]
            ],
            "DELETE" => [
                [
                    "pattern" => $this->getEndpointPattern() . "/{reviewReminderId}",
                    "handler" => [$this, "delete"],
                    "roles" => $roles
                ]
            ]
        ];
        parent::__construct();
    }

    public function authorize($request, &$args, $roleAssignments)
    {
        import('lib.pkp.classes.security.authorization.PolicySet');
        import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');
        $rolePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

        foreach ($roleAssignments as $role => $operations) {
            $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
        }
        $this->addPolicy($rolePolicy);

        return parent::authorize($request, $args, $roleAssignments);
    }

    public function add(Request $slimRequest, Response $response, $args) {
        /** @var ReviewReminderDAO */
        $dao = DAORegistry::getDAO("reviewReminders");
        $reminder = $dao->newDataObject();
        $reminder->setAllData($slimRequest->getParsedBody());
        $reminder->setData("contextId", $this->getRequest()->getContext()->getId());
        return $response->withJson([
            "id" => $dao->insertObject($reminder),
            "title" => __("plugins.generic.reviewReminders.remindersTitle", [
                "numberOfDays" => $reminder->getData("days"),
                "beforeOrAfter" => $reminder->getData("beforeOrAfter") == "before" ? __("plugins.generic.reviewReminders.before") : __("plugins.generic.reviewReminders.after"),
                "deadline" => $reminder->getData("deadline") == "response" ? __("plugins.generic.reviewReminders.form.deadline.option.response") : __("plugins.generic.reviewReminders.form.deadline.option.review"),
            ]),
            "subtitle" => __("plugins.generic.reviewReminders.remindersSubtitle", [
                "templateId" => $reminder->getData("templateId")
            ]),
            "raw" => [
                "days" => $reminder->getData("days"),
                "beforeOrAfter" => $reminder->getData("beforeOrAfter"),
                "templateId" => $reminder->getData("templateId"),
                "deadline" => $reminder->getData("deadline")
            ]
        ]);
    }

    public function edit(Request $slimRequest, Response $response, $args) {
        /** @var ReviewReminderDAO */
        $dao = DAORegistry::getDAO("reviewReminders");
        /** @var ReviewReminderDO */
        $reminder = $dao->getById($args["reviewReminderId"]);
        foreach ($slimRequest->getParsedBody() as $index => $value) {
            $reminder->setData($index, $value);
        }
        $dao->updateObject($reminder);
        return $response->withJson([
            "id" => $reminder->getData("id"),
            "title" => __("plugins.generic.reviewReminders.remindersTitle", [
                "numberOfDays" => $reminder->getData("days"),
                "beforeOrAfter" => $reminder->getData("beforeOrAfter") == "before" ? __("plugins.generic.reviewReminders.before") : __("plugins.generic.reviewReminders.after"),
                "deadline" => $reminder->getData("deadline") == "response" ? __("plugins.generic.reviewReminders.form.deadline.option.response") : __("plugins.generic.reviewReminders.form.deadline.option.review"),
            ]),
            "subtitle" => __("plugins.generic.reviewReminders.remindersSubtitle", [
                "templateId" => $reminder->getData("templateId")
            ]),
            "raw" => [
                "days" => $reminder->getData("days"),
                "beforeOrAfter" => $reminder->getData("beforeOrAfter"),
                "templateId" => $reminder->getData("templateId"),
                "deadline" => $reminder->getData("deadline")
            ]
        ]);
    }

    public function delete(Request $slimRequest, Response $response, $args) {
        /** @var ReviewReminderDAO */
        $dao = DAORegistry::getDAO("reviewReminders");
        $dao->deleteById($args["reviewReminderId"]);
        return $response->withJson([
            "id" => $args["reviewReminderId"]
        ]);
    }
}