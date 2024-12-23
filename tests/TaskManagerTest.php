<?php
use PHPUnit\Framework\TestCase;

require_once 'db.php';
require_once 'task_manager.php';

class TaskManagerTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new mysqli('localhost', 'root', '', 'itivp');
        if ($this->conn->connect_error) {
            $this->fail("Connection failed: " . $this->conn->connect_error);
        }
    }

    protected function tearDown(): void
    {
        $this->conn->close();
    }


    public function testAddTaskSuccess()
    {
      
        $title = "Test Task";
        $description = "This is a test task description.";
        $due_date = "2024-12-14";
        $reminder_time = "12:00:00";
        $user_id = 15; 
        
        $result = addTask($this->conn, $title, $description, $due_date, $reminder_time, $user_id);

        $this->assertEquals("Task created successfully.", $result);
    }

    public function testAddTaskDuplicateTitle()
    {
        $title = "Duplicate Task";
        $description = "This is a test task description.";
        $due_date = "2024-12-14";
        $reminder_time = "12:00:00";
        $user_id = 15;

        addTask($this->conn, $title, $description, $due_date, $reminder_time, $user_id);

       
        $result = addTask($this->conn, $title, $description, $due_date, $reminder_time, $user_id);

        
        $this->assertEquals("Error: The title must be unique.", $result);
    }

    public function testAddTaskDueDateInPast()
    {

        $title = "Past Task";
        $description = "This task has a due date in the past.";
        $due_date = "2020-01-01"; 
        $reminder_time = "12:00:00";
        $user_id = 15;

        
        $result = addTask($this->conn, $title, $description, $due_date, $reminder_time, $user_id);


        $this->assertEquals("Error: The due date and reminder time must be in the future.", $result);
    }
}
