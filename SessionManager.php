<?php 
class SessionManager {

    //checks if $_SESSION['loggedIn'] exists and then checks if it is true 
    public function isLoggedIn(): bool {
        return isset($_SESSION["user_id"]);
        
    }  
    
    public function authenticate(string $email, string $password): bool {
        // Database connection parameters
        $config = include 'config.php';
        try {
            $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_user'], $config['db_pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("SELECT user_id, password_hash FROM user WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && password_verify($password, $result['password_hash'])) {
                $_SESSION['user_id'] = $result['user_id'];
                return true;
            }
        } catch (PDOException $e) {
            // Handle exception (log it, rethrow it, etc.)
        }
        return false;
    }
    //this method destorys the session to log the user out
    public static function logout(): void {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
    //this method redirects the user to the login page
    public function redirectToLogin(): void {
        header("Location: login.php");
        exit();
    }


}

?>


