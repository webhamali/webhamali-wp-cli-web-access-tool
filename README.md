# WebHamali WP CLI Web Access Tool

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

WebHamali WP CLI Web Access Tool is a secure, browser-based interface for running WP CLI commands on your WordPress site. It provides authentication, detailed logging, and a dynamic command list to simplify site management tasks — all while ensuring proper credit and open access.

## Features

- **Secure Login:**  
  Protect access with a simple login form.

- **Command Execution:**  
  Run WP CLI commands via AJAX with real-time logging.

- **Dynamic Command List:**  
  Automatically detect the working WP CLI command and provide clickable command options for ease of use.

- **Log Viewing & Download:**  
  View command logs directly in your browser or download them as a plain (.log) text file.

- **Optional IP Restriction & Command Limiting:**  
  Enhance security by restricting access to specific IPs and allowed commands.

## Installation

1. **Clone the Repository:**

   ```bash
   git clone https://github.com/webhamali/webhamali-wp-cli-web-access-tool.git
   ```

2. **Upload to Your Server:**

   Place the files in a directory on your web server that can execute PHP.

3. **Configure Settings:**

   - Open the PHP file (e.g., `webhamali-wp-cli.php`).
   - Update `$USERNAME` and `$PASSWORD` for login credentials.
   - Set `$ALLOWED_IPS` if you wish to restrict access.
   - Adjust `$ALLOWED_COMMANDS` to limit which WP CLI commands can be run (leave empty for full access).
   - Ensure the server user has permission to execute shell commands and write to the log file (`wp-cli.log`).

## Usage

1. **Access the Tool:**  
   Navigate to the URL where the script is hosted and log in with your credentials.

2. **Execute Commands:**  
   Type a WP CLI command in the input field or select one from the available command list below the log container.

3. **View Logs:**  
   See the live log output in the interface. Use the "Download Log" link to save a copy.

4. **Logout:**  
   Click the "Logout" link to end your session.

## Version

**Version 1.0**

## License

This project is licensed under the GNU General Public License v3. See the [LICENSE](LICENSE) file for details.

## Author

**WebHamali**  
Site: [https://webhamali.com/](https://webhamali.com/)
