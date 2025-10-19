
# FreeFiles

## Project Overview

This project provides a simple file sharing platform. It allows users to upload files, add comments to them, and view them online. Built with PHP, it offers a basic solution for managing and sharing files.

## Key Features & Benefits

*   **File Uploading:** Allows users to upload files to the server.
*   **Commenting:** Enables users to add comments to uploaded files.
*   **File Viewing:** Provides a mechanism to view uploaded files.

## Prerequisites & Dependencies

Before you begin, ensure you have the following installed:

*   **PHP:** (Version 7.0 or higher recommended)
*   **Web Server:** (e.g., Apache, Nginx) with PHP support
*   **Database:** (e.g., MySQL, MariaDB) - for storing file metadata and comments (Although the existing files don't interact with the database, a functional implementation will require one)

## Installation & Setup Instructions

1.  **Clone the repository:**

    ```bash
    git clone <repository_url>
    cd FreeFiles
    ```

2.  **Set up your web server:**

    *   Configure your web server (Apache, Nginx, etc.) to point to the `FreeFiles` directory as the document root.

3.  **Configure the Database (Implementation Required - Not Included):**

    *   Create a database for the project.
    *   Update the `add_comment.php` and `index.php` files with your database connection details (hostname, username, password, database name).  **NOTE:** The provided files lack database interactions, so this requires modification to implement.

4.  **Grant Write Permissions:** Ensure the web server user has write permissions to the directory where files are uploaded.

    ```bash
    chmod -R 777 <upload_directory>
    ```
    Replace `<upload_directory>` with the path to the directory where files are stored. **WARNING:  Setting permissions to 777 is generally not recommended for production environments due to security risks.  Use more restrictive permissions where possible.**

## Usage Examples & API Documentation (if applicable)

*   **index.php:**  This is the main page of the application, listing uploaded files and allowing access to file details and commenting.
*   **upload.php:** This script handles the file uploading process. Users can upload files through a form, and this script processes the uploaded file and stores it on the server.
*   **add_comment.php:** This script is responsible for adding comments to files.  (Requires database integration to function properly - current implementation may not fully function without database configuration).

Example file upload form:
```html
<form action="upload.php" method="post" enctype="multipart/form-data">
    Select file to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload File" name="submit">
</form>
```

## Configuration Options

There are no explicit configuration files included. However, you might need to modify the following within the PHP files:

*   **Upload Directory:**  The directory where files are stored can be adjusted in `upload.php`.
*   **Database Connection Details:**  These need to be configured in `add_comment.php` and `index.php` if you implement database functionality.

## Contributing Guidelines

Contributions are welcome! To contribute:

1.  Fork the repository.
2.  Create a new branch for your feature or bug fix.
3.  Make your changes and commit them.
4.  Submit a pull request.

Please ensure your code adheres to coding standards and includes appropriate comments.

## License Information

No license is specified for this project.  Without a specified license, all rights are reserved by the copyright holder (GalipMert28).  This means you are not permitted to redistribute, modify, or use the code without explicit permission from the owner.  Consider adding a license such as MIT or Apache 2.0.

## Acknowledgments

N/A
