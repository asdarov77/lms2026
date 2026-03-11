# UI Design - Tables, Forms, Dialogs, States

## Courses Table (Courses.vue)
Columns:
- checkbox (selection)
- id (number) sortable
- title (string) sortable, filter by q
- aircraft (string) filterable
- category (string) filterable
- author (string) filterable
- status (enum: draft/published/archived) filterable
- students_count (number) sortable
- avg_progress (percent) sortable
- price (numeric) sortable, filter range
- created_at (date) sortable
- actions (edit, duplicate, archive, delete)

Bulk actions: publish, unpublish, archive, delete, assign to group

Row actions: view, edit, duplicate, archive, delete (confirm)

Form: CourseEditModal - fields: title(required, min 3), short_description(max 500), long_description(html), aircraft(select), categories(multiselect), price(number >=0), visible(switch), cover_image(file)
Validation: required fields; client-side validation + API validation; show field-level errors returned from server in standard error envelope.

## Lessons List (per course)
Columns: order(handle draggable), id, title, type(video/pdf/html/scorm), duration, is_visible, actions(edit, delete, move)
Reorder: drag & drop -> call PATCH /api/v1/courses/{id}/lessons/reorder with new positions. Show saving state per row, rollback on failure.

## Tests Table
Columns: id, title, linked_course, questions_count, time_limit, attempts_allowed, status, actions(preview, edit, duplicate, delete)

## Assignments Table
Columns: id, title, course, student, file_preview, submitted_at, due_date, grade, status, actions(download, grade, request_revision)

## Audit Logs
Columns: id, user, action, target_type, target_id, ip, user_agent, created_at
Filters: date range, user, action, ip
Retention: show in UI but archive older entries to cold storage

## State flows (example: Delete Course)
1. User clicks Delete -> show ConfirmDialog with course title and 'type TO DELETE' input to prevent accidental deletion.
2. On Confirm: disable UI controls, show spinner, call DELETE /api/v1/courses/{id}
3. On success: remove course from store, show toast 'Course deleted' and offer undo via snackbar (undo triggers restore API if soft deletes enabled)
4. On error: show error dialog with retry option, re-enable controls, log error to Sentry.