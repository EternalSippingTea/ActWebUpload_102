<?php
$uploadDir = __DIR__ . '/uploads';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $fileName = basename($_POST['delete_file']);
    $filePath = $uploadDir . '/' . $fileName;

    if (is_file($filePath) && strpos(realpath($filePath), realpath($uploadDir)) === 0) {
        if (unlink($filePath)) {
            $message = "Berkas \"$fileName\" berhasil dihapus.";
        } else {
            $message = "Gagal menghapus berkas \"$fileName\".";
        }
    } else {
        $message = "Berkas tidak ditemukan atau tidak dapat dihapus.";
    }
}

$files = [];

if (is_dir($uploadDir)) {
    foreach (scandir($uploadDir) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $uploadDir . '/' . $item;
        if (!is_file($path)) {
            continue;
        }

        $mime = mime_content_type($path);
        $preview = ['type' => 'other', 'value' => htmlspecialchars(strtoupper(pathinfo($item, PATHINFO_EXTENSION)) ?: 'FILE')];

        if (str_starts_with($mime, 'image/')) {
            $preview = ['type' => 'image', 'value' => 'uploads/' . rawurlencode($item)];
        } elseif (str_starts_with($mime, 'text/') || in_array($mime, ['application/json', 'application/xml', 'application/javascript', 'application/xhtml+xml', 'application/rss+xml'], true)) {
            $snippet = file_get_contents($path, false, null, 0, 200);
            if ($snippet === false) {
                $snippet = 'Unable to read file contents.';
                $preview = ['type' => 'text', 'value' => htmlspecialchars($snippet)];
            } else {
                $preview = ['type' => 'text', 'value' => htmlspecialchars(mb_substr($snippet, 0, 200))];
            }
        }

        $files[] = [
            'name' => $item,
            'size' => filesize($path),
            'modified' => date('Y-m-d H:i:s', filemtime($path)),
            'type' => $mime,
            'preview' => $preview,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Files</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .table-wrap {
            overflow-x: auto;
            margin-top: 24px;
            border-radius: 14px;
        }
        .file-table {
            width: 100%;
            min-width: 680px;
            border-collapse: collapse;
        }
        .file-table th,
        .file-table td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        /* Keep metadata columns on one line; let the name wrap */
        .file-table th:nth-child(3),
        .file-table td:nth-child(3),
        .file-table th:nth-child(4),
        .file-table td:nth-child(4),
        .file-table th:nth-child(5),
        .file-table td:nth-child(5),
        .file-table th:nth-child(6),
        .file-table td:nth-child(6) {
            white-space: nowrap;
        }
        .file-table td:nth-child(2) {
            word-break: break-all;
            max-width: 260px;
        }
        .file-table th {
            color: #334155;
            font-weight: 600;
        }
        .file-table tr:hover {
            background: #f8fafc;
        }
        .delete-form {
            display: inline-flex;
            margin: 0;
        }
        .action-group {
            display: inline-flex;
            flex-wrap: nowrap;
            gap: 8px;
            align-items: center;
        }
        .small-button {
            border: none;
            background: #ef4444;
            color: white;
            border-radius: 12px;
            padding: 10px 14px;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease;
            font-size: 0.95rem;
        }
        .small-button:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }
        .download-button {
            border: none;
            background: #10b981;
            color: white;
            border-radius: 12px;
            padding: 10px 14px;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
        }
        .download-button:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        .button-row {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .row-thumb {
            max-width: 76px;
            max-height: 76px;
            border-radius: 12px;
            display: block;
            object-fit: cover;
        }
        .preview-snippet {
            max-width: 220px;
            color: #475569;
            font-size: 0.88rem;
            line-height: 1.4;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .preview-icon {
            width: 76px;
            height: 76px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: #eef2ff;
            color: #1f2937;
            font-weight: 700;
            font-size: 0.95rem;
        }
        .view-button {
            border: none;
            background: #2563eb;
            color: white;
            border-radius: 12px;
            padding: 10px 14px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            font-size: 0.95rem;
            text-decoration: none;
        }
        .view-button:hover {
            background: #1d4ed8;
        }
        .preview-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 18px 20px;
            margin-bottom: 24px;
        }
        .preview-card h2 {
            margin: 0 0 12px;
            font-size: 1.1rem;
        }
        .preview-card p {
            margin: 0 10px 0 0;
            color: #475569;
        }
        .preview-image {
            max-width: 100%;
            border-radius: 14px;
            display: block;
            margin-top: 14px;
        }
        .preview-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            white-space: pre-wrap;
            word-break: break-word;
            font-size: 0.95rem;
            color: #0f172a;
            line-height: 1.5;
            background: #ffffff;
            border: 1px solid #dfe3ea;
            border-radius: 14px;
            padding: 14px;
            margin-top: 14px;
            overflow: auto;
            max-height: 300px;
        }
        .preview-warning {
            margin-top: 10px;
            color: #b45309;
            font-weight: 600;
        }
        .container {
            width: min(100%, 980px);
            max-width: 98vw;
        }
        .top-actions {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin-bottom: 18px;
        }
        .top-actions a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <main class="container">
        <header>
            <h1>Uploaded Files</h1>
        </header>

        <?php if ($message): ?>
            <div class="message<?= strpos($message, 'success') !== false ? ' success' : ' error' ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="top-actions">
                <span><?= count($files) ?> files found.</span>
                <a href="index.html">Back to upload</a>
            </div>

            <?php if (count($files) === 0): ?>
                <div class="message">No files in <code>uploads/</code>.</div>
            <?php else: ?>
                <div class="table-wrap">
                        <table class="file-table">
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Modified</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                            <tr>
                                <td>
                                    <?php if ($file['preview']['type'] === 'image'): ?>
                                        <img src="<?= htmlspecialchars($file['preview']['value']) ?>" alt="Pratinjau <?= htmlspecialchars($file['name']) ?>" class="row-thumb">
                                    <?php elseif ($file['preview']['type'] === 'text'): ?>
                                        <div class="preview-snippet"><?= nl2br($file['preview']['value']) ?></div>
                                    <?php else: ?>
                                        <div class="preview-icon"><?= $file['preview']['value'] ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($file['name']) ?></td>
                                <td><?= htmlspecialchars($file['type']) ?></td>
                                <td><?= number_format($file['size'] / 1024, 2) ?> KB</td>
                                <td><?= htmlspecialchars($file['modified']) ?></td>
                                <td>
                                    <div class="action-group">
                                        <a class="download-button" href="uploads/<?= rawurlencode($file['name']) ?>" download="<?= htmlspecialchars($file['name']) ?>">Download</a>
                                        <form class="delete-form" method="post" onsubmit="return confirm('Delete file <?= addslashes($file['name']) ?>?');">
                                            <input type="hidden" name="delete_file" value="<?= htmlspecialchars($file['name']) ?>">
                                            <button type="submit" class="small-button">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
