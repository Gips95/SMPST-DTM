<?php
session_start();
$_SESSION['user'] = $_SESSION['user'] ?? null;
$_SESSION['rol'] = $_SESSION['rol'] ?? 'invitado';
include 'db/conn.php'; // Conexión a la base de datos

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repositorio de Proyectos Académicos</title>
    <link href="style/css2.csss" rel="stylesheet">
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">
    <link rel="stylesheet" href="styles/index.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
        <img src="imagenes/cintillo.jpg" class="">
            <div class="header-content">
                
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Departamento de Tecnologia de los Materiales</span><br><br>
                </div>
                <div class="auth-buttons">
                    <?php if(isset($_SESSION['user'])): ?>
                        <a href="login.php" class="btn btn-login">Iniciar Sesión</a>
                        <a href="crearestudiante.php" class="btn btn-login">Registrarse</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-login">Iniciar Sesión</a>
                        <a href="crearestudiante.php" class="btn btn-login">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Explora proyectos académicos innovadores</h1>
                <p>Accede a una amplia colección de trabajos de investigación, proyectos de grado y materiales académicos desarrollados por nuestra comunidad universitaria.</p>
                <img src="imagenes/logo.png" class="hero-image">
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Beneficios de Nuestra Plataforma</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Búsqueda Avanzada</h3>
                    <p>Encuentra proyectos específicos utilizando nuestros potentes filtros de búsqueda por categorías, palabras clave o autores.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Comunidad Académica</h3>
                    <p> Fomente investigaciones interdisciplinarias y construya redes de conocimiento que enriquezcan su trayectoria profesional.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Seguridad Garantizada</h3>
                    <p>Todos los proyectos están protegidos y solo los usuarios autorizados pueden acceder a contenido sensible.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Projects Section -->
    <section class="projects">
        <div class="container">
            
            
           
            
            <div class="projects-grid" id="tabla_proyectos">
  <!-- AJAX inyectará aquí sólo <div class="project-card">…</div> -->
  <div class="no-results">Escribe para buscar...</div>
  </div>
</div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="fas fa-graduation-cap"></i>PNF-IMI Repositorio Académico
                </div>
               
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
                <div class="copyright">
                    &copy; <?= date('Y') ?> Repositorio Académico. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="js/jquery.js"></script>
    <script>
    function buscarProyectos() {
        let busqueda = $("#buscar").val();

        $.ajax({
            url: "endpoints/filter_projects.php",
            method: "POST",
            data: { buscar: busqueda },
            success: function(data) {
                $("#tabla_proyectos").html(data);
            }
        });
    }

    // Cargar todos los proyectos al inicio
    $(document).ready(function() {
        buscarProyectos();
    });
    </script>
</body>
</html>