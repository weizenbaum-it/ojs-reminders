<?php

use PKP\components\forms\FormComponent;

import("lib.pkp.classes.plugins.GenericPlugin");
import("plugins.generic.reviewReminders.ReviewRemindersForm");
import("plugins.generic.reviewReminders.ReviewRemindersMigration");
import("plugins.generic.reviewReminders.ReviewReminderDAO");
import("plugins.generic.reviewReminders.ReviewReminderDO");
import("plugins.generic.reviewReminders.ReviewRemindersTask");
class ReviewRemindersPlugin extends GenericPlugin {

    public function getDisplayName() {
        return __("plugins.generic.reviewReminders.name");
    }

    public function getDescription() {
        return __("plugins.generic.reviewReminders.description");
    }

    public function register($category, $path, $mainContextId = null) {
        // Api routes can not be registered through hooks, so it has to be done via the filesystem
        // Add route to api if plugin is enabled and route does not exist yet
        if (!file_exists("api/v1/reviewReminders/index.php")) {
            mkdir("api/v1/reviewReminders");
            copy(__DIR__. "/install/index.php", "api/v1/reviewReminders/index.php");
        }
        DAORegistry::registerDAO("reviewReminders", new ReviewReminderDAO());
        HookRegistry::register("Schema::get::reviewReminders", [$this, "getSchema"]);

        // Register scheduled tasks with the acron plugin if available as a fallback if scheduled tasks are disabled
        // Runs on OJS or plugin installation, so it needs to run even if the plugin is disabled
        HookRegistry::register("AcronPlugin::parseCronTab", [$this, "parseCronTab"]);

        // Check if runScheduledTasks has been called. If this is the case, run the plugins own task.
        // Runs even if plugin as disabled, as runScheduledTasks does not specify any context in which the plugin might be enabled
        if (strpos($_SERVER["PHP_SELF"], "runScheduledTasks.php") !== false) {
            $this->runScheduledTasks();
        }

        $registered = parent::register($category, $path);
        if ($registered && $this->getEnabled()) {
            HookRegistry::register("Template::Settings::workflow::review", [$this, "addReviewSettingsMenuItem"]);
            HookRegistry::register("TemplateManager::display", [$this, "onTemplateDisplay"]);
        }
        return $registered;
    }

    public function parseCronTab(string $hookName, array $args) {
        $args[0][] = $this->getPluginPath() . "/scheduledTasks.xml";
    }

    public function getSchema(string $hookName, array $args) {
        $args[0] = json_decode(file_get_contents($this->getPluginPath() . "/schema.json"));
    }

    public function onTemplateDisplay(string $hookName, array $args) {
        if ($args[1] == "management/workflow.tpl") {
            /** @var TemplateManager */
            $templateManager = $args[0];
            /** @var Request */
            $request = $this->getRequest();
            /** @var Context */
            $context = $request->getContext();
            $formLocales = $context->getSupportedFormLocaleNames();
            $url = Registry::get("request")->getBaseUrl() . "/" . $this->getPluginPath();
            $templateManager->addJavaScript("reviewRemindersScript", "$url/build/js/build.js", [
                "contexts" => "backend",
                "priority" => STYLE_SEQUENCE_LAST
            ]);
            $templateManager->addStyleSheet("reviewRemindersStyle",  "$url/build/css/build.css", [
                "contexts" => "backend"
            ]);
            /** @var ReviewReminderDAO */
            $dao = DAORegistry::getDAO("reviewReminders");
            $items = [];
            /** @var ReviewReminderDO */
            foreach ($dao->getAll($context->getId()) as $reminder) {
                $items[] = [
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
                ];
            }
            $templateManager->setState([
                "components" => array_merge($templateManager->getState("components"), [
                    "reviewRemindersList" => [
                        "items" => $items,
                        "form" => (new ReviewRemindersForm($request->getDispatcher()->url($request, ROUTE_API, $context->getPath(), "reviewReminders"), array_map(function($value, $key) {
                            return [
                                "key" => $key,
                                "label" => $value
                            ];
                        }, $formLocales, array_keys($formLocales)), $context))->getConfig(),
                        "addLabel" => __("plugins.generic.reviewReminders.frontend.addLabel"),
                        "editLabel" => __("plugins.generic.reviewReminders.frontend.editLabel"),
                        "deleteModalTitle" => __("plugins.generic.reviewReminders.frontend.deleteModalTitle"),
                        "deleteModalMessage" => __("plugins.generic.reviewReminders.frontend.deleteModalMessage"),
                        "addButtonLabel" => __("plugins.generic.reviewReminders.frontend.addButtonLabel"),
                        "editButtonLabel" => __("plugins.generic.reviewReminders.frontend.editButtonLabel"),
                        "deleteButtonLabel" => __("plugins.generic.reviewReminders.frontend.deleteButtonLabel")
                    ]
                ])
            ]);
        }
    }

    public function runScheduledTasks() {
        /** @var ScheduledTaskDAO */
        $taskDao = DAORegistry::getDAO('ScheduledTaskDAO');
        $xmlParser = new PKPXMLParser();
		$tree = $xmlParser->parse(__DIR__ . "/scheduledTasks.xml");

		foreach ($tree->getChildren() as $task) {
			$className = $task->getAttribute('class');

			$frequency = $task->getChildByName('frequency');
			if (isset($frequency)) {
				$canExecute = ScheduledTaskHelper::checkFrequency($className, $frequency);
			} else {
				// Always execute if no frequency is specified
				$canExecute = true;
			}

			if ($canExecute) {
				if (!is_object($instance = instantiate($className, null, null, 'execute', ScheduledTaskHelper::getTaskArgs($task)))) {
                    fatalError('Cannot instantiate task class.');
                }
                $taskDao->updateLastRunTime($className);
                $instance->execute();
			}
		}
    }

    public function addReviewSettingsMenuItem() {
        $tplManager = TemplateManager::getManager();
        $tplManager->display($this->getTemplateResource("settingsMenuItem.tpl"));
    }

    public function getInstallMigration() {
        return new ReviewRemindersMigration();
    }
}