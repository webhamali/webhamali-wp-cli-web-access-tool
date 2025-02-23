<?php
session_start();

// ==========================
// TITLE: WEBHAMALI WP-CLI WEB ACCESS TOOL
// AUTHOR: WEBHAMALI
// SITE URL: https://webhamali.com/
// LICENSE: GPL V3
// VERSION: 1.0
// ==========================

// ==========================
// CONFIGURATION SETTINGS
// ==========================
$USERNAME = "admin";             // Change this!
$PASSWORD = "securepass123";     // Change this!
$ALLOWED_IPS = [];               // Optional: add allowed IPs if needed and separate with (,)
$ALLOWED_COMMANDS = []; // Optional: if not empty, only these commands are allowed


$logFile = 'wp-cli.log';
$loginError = "";

//Variable to store working WP-CLI command
$cliCheck = "";

// ==========================
// AUTHENTICATION & LOGIN FORM
// ==========================
if (!isset($_SESSION['authenticated'])) {
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login_user'], $_POST['login_pass'])) {
        if ($_POST['login_user'] === $USERNAME && $_POST['login_pass'] === $PASSWORD) {
            $_SESSION['authenticated'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $loginError = "Invalid username or password. Please try again.";
        }
    }
    
    // Output login form and exit if not authenticated
    echo '<!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <title>Login - WP CLI Web Access</title>
      <style>
          body {
              background: #151540;
              font-family: Arial, sans-serif;
              margin: 0;
              display: flex;
              justify-content: center;
              align-items: center;
              height: 80vh;
          }
          .login-container {
              background: #fff;
              padding: 25px;
              border-radius: 8px;
              box-shadow: 0 0 15px rgba(0,0,0,0.2);
              width: 300px;
              text-align: center;
          }
          .login-container h2 {
              margin-bottom: 15px;
              font-size: 18px;
              color: #444;
          }
          .login-container input {
              width: 250px;
              padding: 10px;
              margin: 8px 0;
              border: 1px solid #ccc;
              border-radius: 4px;
              font-size: 14px;
          }
          .login-container button {
              width: 270px;
              background: #614bc3;
              color: #fff;
              margin-top:10px;
              padding: 10px;
              border: none;
              border-radius: 4px;
              cursor: pointer;
              font-size: 14px;
          }
          .login-container button:hover {
              background: #523fa5;
          }
          .error-message {
              background: #ff4d4d;
              color: white;
              padding: 8px;
              border-radius: 4px;
              font-size: 12px;
              margin-bottom: 10px;
          }
      </style>
    </head>
    <body>
      <div class="login-container">
          <h2>Login to WH WP CLI Web Access</h2>
          ' . (!empty($loginError) ? '<div class="error-message">' . $loginError . '</div>' : '') . '
          <form method="POST">
              <input type="text" name="login_user" placeholder="Username" required>
              <input type="password" name="login_pass" placeholder="Password" required>
              <button type="submit">Login</button>
          </form>
      </div>
    </body>
    </html>';
    exit;
}

// ==========================
// LOGOUT HANDLING
// ==========================
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==========================
// IP RESTRICTION
// ==========================
if (!empty($ALLOWED_IPS) && !in_array($_SERVER['REMOTE_ADDR'], $ALLOWED_IPS)) {
    die("Access denied: Your IP is not allowed.");
}

// ==========================
// LOG FILE INITIALIZATION
// ==========================
if (!file_exists($logFile)) {
    file_put_contents($logFile, "WP CLI Log Initialized on " . date('Y-m-d H:i:s') . "\n\n");
}

// ==========================
// DETECT WORKING WP-CLI COMMAND
// ==========================
if (!isset($_SESSION['cliCheck'])) {
    $wpCliCom = ["wp", "wp-cli", "wp-cli.phar"];
    foreach ($wpCliCom as $candidate) {
        // Run the candidate with --version (suppress errors via 2>&1)
        $versionOutput = shell_exec($candidate . " --version 2>&1");
        if ($versionOutput && strpos($versionOutput, "command not found") === false) {
            $cliCheck = $candidate;
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Detected working WP CLI command: $cliCheck\n", FILE_APPEND);
            break;
        }
    }
    $_SESSION['cliCheck'] = $cliCheck;
} else {
    $cliCheck = $_SESSION['cliCheck'];
}

// ==========================
// AJAX HANDLING: COMMAND EXECUTION & LOG LOADING
// ==========================
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['command'])) {
    $timestamp = date('Y-m-d H:i:s');
    $command = trim($_POST['command']);
    if (!empty($command)) {
        // If allowed commands are defined, enforce them
        if (!empty($ALLOWED_COMMANDS)) {
            $allowed = false;
            foreach ($ALLOWED_COMMANDS as $allowedCommand) {
                if (strpos($command, $allowedCommand) === 0) {
                    $allowed = true;
                    break;
                }
            }
            if (!$allowed) {
                 file_put_contents($logFile, "[$timestamp] Command not allowed: $command\n", FILE_APPEND);
                exit;
            }
        }
        file_put_contents($logFile, "[$timestamp] Running: $command\n", FILE_APPEND);
        $fullCommand = escapeshellcmd($command) . " 2>&1";
        $output = shell_exec($fullCommand);
        file_put_contents($logFile, $output . "\n", FILE_APPEND);
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'load_log') {
    echo file_get_contents($logFile);
    exit;
}

// Handle log download before any output
if (isset($_GET['download_log'])) {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="wp-cli.log"');
    readfile($logFile);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>WebHamali WP CLI Web Access</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #151540;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            height: 80vh;
        }
        .container {
            width:800px;
            margin: auto;
            background: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 20px;
            color: #444;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            padding: 10px 15px;
            font-size: 14px;
            background-color: #614bc3;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #523fa5;
        }
        .log {
            background: #000;
            color: #0f0;
            padding: 10px;
            border-radius: 4px;
            height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }
        .log h3 {
            color: #fff;
            margin-top: 0;
        }
        .logout, .get-log{
            float: right;
            color: #444;
            text-decoration: none;
            font-size: 12px;
            padding-left:10px;
        }
        
        .logout:hover, .get-log:hover{
            text-decoration: underline;
        }
        
        /* New command list styling */
        .command-list {
            margin-top: 20px;
            background: #eee;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .command-list h3 {
            margin-top: 0;
            color: #444;
            font-size:16px;
        }
        .command-list ul {
            list-style: none;
            padding: 0;
        }
        .command-list ul li {
            cursor: pointer;
            padding: 5px;
            margin: 3px 0;
            background: #f7f7f7;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .command-list ul li:hover {
            background: #eaeaea;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>
        WebHamali WP CLI Web Access 
        <a href="?logout" class="logout">Logout</a> 
        <a href="?download_log" class="get-log">Download Log</a>
    </h1>
    <form id="commandForm">
        <input type="text" id="commandInput" name="command" placeholder="Enter WP CLI command" autofocus>
        <button type="submit">Run Command</button>
    </form>
    <div class="log" id="logContainer">
        <h3>Log Output</h3>
        <pre id="logContent">Loading log...</pre>
    </div>
    <!-- New Command List Section -->
    <div class="command-list">
        <h3>Quick access:</h3>
        <ul>
            <?php
                // If $ALLOWED_COMMANDS is defined (not empty), list them.
                // Otherwise, show default basic commands using the detected working command.
                if (!empty($ALLOWED_COMMANDS)) {
                    foreach ($ALLOWED_COMMANDS as $cmd) {
                        echo '<li data-command="' . htmlspecialchars($cmd, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($cmd, ENT_QUOTES, 'UTF-8') . '</li>';
                    }
                } else {
                    // Use detected working command; if not detected, default to "wp"
                    $cliCmd = ($cliCheck !== "") ? $cliCheck : "wp";
                    echo '<li data-command="' . htmlspecialchars($cliCmd . " help", ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($cliCmd . " help", ENT_QUOTES, 'UTF-8') . '</li>';
                    echo '<li data-command="' . htmlspecialchars($cliCmd . " cache flush", ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($cliCmd . " cache flush", ENT_QUOTES, 'UTF-8') . '</li>';
                    echo '<li data-command="' . htmlspecialchars($cliCmd . " plugin update", ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($cliCmd . " plugin update", ENT_QUOTES, 'UTF-8') . '</li>';
                    echo '<li data-command="' . htmlspecialchars($cliCmd . " theme update", ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($cliCmd . " theme update", ENT_QUOTES, 'UTF-8') . '</li>';
                    echo '<li data-command="' . htmlspecialchars($cliCmd . " core update", ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($cliCmd . " core update", ENT_QUOTES, 'UTF-8') . '</li>';
                    echo '<li>Find more at: <a href="https://developer.wordpress.org/cli/commands/" target="_blank">docs</a></li>';
                }
            ?>
        </ul>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("commandForm");
    const commandInput = document.getElementById("commandInput");
    const logContent = document.getElementById("logContent");
    const logContainer = document.getElementById("logContainer");

    function loadLog() {
        fetch("?action=load_log")
            .then(response => response.text())
            .then(data => {
                logContent.textContent = data;
                logContainer.scrollTop = logContainer.scrollHeight;
            });
    }

    loadLog();

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        const command = commandInput.value.trim();
        if (command === "") return;
        fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "command=" + encodeURIComponent(command)
        })
        .then(response => response.text())
        .then(() => {
            commandInput.value = "";
            loadLog();
        });
    });

    //If you want to reload the log file after set interval, add the commented section below.
    //setInterval(loadLog, 5000);

    // When a command from the $ALLOWED_COMMANDS list is clicked, insert it into the input field.
    document.querySelectorAll('.command-list ul li').forEach(function(item) {
        item.addEventListener('click', function() {
            const cmd = this.getAttribute('data-command');
            commandInput.value = cmd;
            commandInput.focus();
        });
    });
});
</script>

</body>
</html>