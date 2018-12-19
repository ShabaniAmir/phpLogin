<?php
class Login{
  var $db_host = "";
  var $db_username = "";
  var $db_password = "";
  var $db_table = "";
  var $db_name = "";

  var $conn;

  function __construct(){
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }
    Login::initDB();
  }

  function initDB(){
    $this->conn = new mysqli($this->db_host, $this->db_username, $this->db_password, $this->db_name);
    if($this->conn->connect_error){
      die ("Failed to connect to database in login.php");
    }else{
      #echo "connection established with database";
    }
  }

  // Check if user is logged in for pages that require authentication
  public function checkUser(){
    if($_SESSION["logged_in"]){
      return true;
    }else{
      header('Location: ' . $website_url . 'login/');
      exit();
    }
  }

  // Log user out
  public static function logout(){
    session_unset();
    session_destroy();
  }

  // Login user
  public function processLogin($username, $password, &$error, $redirect_url = ""){
    $username = $this->conn->real_escape_string($username);
    $sql = "SELECT * FROM `users` WHERE `username` = '$username'";
    $result = $this->conn->query($sql);
    if(!$result){
      $error .= "Incorrect Username or Password\n";
      return;
    }
    if($result->num_rows < 1){
      $error .= "Incorrect Username or Password\n";
      return;
    }else{
      $row = $result->fetch_assoc();
      if(password_verify($password, $row["password"])){
        #TODO: setup session, etc.
        $_SESSION["logged_in"] = TRUE;
        $_SESSION["username"] = $username;
        echo "<script>alert('login successful');</script>";
      }else{
        $error .= "Incorrect Username or Password\n";
        return;
      }
    }
  }

  // Register User
  public function processAddUser($userInfo, &$error){
    // array($_POST["firstname"], $_POST["lastname"], $_POST["email"], $_POST["username"], $_POST["password"]);
    // Sanitize input
    for($i = 0; $i < count($userInfo); $i++){
      $userInfo[$i] = $this->conn->real_escape_string($userInfo[$i]);
      $userInfo[$i] = strip_tags($userInfo[$i]);
    }

    // Hash password
    $password = password_hash($userInfo[4], PASSWORD_DEFAULT);

    // Verify that user does not already exists
    $sql = "Select * FROM `users` WHERE `username` = '$userInfo[3]'";
    $result = $this->conn->query($sql);
    if($result->num_rows > 0){
      $error .= "Username Already Exists";
      return;
    }
    $sql = "Select * FROM `users` WHERE `email` = '$userInfo[2]'";
    $result = $this->conn->query($sql);
    if($result->num_rows > 0){
      $error .= "Email Already Exists";
      return;
    }

    // Insert into database
    $sql = "INSERT INTO `users` ( `username`, `password`, `firstname`, `lastname`, `email`) VALUES ('$userInfo[3]', '$password', '$userInfo[0]', '$userInfo[1]', '$userInfo[2]'); ";
    if($this->conn->query($sql) === TRUE){
      echo "<script>alert('Successfully Added User');</script>";
    }else{
      echo "<script>alert('Failed to add user. Please contact technical Support');</script>";
    }
  }
}
 ?>
