# Project Management API Documentation

This API provides endpoints for managing a drag-and-drop project management system with columns and tasks.

## Base URL

```
http://localhost:8000/api
```

## Authentication

_This API implementation does not include authentication. In production, add an appropriate authentication mechanism._

## Boards

### List all boards

```
GET /boards
```

### Create a new board

```
POST /boards

{
  "name": "My Project",
  "description": "Project description"
}
```

### Get a specific board with columns and tasks

```
GET /boards/{board_id}
```

### Update a board

```
PUT /boards/{board_id}

{
  "name": "Updated Project Name",
  "description": "Updated description"
}
```

### Delete a board

```
DELETE /boards/{board_id}
```

## Columns

### List all columns for a board

```
GET /boards/{board_id}/columns
```

### Create a new column

```
POST /boards/{board_id}/columns

{
  "title": "New Column"
}
```

### Get a specific column with its tasks

```
GET /columns/{column_id}
```

### Update a column

```
PUT /columns/{column_id}

{
  "title": "Updated Column Name"
}
```

### Delete a column

```
DELETE /columns/{column_id}
```

### Move a single column (drag and drop)

```
POST /columns/{column_id}/move

{
  "position": 2
}
```

### Update multiple column positions (bulk reordering)

```
POST /boards/{board_id}/column-positions

{
  "columns": [
    {
      "id": 1,
      "position": 0
    },
    {
      "id": 2,
      "position": 1
    },
    {
      "id": 3,
      "position": 2
    }
  ]
}
```

## Tasks

### List all tasks for a column

```
GET /columns/{column_id}/tasks
```

### Create a new task

```
POST /columns/{column_id}/tasks

{
  "title": "New Task",
  "description": "Task description",
  "tag": "Feature",
  "due_date": "2024-06-30",
  "assigned_to": "John Doe",
  "priority": "high"
}
```

### Get a specific task

```
GET /tasks/{task_id}
```

### Update a task

```
PUT /tasks/{task_id}

{
  "title": "Updated Task",
  "description": "Updated description",
  "tag": "Bug",
  "due_date": "2024-07-15",
  "assigned_to": "Jane Smith",
  "priority": "medium"
}
```

### Delete a task

```
DELETE /tasks/{task_id}
```

### Move a task (drag and drop between columns or within a column)

```
POST /tasks/{task_id}/move

{
  "column_id": 2,
  "position": 3
}
```

Response:
```json
{
  "message": "Task moved successfully",
  "affected_columns": [
    {
      "id": 1,
      "title": "To Do",
      "tasks": [...]
    },
    {
      "id": 2,
      "title": "In Progress",
      "tasks": [...]
    }
  ]
}
```

### Update multiple task positions within a column (bulk reordering)

```
POST /columns/{column_id}/task-positions

{
  "tasks": [
    {
      "id": 1,
      "position": 0
    },
    {
      "id": 2,
      "position": 1
    },
    {
      "id": 3,
      "position": 2
    }
  ]
}
```

## Data Models

### Board

- `id`: Unique identifier
- `name`: Board name
- `description`: Board description

### Column

- `id`: Unique identifier
- `board_id`: ID of the parent board
- `title`: Column title
- `position`: Column order position

### Task

- `id`: Unique identifier
- `column_id`: ID of the parent column
- `title`: Task title
- `description`: Task description
- `tag`: Task category or tag
- `position`: Task order position within column
- `due_date`: Due date for task completion
- `assigned_to`: Person assigned to the task
- `priority`: Task priority (low, medium, high)

## Frontend Implementation Examples

### Loading a Board with Columns and Tasks

```javascript
// Example fetch request to get a board with its columns and tasks
fetch('/api/boards/1')
  .then(response => response.json())
  .then(board => {
    console.log('Board:', board.name);
    board.columns.forEach(column => {
      console.log('Column:', column.title);
      column.tasks.forEach(task => {
        console.log('Task:', task.title);
      });
    });
  });
```

### Moving a Task Between Columns

```javascript
// Example fetch request to move a task
fetch('/api/tasks/5/move', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    column_id: 2,  // Target column ID
    position: 1    // New position in the column
  }),
})
  .then(response => response.json())
  .then(result => {
    console.log(result.message);
    // Update UI with the affected columns' tasks
    result.affected_columns.forEach(column => {
      console.log(`Updated column ${column.title} with ${column.tasks.length} tasks`);
      // Update the column's tasks in the UI
    });
  });
```

### Dragging and Dropping a Column

```javascript
// Example fetch request to move a column
fetch('/api/columns/3/move', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    position: 1  // New position for the column
  }),
})
  .then(response => response.json())
  .then(column => console.log('Column moved to position:', column.position));
```

### Reordering Multiple Tasks at Once

```javascript
// Example fetch request to reorder multiple tasks in a column
fetch('/api/columns/2/task-positions', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    tasks: [
      { id: 4, position: 0 },
      { id: 7, position: 1 },
      { id: 9, position: 2 }
    ]
  }),
})
  .then(response => response.json())
  .then(tasks => console.log('Tasks reordered, new task count:', tasks.length));
```

## Setup Instructions

1. Run database migrations: `php artisan migrate`
2. Seed the database with sample data: `php artisan db:seed`
3. Start the server: `php artisan serve` 