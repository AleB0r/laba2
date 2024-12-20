<?php
session_start();
include 'db.php';
include 'header.php';


echo "<link rel='stylesheet' href='css/style.css'>";


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'user') {
    header('Location: login.php');
    exit();  
}


if (isset($_SESSION['message'])) {
    echo "<script>alert('" . addslashes($_SESSION['message']) . "');</script>";
    unset($_SESSION['message']);
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username']; 

$secret_key = hash('sha256', $username . $user_id);



function encrypt($data, $secret_key) {
    $iv = random_bytes(16); 
    $encrypted_data = openssl_encrypt($data, 'aes-256-cbc', $secret_key, 0, $iv);
    return base64_encode($iv . $encrypted_data);
}


function decrypt($data, $secret_key) {
    $data = base64_decode($data);
    $iv = substr($data, 0, 16); 
    $encrypted_data = substr($data, 16); 
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $secret_key, 0, $iv);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_type'], $_POST['search_query'])) {
        $new_search_type = $_POST['search_type'];
        $new_search_query = $_POST['search_query'];

        
        $cookie_search_type_name = 'search_type_' . $user_id;
        $cookie_search_query_name = 'search_query_' . $user_id;

        
        $encrypted_search_type = encrypt($new_search_type, $secret_key);
        $encrypted_search_query = encrypt($new_search_query, $secret_key);

       
        echo "<script>
                document.cookie = '{$cookie_search_type_name}={$encrypted_search_type}'; 
                document.cookie = '{$cookie_search_query_name}={$encrypted_search_query}';
                window.location.href = 'search_task.php?search_type=' + encodeURIComponent('{$new_search_type}') + '&search_query=' + encodeURIComponent('{$new_search_query}');
              </script>";
        exit();  
    }

    
    $sql = "SELECT * FROM tasks WHERE user_id = '$user_id'";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception($conn->error);
    }

    echo "<div class='task-manager-container'>";
    echo "<h1>Task Manager</h1>";
    echo "<a href='add_task.php' class='add-task-link'>Add New Task</a><br><br>";

    if ($result->num_rows > 0) {
        echo "<table class='task-table'>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Reminder Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            $checked = ($row['status'] === 'complete') ? 'checked' : '';
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['due_date']}</td>
                    <td>{$row['reminder_time']}</td>
                    <td>
                        <form action='complete_task.php' method='post' style='display:inline;'>
                            <input type='hidden' name='task_id' value='{$row['id']}'>
                            <input type='checkbox' name='status' value='complete' onchange='this.form.submit();' $checked>
                        </form>
                    </td>
                    <td>
                        <form action='edit_task.php' method='get' style='display:inline;'>
                            <input type='hidden' name='task_id' value='{$row['id']}'>
                            <button type='submit' class='edit-button' title='Edit'>&#9998;</button>
                        </form>
                        <form action='delete_task.php' method='post' style='display:inline;'>
                            <input type='hidden' name='task_id' value='{$row['id']}'>
                            <button type='submit' class='delete-button'>&#10005;</button>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='no-tasks'>No tasks found.</p>";
    }

    
    $cookie_search_type_name = 'search_type_' . $user_id;
    $cookie_search_query_name = 'search_query_' . $user_id;

    $search_type = isset($_COOKIE[$cookie_search_type_name]) ? decrypt($_COOKIE[$cookie_search_type_name], $secret_key) : 'title';
    $search_query = isset($_COOKIE[$cookie_search_query_name]) ? decrypt($_COOKIE[$cookie_search_query_name], $secret_key) : '';

    echo "<form method='POST' action='" . $_SERVER['PHP_SELF'] . "' class='form1'>
            <select name='search_type'>
                <option value='title' " . ($search_type === 'title' ? 'selected' : '') . ">Title</option>
                <option value='due_date' " . ($search_type === 'due_date' ? 'selected' : '') . ">Due Date</option>
            </select>
            <input type='text' name='search_query' placeholder='Search' value='" . htmlspecialchars($search_query) . "' required>
            <input type='submit' value='Search'>
          </form>";

} catch (Exception $e) {
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
}
?>

<script>
function decryptCookie(cookieValue, secretKey) {
    const ivLength = 16; 
    const data = atob(cookieValue); 
    const iv = data.slice(0, ivLength); 
    const encryptedData = data.slice(ivLength); 

    const ivBuffer = CryptoJS.enc.Hex.parse(iv);
    const encryptedDataBuffer = CryptoJS.enc.Base64.parse(encryptedData);

    const decrypted = CryptoJS.AES.decrypt(
        { ciphertext: encryptedDataBuffer },
        secretKey,
        { iv: ivBuffer }
    );

    return decrypted.toString(CryptoJS.enc.Utf8); 
}

const secretKey = '<?= $secret_key ?>';

const searchTypeCookieName = 'search_type_' + '<?= $user_id ?>';
const searchQueryCookieName = 'search_query_' + '<?= $user_id ?>';

const searchType = decryptCookie(document.cookie.split('; ').find(row => row.startsWith(searchTypeCookieName)).split('=')[1], secretKey);
const searchQuery = decryptCookie(document.cookie.split('; ').find(row => row.startsWith(searchQueryCookieName)).split('=')[1], secretKey);
</script>
