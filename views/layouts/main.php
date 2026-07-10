<?php
/**
 * Variables available in this view:
 * @var string $title
 * @var string $content
 * @var array $user
 * @var string $currentPage
 * @var int $pageno
 * @var int $totalPages
 * @var string $currentPageUrl
 * @var int $wishlist_id
 * @var string $base_url
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css" integrity="sha512-zxBiDORGDEAYDdKLuYU9X/JaJo/DPzE42UubfBw9yg8Qvb2YRRIQ8v4KsGHOx2H1/+sdSXyXxLXv5r7tHc9ygg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js" integrity="sha512-Gs+PsXsGkmr+15rqObPJbenQ2wB3qYvTHuJO6YJzPe/dTLvhy0fmae2BcnaozxDo5iaF8emzmCZWbQ1XXiX2Ig==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <title><?php echo htmlspecialchars($title ?? 'Any Wish List'); ?></title>
    <style>
        h1 {
            margin-top: 0;
        }
        h2 {
            font-size: 28px;
        }
        h3 {
            margin-bottom: 0.5em;
        }
        #container {
            padding: 0 10px 110px;
        }
        h2.items-list-title {
            position: relative;
        }
        #container .background-theme.mobile-background {
            display: none;
        }
        @media (max-width: 600px){
            #container .background-theme.mobile-background {
                display: block;
            }
            #container .background-theme.desktop-background {
                display: none;
            }
        }
    </style>
    <?php if(isset($customStyles) && !empty($customStyles)): ?>
    <style>
    <?php echo $customStyles; ?>
    </style>
    <?php endif; ?>
</head>
<body class="<?php echo $user['dark'] === 'Yes' ? 'dark' : ''; ?>" 
      <?php if(isset($currentPage)): ?>data-current-page="<?php echo $currentPage; ?>"<?php elseif(isset($pageno)): ?>data-current-page="<?php echo $pageno; ?>"<?php endif; ?>
      <?php if(isset($totalPages)): ?>data-total-pages="<?php echo $totalPages; ?>"<?php elseif(isset($total_pages)): ?>data-total-pages="<?php echo $total_pages; ?>"<?php endif; ?>
      <?php if(isset($currentPageUrl)): ?>data-base-url="<?php echo $currentPageUrl; ?>"<?php elseif(isset($wishlist_id)): ?>data-base-url="/wishlists/<?php echo $wishlist_id; ?>"<?php elseif(isset($base_url)): ?>data-base-url="<?php echo $base_url; ?>"<?php endif; ?>>
    <div id="body">
        <?php include __DIR__ . '/../components/header.php'; ?>
        <div id="container">
            <?php echo $content; ?>
        </div>
    </div>
    <?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
