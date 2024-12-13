<?php
use PHPUnit\Framework\TestCase;

require_once 'db.php';

class DeleteTaskTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new mysqli('localhost', 'root', '', 'itivp');
        if ($this->conn->connect_error) {
            $this->fail("Connection failed: " . $this->conn->connect_error);
        }

        $this->conn->query("INSERT INTO tasks (id, title) VALUES (1, 'Test Task')");
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM tasks WHERE id = 1");
        $this->conn->close();
    }

    public function testDeleteTaskSuccess()
    {
        $_POST['task_id'] = 1;

        $_SESSION['user_id'] = 15;
        $_SESSION['user_type'] = 'user'; 

        ob_start();
        include 'delete_task.php'; 
        $output = ob_get_clean();

        $result = $this->conn->query("SELECT * FROM tasks WHERE id = 1");
        $this->assertEquals(0, $result->num_rows); 
    }

    public function testDeleteTaskNotFound()
    {
        $_POST['task_id'] = 9999;

        $_SESSION['user_id'] = 15;
        $_SESSION['user_type'] = 'user';

        ob_start();
        include 'delete_task.php'; 
        $output = ob_get_clean();

        $result = $this->conn->query("SELECT * FROM tasks WHERE id = 9999");
        $this->assertEquals(0, $result->num_rows); 
    }
}

