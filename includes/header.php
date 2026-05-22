<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - <?= $page_title ?? 'Location de voitures' ?></title>
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>assets/images/ii.jpg">
    <link rel="stylesheet" href="<?= SITE_URL ?>assets/css/style.css">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Miniver&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css">
    <script src="https://unpkg.com/scrollreveal"></script>
  
  
</head>
<body class="<?= htmlspecialchars($body_class ?? '') ?>">
    <?php include 'navbar.php'; ?>
    <main>