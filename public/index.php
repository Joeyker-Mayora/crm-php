<?php
require_once __DIR__ . '/../backend/api/api.php';
$allUsers = api_get_all_users();

//  opciones únicas para selects
$nombres = array_map(fn($u) => $u['nombre'], $allUsers);
$paises = array_map(fn($u) => $u['pais'], $allUsers);
$emails = array_map(fn($u) => $u['email'], $allUsers);
$nombres = array_unique($nombres);
$paises = array_unique($paises);
$emails = array_unique($emails);
sort($nombres);
sort($paises);
sort($emails);

//  filtros 
$nombre = isset($_GET['nombre']) ? trim($_GET['nombre']) : null;
$pais = isset($_GET['pais']) ? trim($_GET['pais']) : null;
$email = isset($_GET['email']) ? trim($_GET['email']) : null;
$datos = api_filter_users($allUsers, $nombre, $pais, $email);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>CRM - Usuarios</title>
<link rel="stylesheet" href="assets/css/styles.css">
<script src="assets/js/delete.js"></script>
</head>
<body>

<header class="app-header">
    <h1>📊 CRM - Usuarios Registrados</h1>
</header>

<main class="container">
    <section class="card">
        <form method="get" class="filter-form">
            <div class="filter-group">
                <label for="nombre">👤 Nombre</label>
                <select id="nombre" name="nombre">
                    <option value="">-- Todos --</option>
                    <?php foreach ($nombres as $nom): ?>
                        <option value="<?php echo htmlspecialchars($nom, ENT_QUOTES); ?>"
                            <?php if(($_GET['nombre'] ?? '') === $nom) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($nom); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="pais">🌍 País</label>
                <select id="pais" name="pais">
                    <option value="">-- Todos --</option>
                    <?php foreach ($paises as $pa): ?>
                        <option value="<?php echo htmlspecialchars($pa, ENT_QUOTES); ?>"
                            <?php if(($_GET['pais'] ?? '') === $pa) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($pa); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="email">✉️ Email</label>
                <select id="email" name="email">
                    <option value="">-- Todos --</option>
                    <?php foreach ($emails as $em): ?>
                        <option value="<?php echo htmlspecialchars($em, ENT_QUOTES); ?>"
                            <?php if(($_GET['email'] ?? '') === $em) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($em); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-search">🔍 Filtrar</button>
            <a href="?" class="btn-reset">↻ Limpiar</a>
        </form>
        <table class="crm-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>País</th>
                    <th>Foto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($datos)): ?>
                    <tr>
                        <td colspan="7">No hay datos disponibles</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($datos as $item): ?>
                        <tr data-id="<?php echo htmlspecialchars($item['id'] ?? '', ENT_QUOTES); ?>">
                            <td><?php echo htmlspecialchars($item['id'] ?? '', ENT_QUOTES); ?></td>
                            <td><?php echo htmlspecialchars($item['nombre'] ?? '', ENT_QUOTES); ?></td>
                            <td><?php echo htmlspecialchars($item['email'] ?? '', ENT_QUOTES); ?></td>
                            <td><?php echo htmlspecialchars($item['telefono'] ?? '', ENT_QUOTES); ?></td>
                            <td><?php echo htmlspecialchars($item['pais'] ?? '', ENT_QUOTES); ?></td>
                            <td>
                                <?php if (!empty($item['foto'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['foto'], ENT_QUOTES); ?>" alt="foto">
                                <?php endif; ?>
                            </td>
                            <td class="actions-cell">
                                <button class="delete-btn" data-id="<?php echo htmlspecialchars($item['id'] ?? '', ENT_QUOTES); ?>">Eliminar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (!empty($datos)): ?>
            <div class="results-info">
                📊 Se encontraron <strong><?php echo count($datos); ?></strong> usuario(s)
            </div>
        <?php endif; ?>

    </section>
</main>

</body>
</html>
