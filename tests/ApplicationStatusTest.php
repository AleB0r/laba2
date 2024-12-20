<?php
use PHPUnit\Framework\TestCase;

require_once 'db.php';

class ApplicationStatusTest extends TestCase
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

    public function testApplicationStatusesInIdRange()
    {
        $minId = 65;
        $maxId = 105;

        $query = "SELECT id, status FROM project_applications WHERE id BETWEEN $minId AND $maxId";
        $result = $this->conn->query($query);

        if ($result->num_rows === 0) {
            $this->markTestSkipped("No applications found in the ID range $minId to $maxId.");
        }

        $validStatuses = ['approved', 'rejected', 'pending'];
        $invalidIds = [];
        $totalRows = 0;

        while ($row = $result->fetch_assoc()) {
            $totalRows++;
            if (!in_array($row['status'], $validStatuses)) {
                $invalidIds[] = $row['id'];
            }
        }

        echo "\nTotal applications in range ($minId - $maxId): $totalRows\n";
        echo "Invalid application IDs: " . (empty($invalidIds) ? 'None' : implode(', ', $invalidIds)) . "\n";

        $this->assertEmpty(
            $invalidIds,
            "Found applications with invalid statuses in IDs: " . implode(', ', $invalidIds)
        );
    }
}
