<?php

import("lib.pkp.classes.task.ReviewReminder");
class ReviewRemindersTask extends ReviewReminder {

    public function executeActions() {
        /** @var ReviewAssignmentDAO */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $incompleteAssignments = $reviewAssignmentDao->getIncompleteReviewAssignments();
        /** @var SubmissionDAO */
        $submissionDao = DAORegistry::getDAO('SubmissionDAO');
        /** @var ReviewAssignment */
        foreach ($incompleteAssignments as $reviewAssignment) {
            /** @var Submission */
            $submission = $submissionDao->getById($reviewAssignment->getSubmissionId());
            if (!$submission) {
                continue;
            }

            if ($submission->getStatus() != STATUS_QUEUED) {
                continue;
            }

            $contextId = $submission->getContextId();
            /** @var ContextService */
            $contextService = Services::get("context");
            $context = $contextService->get($contextId);
            /** @var ReviewReminderDAO */
            $reminderDao = DAORegistry::getDAO("reviewReminders");
            foreach ($reminderDao->getAll($contextId) as $reminder) {
                $calcTime = $reminder->getData("days") * 24 * 60 * 60;
                if ($reminder->getData("beforeOrAfter") == "before") {
                    $calcTime *= -1;
                }
                $dateReminded = strtotime($reviewAssignment->getDateReminded());
                if ($reminder->getData("deadline") == "response") {
                    if ($reviewAssignment->getDateConfirmed() != null) {
                        continue;
                    }
                    $remindTime = strtotime($reviewAssignment->getDateResponseDue()) + $calcTime;
                    if ($remindTime < time() && ($dateReminded === false || $remindTime > $dateReminded)) {
                        $this->sendReminder($reviewAssignment, $submission, $context, $reminder->getData("templateId"));
                    }
                    
                }
                else {
                    if ($reviewAssignment->getDateConfirmed() === null) {
                        continue;
                    }
                    $remindTime = strtotime($reviewAssignment->getDateDue()) + $calcTime;
                    if ($remindTime < time() && ($dateReminded === false || $remindTime > $dateReminded)) {
                        $this->sendReminder($reviewAssignment, $submission, $context, $reminder->getData("templateId"));
                    }
                }
            }
        }
        return true;
    }
    
}