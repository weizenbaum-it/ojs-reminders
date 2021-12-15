<?php

use PKP\components\forms\FieldSelect;
use PKP\components\forms\FieldText;
use PKP\components\forms\FormComponent;

use function PHPSTORM_META\map;

define("FORM_REVIEW_REMINDERS", "review_reminders");

class ReviewRemindersForm extends FormComponent {
    public function __construct($action, $locales, Context $context) {
        parent::__construct(FORM_REVIEW_REMINDERS, "POST", $action, $locales);
        $this->addField(new FieldText("days", [
            "label" => __("plugins.generic.reviewReminders.form.days.label"),
            "description" => __("plugins.generic.reviewReminders.form.days.description")
        ]));
        $this->addField(new FieldSelect("beforeOrAfter", [
            "label" => __("plugins.generic.reviewReminders.form.beforeOrAfter.label"),
            "description" => __("plugins.generic.reviewReminders.form.beforeOrAfter.description"),
            "options" => [
                [
                    "label" => __("plugins.generic.reviewReminders.form.beforeOrAfter.option.before"),
                    "value" => "before"
                ],
                [
                    "label" => __("plugins.generic.reviewReminders.form.beforeOrAfter.option.after"),
                    "value" => "after"
                ]
            ]
        ]));
        $this->addField(new FieldSelect("deadline", [
            "label" => __("plugins.generic.reviewReminders.form.deadline.label"),
            "description" => __("plugins.generic.reviewReminders.form.deadline.description"),
            "options" => [
                [
                    "label" => __("plugins.generic.reviewReminders.form.deadline.option.response"),
                    "value" => "response"
                ],
                [
                    "label" => __("plugins.generic.reviewReminders.form.deadline.option.review"),
                    "value" => "review"
                ]
            ]
        ]));
        $templateOptions = [];
        foreach(Services::get("emailTemplate")->getMany() as $template) {
            $templateOptions[] = [
                "label" => $template->getData("key"),
                "value" => $template->getData("key")
            ];
        }
        $this->addField(new FieldSelect("templateId", [
            "label" => "Email Template",
            "description" => "The E-Mail Template to be used",
            "options" => $templateOptions
        ]));
    }
}