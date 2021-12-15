describe("Review Reminders plugin tets", function() {
    it("Creates a custom Review Reminder", function() {
        cy.login("admin", "admin", "publicknowledge");
        
        cy.get(".app__nav a").contains("Website").click();
        cy.get("#plugins-button").click();
        cy.get('input[id^="select-cell-reviewremindersplugin-enabled"]').click();
        cy.get('div:contains(\'The plugin "Extended Review Reminders" has been enabled.\')');

        cy.login("admin", "admin", "publicknowledge");
        cy.get(".app__nav a").contains("Workflow").click();
        cy.get("#review-button").click();
        cy.get("#reviewReminders-button").click();

        cy.get("div:contains('5 days before the Review Deadline')").should("not.exist");

        cy.get("button").contains("Add Reminder").click();
        cy.get("#review_reminders-days-control").type("5");
        cy.get("#review_reminders-beforeOrAfter-control").select("before");
        cy.get("#review_reminders-deadline-control").select("review");
        cy.get("#review_reminders-templateId-control").select("ANNOUNCEMENT");

        cy.get("button:visible").contains("Save").click();

        cy.get("div:contains('5 days before the Review Deadline')");

        cy.reload();

        cy.get("div:contains('5 days before the Review Deadline')");

        cy.get("button:visible").contains("Delete").click();
        cy.get("button:visible").contains("Yes").click();

        cy.get("div:contains('5 days before the Review Deadline')").should("not.exist");

        cy.reload();
        
        cy.get("div:contains('5 days before the Review Deadline')").should("not.exist");
    })
})