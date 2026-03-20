<?php $pageTitle = 'Página no encontrada – 404'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
  <div class="text-center">
    <div class="text-8xl mb-6">🗺️</div>
    <h1 class="text-6xl font-black text-gray-200 mb-2">404</h1>
    <h2 class="text-2xl font-bold text-gray-700 mb-3">Página no encontrada</h2>
    <p class="text-gray-500 mb-8 max-w-sm mx-auto">El lugar que buscas no existe en nuestro mapa turístico.</p>
    <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>"
      class="inline-block bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-700 transition">
      Ir al inicio
    </a>
  </div>
</body>
</html>
