<?php
// index.php – Catálogo de Cursos y Carrusel

// 1. Conexión a la BD
require_once __DIR__ . '/../config/conexion.php';

// 2. Consultas
// Carrusel activo
$sqlCarrusel = "SELECT * FROM carrusel WHERE activo = 1 ORDER BY orden ASC, id DESC";
$resCarrusel = mysqli_query($conexion, $sqlCarrusel);

// Cursos
$sqlCursos = "SELECT * FROM cursos ORDER BY idcurso DESC";
$resCursos = mysqli_query($conexion, $sqlCursos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Cursos – DevAcademy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-3">

<!-- Carrusel dinámico -->
<?php if ($resCarrusel && mysqli_num_rows($resCarrusel) > 0): ?>
<section id="carruselPrincipal" class="mb-5">
    <div id="carrusel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php $esPrimera = true; while ($banner = mysqli_fetch_assoc($resCarrusel)): ?>
                <div class="carousel-item <?= $esPrimera ? 'active' : '' ?>">
                    <img src="../uploads/carrusel/<?= htmlspecialchars($banner['imagen']) ?>"
                         class="d-block w-100" height="350" style="object-fit:cover;" alt="Banner">
                    <?php if (!empty($banner['titulo'])): ?>
                        <div class="carousel-caption d-none d-md-block">
                            <h5><?= htmlspecialchars($banner['titulo']) ?></h5>
                        </div>
                    <?php endif; ?>
                </div>
            <?php $esPrimera = false; endwhile; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carrusel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carrusel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>
<?php else: ?>
<p class="text-center">No hay banners configurados todavía.</p>
<?php endif; ?>

<!-- Catálogo de Cursos -->
<section id="cursos">
    <h2 class="mb-4">Catálogo de Cursos</h2>
    <div class="row g-3">
        <?php if ($resCursos && mysqli_num_rows($resCursos) > 0): ?>
            <?php while ($curso = mysqli_fetch_assoc($resCursos)): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title h5">
                                <?= htmlspecialchars($curso['titulo']) ?>
                            </h3>
                            <p class="card-text">
                                <?= htmlspecialchars(mb_strimwidth($curso['descripcion'], 0, 80, '...')) ?>
                            </p>
                            <p><strong>Precio: $<?= number_format($curso['precio'], 2) ?></strong></p>
                            <a href="curso_detalle.php?idcurso=<?= $curso['idcurso'] ?>" class="btn btn-primary btn-sm">Ver Detalles</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay cursos disponibles por el momento.</p>
        <?php endif; ?>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
