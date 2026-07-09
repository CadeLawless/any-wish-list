<?php
/**
 * Variables available in this view:
 * @var string $title
 * @var array $user
 * @var string $content
 * @var string $customStyles
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/public/images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="/public/css/styles.css?v=2.9" />
    <link rel="stylesheet" type="text/css" href="/public/css/snow.css?v=2.2" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <title><?= htmlspecialchars($title ?? 'Any Wish List'); ?></title>
    <style>
        #body {
            padding-top: 20px;
        }
        #container {
            margin-top: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: calc(100vh - 180px);
        }
    <?php echo $customStyles ?? ""; ?>
    </style>
</head>
<body class="<?php echo isset($user['dark']) && $user['dark'] === 'Yes' ? 'dark' : ''; ?>">
    <div id="body">
        <div id="container">
            <?php echo $content; ?>
        </div>
    </div>
    <?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
