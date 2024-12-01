<?php
session_start();
if (!isset($_SESSION['fields'])) {
    $_SESSION['fields'] = [];
}
if (!isset($_SESSION['data'])) {
    $_SESSION['data'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_field'])) {
    $newField = trim($_POST['field_name']);
    if (!empty($newField) && !in_array($newField, $_SESSION['fields'])) {
        $_SESSION['fields'][] = $newField;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_data'])) {
    $isDataValid = false;
    $newData = [];
    foreach ($_SESSION['fields'] as $field) {
        $value = trim($_POST[$field] ?? '');
        $newData[$field] = $value;
        if (!empty($value)) {
            $isDataValid = true;
        }
    }
    if ($isDataValid) {
        $_SESSION['data'][] = $newData;
    } else {
        $error = "At least one field must be filled!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_data'])) {
    $_SESSION['data'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_field'])) {
    $fieldToDelete = $_POST['field_to_delete'];
    if (($key = array_search($fieldToDelete, $_SESSION['fields'])) !== false) {
        unset($_SESSION['fields'][$key]);
        foreach ($_SESSION['data'] as &$row) {
            unset($row[$fieldToDelete]);
        }
        $_SESSION['fields'] = array_values($_SESSION['fields']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_excel'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=data.xls");
    echo "SN\t" . implode("\t", $_SESSION['fields']) . "\n";
    foreach ($_SESSION['data'] as $index => $row) {
        echo ($index + 1) . "\t";
        foreach ($_SESSION['fields'] as $field) {
            echo (isset($row[$field]) ? $row[$field] : '') . "\t";
        }
        echo "\n";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Data Entry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f9;
        }
        h1, h2 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 8px;
            margin: 5px 0;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 15px;
            background-color: #09186d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0d265c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            text-align: center;
            padding: 10px;
        }
        th {
            background-color: #09186d;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Custom Data Entry App</h1>

    <form method="POST">
        <label for="field_name">Add Field:</label>
        <input type="text" name="field_name" id="field_name" placeholder="Enter field name" required>
        <button type="submit" name="add_field">Add Field</button>
    </form>

    <form method="POST">
        <label for="field_to_delete">Delete Field:</label>
        <select name="field_to_delete" id="field_to_delete" required>
            <option value="">Select Field</option>
            <?php foreach ($_SESSION['fields'] as $field): ?>
                <option value="<?= htmlspecialchars($field) ?>"><?= htmlspecialchars($field) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="delete_field">Delete Field</button>
    </form>

    <h2>Current Fields:</h2>
    <ul>
        <?php foreach ($_SESSION['fields'] as $field): ?>
            <li><?= htmlspecialchars($field) ?></li>
        <?php endforeach; ?>
    </ul>

    <h2>Enter Data:</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <?php foreach ($_SESSION['fields'] as $field): ?>
            <label for="<?= $field ?>"><?= htmlspecialchars($field) ?>:</label>
            <input type="text" name="<?= $field ?>" id="<?= $field ?>" placeholder="Enter <?= htmlspecialchars($field) ?>"><br>
        <?php endforeach; ?>
        <button type="submit" name="add_data">Add Data</button>
    </form>

    <h2>Data Table:</h2>
    <form method="POST">
        <button type="submit" name="clear_data">Clear All Data</button>
        <button type="submit" name="export_excel">Export to Excel</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>SN</th>
                <?php foreach ($_SESSION['fields'] as $field): ?>
                    <th><?= htmlspecialchars($field) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['data'] as $index => $row): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <?php foreach ($_SESSION['fields'] as $field): ?>
                        <td><?= htmlspecialchars($row[$field] ?? '') ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
