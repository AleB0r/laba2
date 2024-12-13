<?php
use PHPUnit\Framework\TestCase;


require_once 'db.php';
require_once 'registerController.php';

class RegisterUserTest extends TestCase
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


    public function testRegisterUserSuccess()
    {
        $username = "newuserSuccess";
        $password = "password123";
        $confirm_password = "password123";
        $skills = [3]; 

        
        $result = registerUser($this->conn, $username, $password, $confirm_password, $skills);

      
        $this->assertEquals('Registration successful!', $result);
    }

   
    public function testRegisterUserUsernameWithSpaces()
    {
     
        $username = "new user"; 
        $password = "password123";
        $confirm_password = "password123";
        $skills = [3];

        $result = registerUser($this->conn, $username, $password, $confirm_password, $skills);

        $this->assertEquals('Username cannot contain spaces!', $result);
    }

    public function testRegisterUserPasswordsDontMatch()
    {
        $username = "newuser";
        $password = "password123";
        $confirm_password = "password124"; 
        $skills = [3];

       
        $result = registerUser($this->conn, $username, $password, $confirm_password, $skills);

       
        $this->assertEquals('Passwords do not match!', $result);
    }

    
    public function testRegisterUserNoSkillsSelected()
    {
       
        $username = "newuser1234";
        $password = "password123";
        $confirm_password = "password123";
        $skills = []; 

        
        $result = registerUser($this->conn, $username, $password, $confirm_password, $skills);

        
        $this->assertEquals('Please select at least one skill!', $result);
    }

}
