<?php

import("lib.pkp.classes.db.SchemaDAO");
class ReviewReminderDAO extends SchemaDAO {
    public $schemaName = "reviewReminders";
    public $tableName = "review_reminders";
    public $primaryKeyColumn = "id";
    public $primaryTableColumns = [
        "id" => "id",
        "contextId" => "contextId",
        "days" => "days",
        "beforeOrAfter" => "beforeOrAfter",
        "deadline" => "deadline",
        "templateId" => "templateId"
    ];

    public function newDataObject() {
        return new ReviewReminderDO();
    }

    /** @return ReviewReminderDO[] */
    public function getAll($contextId) {
        $result = [];
        foreach ($this->retrieve("SELECT * FROM $this->tableName WHERE contextId = ?", [$contextId]) as $row) {
            $result[] = $this->_fromRow((array) $row);
        }
        return $result;
    }

    public function _fromRow($primaryRow) {
        $schemaService = Services::get('schema');
        $schema = $schemaService->get($this->schemaName);

        $object = $this->newDataObject();

        foreach ($this->primaryTableColumns as $propName => $column) {
            if (isset($primaryRow[$column])) {
                $object->setData(
                    $propName,
                    $this->convertFromDb($primaryRow[$column], $schema->properties->{$propName}->type)
                );
            }
        }

        return $object;
    }

    public function deleteById($objectId) {
        $this->update(
            "DELETE FROM {$this->tableName} WHERE {$this->primaryKeyColumn} = ?",
            [(int) $objectId]
        );
    }
}