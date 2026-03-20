<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="<?= e(setting('color_primary','#3B82F6')) ?>">
  <title><?= e($pageTitle ?? APP_NAME) ?></title>
  <!-- Tailwind CSS CDN (production: use compiled CSS) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary:   '<?= e(setting('color_primary','#3B82F6')) ?>',
            secondary: '<?= e(setting('color_secondary','#10B981')) ?>',
            accent:    '<?= e(setting('color_accent','#F59E0B')) ?>',
          }
        }
      }
    }
  </script>
  <style>
    :root {
      --color-primary:   <?= e(setting('color_primary','#3B82F6')) ?>;
      --color-secondary: <?= e(setting('color_secondary','#10B981')) ?>;
      --color-accent:    <?= e(setting('color_accent','#F59E0B')) ?>;
    }
    main h1 { color: var(--color-secondary); }
  </style>
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <!-- ApexCharts -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <!-- PWA manifest -->
  <link rel="manifest" href="<?= url('public/manifest.json') ?>">
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
